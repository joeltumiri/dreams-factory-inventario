<?php

/** @var yii\web\View $this */
/** @var app\models\Producto $model */
/** @var array $categorias */

use yii\bootstrap5\Html;

$this->title = 'Nuevo producto';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
</div>

<?= $this->render('_form', [
    'model' => $model,
    'categorias' => $categorias,
]) ?>
