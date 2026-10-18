<?php
/** @var yii\web\View $this */
/** @var app\models\Proveedor $model */
/** @var array $ciudades */
/** @var array $rubros */
use yii\bootstrap5\Html;
$this->title = 'Nuevo Proveedor';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-handshake me-2 text-primary"></i><?= Html::encode($this->title) ?>
    </h4>
</div>
<?= $this->render('_form', ['model' => $model, 'ciudades' => $ciudades, 'rubros' => $rubros]) ?>
