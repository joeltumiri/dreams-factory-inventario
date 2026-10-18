<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var string $search
 */

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Gestión de Usuarios';
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css', ['depends' => [\app\assets\AppAsset::class]]);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-users-gear me-2 text-primary"></i> Gestión de Usuarios</h4>
    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Nuevo Usuario', ['/usuario/create'], ['class' => 'btn btn-primary']) ?>
</div>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('danger')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= Html::encode(Yii::$app->session->getFlash('danger')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" action="<?= Url::to(['/usuario/index']) ?>" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label">Buscador</label>
                <input type="text" name="q" value="<?= Html::encode($search) ?>" class="form-control" placeholder="Buscar por usuario o email">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Buscar</button>
                <a href="<?= Url::to(['/usuario/index']) ?>" class="btn btn-outline-secondary w-100">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Tipo de usuario</th>
                    <th>Estado</th>
                    <th>Fecha de creación</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($dataProvider->models as $u): ?>
                <tr>
                    <td class="text-muted small"><?= $u->id ?></td>
                    <td>
                        <i class="fa-solid fa-user me-1 text-secondary"></i>
                        <strong><?= Html::encode($u->username) ?></strong>
                    </td>
                    <td><?= Html::encode($u->email) ?></td>
                    <td><span class="badge bg-<?= Html::encode($u->roleBadgeClass()) ?>"><?= Html::encode($u->roleLabel() ?: '—') ?></span></td>
                    <td><span class="badge bg-success"><?= Html::encode($u->tipo_usuario ?: '—') ?></span></td>
                    <td>
                        <span class="badge bg-<?= Html::encode($u->statusBadgeClass()) ?>"><?= Html::encode($u->statusLabel()) ?></span>
                    </td>
                    <td>
                        <?= Html::encode($u->createdAtFormatted('d/m/Y')) ?>
                    </td>
                    <td class="text-center">
                        <a href="<?= Url::to(['/usuario/view', 'id' => $u->id]) ?>" class="btn btn-info btn-sm" title="Ver" aria-label="Ver"><i class="bi bi-eye"></i></a>
                        <a href="<?= Url::to(['/usuario/update', 'id' => $u->id]) ?>" class="btn btn-warning btn-sm" title="Editar" aria-label="Editar"><i class="bi bi-pencil-square"></i></a>
                        <a href="<?= Url::to(['/usuario/eliminar', 'id' => $u->id]) ?>" class="btn btn-danger btn-sm" title="Eliminar" aria-label="Eliminar" onclick="return confirm('¿Eliminar definitivamente a <?= Html::encode(addslashes($u->username)) ?>? Esta acción no se puede deshacer.')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$dataProvider->models): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-body border-top">
        <?= \yii\widgets\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    </div>
</div>
