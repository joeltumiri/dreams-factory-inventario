<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Proveedor;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

final class SeedController extends Controller
{
    public function actionDemo(int $force = 0): int
    {
        $db = Yii::$app->db;

        $ciudadId = (int)$db->createCommand(
            "SELECT id FROM {{%ciudad}} WHERE nombre=:n ORDER BY id ASC LIMIT 1",
            [':n' => 'El Alto']
        )->queryScalar();

        $now = time();

        $proveedores = [
            [
                'nombre'       => 'Distribuidora Bebé S.R.L.',
                'razon_social' => 'Distribuidora Bebé S.R.L.',
                'direccion'    => 'Av. 6 de Marzo, El Alto',
                'contacto'     => 'Ventas',
                'nit'          => '123456789',
                'email'        => 'cliente1@example.com',
                'telefono'     => '777-00001',
                'rubro'        => 'Insumos para bebé',
            ],
            [
                'nombre'       => 'Importadora Kids',
                'razon_social' => 'Importadora Kids',
                'direccion'    => 'Zona 16 de Julio, El Alto',
                'contacto'     => 'Atención al cliente',
                'nit'          => '987654321',
                'email'        => 'cliente2@example.com',
                'telefono'     => '777-00002',
                'rubro'        => 'Textiles y accesorios',
            ],
        ];

        $proveedorIds = [];
        foreach ($proveedores as $p) {
            $existing = Proveedor::find()->where(['nombre' => $p['nombre']])->one();
            if ($existing !== null) {
                $proveedorIds[$p['nombre']] = (int)$existing->id;
                continue;
            }

            $prov = new Proveedor();
            $prov->nombre       = $p['nombre'];
            $prov->razon_social = $p['razon_social'];
            $prov->direccion    = $p['direccion'];
            $prov->contacto     = $p['contacto'];
            $prov->nit          = $p['nit'];
            $prov->email        = $p['email'];
            $prov->telefono     = $p['telefono'];
            $prov->rubro        = $p['rubro'];
            $prov->ciudad_id    = $ciudadId > 0 ? $ciudadId : null;
            $prov->estado       = 1;
            $prov->created_by   = 1;
            $prov->created_at   = $now;

            if (!$prov->save()) {
                $this->stderr("Error creando proveedor '{$p['nombre']}': " . json_encode($prov->getErrors(), JSON_UNESCAPED_UNICODE) . "\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $proveedorIds[$p['nombre']] = (int)$prov->id;
        }

        $this->stdout("Seed demo OK.\n");
        $this->stdout("- Proveedores: " . count($proveedorIds) . "\n");

        return ExitCode::OK;
    }
}
