<?php

/** @var yii\web\View $this */
/** @var app\models\User $model */

use yii\bootstrap5\Html;

$this->title = 'Editar usuario';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Volver', ['/usuario/index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?= $this->render('_form', compact('model')) ?>