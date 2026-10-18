<?php

/** @var yii\web\View $this */
/** @var app\models\Cliente $model */
/** @var array $tipos */
/** @var array $ciudades */
/** @var array $departamentos */

use yii\bootstrap5\Html;

$this->title = 'Nuevo cliente';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Volver', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>
<?= $this->render('_form', compact('model', 'tipos', 'ciudades', 'departamentos')) ?>
