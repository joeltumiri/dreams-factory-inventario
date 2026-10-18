<?php
/** @var yii\web\View $this */
/** @var app\models\Almacen $model */
use yii\bootstrap5\Html;

$this->title = 'Editar Almacén: ' . $model->nombre;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-pen me-2 text-warning"></i><?= Html::encode($this->title) ?></h4>
    <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Volver', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
</div>

<?= $this->render('_form', ['model' => $model]) ?>
