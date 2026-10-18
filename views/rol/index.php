<?php
/** @var \yii\web\View $this */
/** @var \app\models\Role[] $roles */

use yii\bootstrap5\Html;

$this->title = 'Gestión de Roles';
?>
<div class="card shadow-sm border-0">
    <div class="card-header d-flex align-items-center justify-content-between py-3" style="background:var(--df-azul);color:#fff;border-radius:10px 10px 0 0;">
        <span class="fw-bold fs-5"><i class="fa-solid fa-user-shield me-2"></i>Roles del Sistema</span>
        <?= Html::a('<i class="fa-solid fa-plus me-1"></i>Nuevo Rol', ['create'], ['class' => 'btn btn-light btn-sm fw-semibold']) ?>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 df-separated-table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th>Nombre del Rol</th>
                    <th style="width:120px;">Valor</th>
                    <th style="width:120px;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roles)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fa-solid fa-circle-info me-1"></i>No hay roles registrados.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td class="text-muted"><?= $role->id ?></td>
                    <td>
                        <span class="badge rounded-pill" style="background:var(--df-azul2);font-size:.88rem;padding:.4em .9em;">
                            <i class="fa-solid fa-circle-dot me-1"></i><?= Html::encode($role->role_name) ?>
                        </span>
                    </td>
                    <td><code><?= $role->role_value ?></code></td>
                    <td class="text-center">
                        <?php if (!in_array($role->role_value, [\app\models\Role::GERENTE, \app\models\Role::ALMACEN, \app\models\Role::VENDEDOR], true)): ?>
                            <?= Html::a(
                                '<i class="fa-solid fa-trash-can"></i>',
                                ['delete', 'id' => $role->id],
                                [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'Eliminar',
                                    'data'  => ['confirm' => "Eliminar rol: {$role->role_name}"],
                                ]
                            ) ?>
                        <?php else: ?>
                            <span class="text-muted small" title="Rol del sistema"><i class="fa-solid fa-lock"></i></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
