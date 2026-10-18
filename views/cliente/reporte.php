<?php
/** @var yii\web\View $this */
/** @var array $rows */
/** @var int $gestion */
/** @var array $gestiones */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Reporte de Compras por Cliente — Gestión ' . $gestion;
$totalGlobal = array_sum(array_column($rows, 'total'));
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="m-0"><i class="fa-solid fa-chart-bar me-2 text-primary"></i>Reporte de Compras por Cliente</h4>
        <small class="text-muted">Clientes ordenados por mayor volumen de compra</small>
    </div>
    <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Volver', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<!-- Filtro de gestión -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="get" class="d-flex align-items-center gap-3">
            <label class="fw-semibold mb-0">Gestión:</label>
            <select name="gestion" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
                <?php foreach ($gestiones as $g): ?>
                    <option value="<?= $g ?>" <?= (int)$g === $gestion ? 'selected' : '' ?>><?= $g ?></option>
                <?php endforeach; ?>
            </select>
            <span class="text-muted small"><?= count($rows) ?> clientes con compras en <?= $gestion ?></span>
        </form>
    </div>
</div>

<?php if (!$rows): ?>
    <div class="alert alert-info">Sin compras registradas en la gestión <?= $gestion ?>.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover df-separated-table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3">#</th>
                    <th>Cliente</th>
                    <th>Zona</th>
                    <th class="text-end">Pedidos</th>
                    <th class="text-end">Total (Bs.)</th>
                    <th>Participación</th>
                    <th class="text-end">Última compra</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $i => $r):
                $pct = $totalGlobal > 0 ? ((float)$r['total'] / $totalGlobal) * 100 : 0;
                $badgeColor = $i === 0 ? 'warning' : ($i < 3 ? 'primary' : 'secondary');
            ?>
            <tr>
                <td class="ps-3">
                    <span class="badge bg-<?= $badgeColor ?>"><?= $i + 1 ?></span>
                </td>
                <td class="fw-semibold"><?= Html::encode((string)$r['razon_social']) ?></td>
                <td><small class="text-muted"><?= Html::encode((string)($r['zona'] ?: '—')) ?></small></td>
                <td class="text-end"><?= (int)$r['pedidos'] ?></td>
                <td class="text-end fw-bold text-success">
                    <?= number_format((float)$r['total'], 2) ?>
                </td>
                <td style="min-width:160px;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height:8px;">
                            <div class="progress-bar bg-primary" style="width:<?= number_format($pct, 1) ?>%"></div>
                        </div>
                        <small class="text-muted"><?= number_format($pct, 1) ?>%</small>
                    </div>
                </td>
                <td class="text-end">
                    <small><?= $r['ultima_compra'] ? date('d/m/Y', strtotime($r['ultima_compra'])) : '—' ?></small>
                </td>
                <td>
                    <?php if ($r['cliente_id']): ?>
                        <?= Html::a('<i class="fa-solid fa-eye"></i>', ['view', 'id' => $r['cliente_id']], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="3" class="ps-3">TOTAL</td>
                    <td class="text-end"><?= array_sum(array_column($rows, 'pedidos')) ?></td>
                    <td class="text-end text-success"><?= number_format($totalGlobal, 2) ?></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>
<?php endif; ?>
