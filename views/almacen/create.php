<?php
/** @var yii\web\View $this */
/** @var app\models\Almacen $model */
use yii\bootstrap5\Html;

$this->title = 'Nuevo Almacén';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-warehouse me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Volver', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
</div>

<?= $this->render('_form', ['model' => $model]) ?>
