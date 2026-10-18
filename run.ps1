$ErrorActionPreference = 'Stop'

function Get-ComposeCommand {
	if (Get-Command docker -ErrorAction SilentlyContinue) {
		try {
			docker compose version | Out-Null
			return @('docker', 'compose')
		}
		catch {
		}
	}

	if (Get-Command docker-compose -ErrorAction SilentlyContinue) {
		return @('docker-compose')
	}

	throw 'No se encontro Docker Compose. Instala Docker Desktop y verifica que este en ejecucion.'
}

function Invoke-Compose {
	param(
		[string[]]$Compose,
		[string[]]$ComposeArgs
	)

	if ($Compose.Count -eq 2) {
		& $Compose[0] $Compose[1] @ComposeArgs
	}
	else {
		& $Compose[0] @ComposeArgs
	}

	if ($LASTEXITCODE -ne 0) {
		throw "Fallo Docker Compose al ejecutar: $($ComposeArgs -join ' ')"
	}
}

Write-Host ''
Write-Host '=== Dreams Factory - Arranque ===' -ForegroundColor Cyan

$compose = Get-ComposeCommand

docker info | Out-Null
if ($LASTEXITCODE -ne 0) {
	throw 'Docker Desktop no esta ejecutandose. Inicia Docker Desktop y vuelve a correr run.ps1'
}

Write-Host '1) Levantando contenedores...' -ForegroundColor Yellow
Invoke-Compose -Compose $compose -ComposeArgs @('up', '-d', '--build')

Write-Host '2) Esperando base de datos saludable (df_db)...' -ForegroundColor Yellow
$maxAttempts = 40
for ($i = 1; $i -le $maxAttempts; $i++) {
	$health = docker inspect -f "{{.State.Health.Status}}" df_db 2>$null
	if ($health -eq 'healthy') {
		break
	}

	if ($i -eq $maxAttempts) {
		throw 'La base de datos no llego a estado healthy a tiempo. Revisa: docker logs df_db'
	}

	Start-Sleep -Seconds 2
}

Write-Host '3) Ejecutando migraciones...' -ForegroundColor Yellow
docker exec df_web php yii migrate --interactive=0
if ($LASTEXITCODE -ne 0) {
	throw 'Fallo al ejecutar migraciones. Revisa: docker logs df_web'
}

Write-Host '4) Sistema disponible.' -ForegroundColor Green
Write-Host '   App:        http://localhost:8080'
Write-Host '   phpMyAdmin: http://localhost:8081'

Start-Process 'http://localhost:8080' | Out-Null
