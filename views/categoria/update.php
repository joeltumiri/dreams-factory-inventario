<?php

/** @var yii\web\View $this */
/** @var app\models\Categoria $model */

use yii\bootstrap5\Html;

$this->title = 'Editar Categoría';
?>
<h4 class="mb-3"><?= Html::encode($this->title) ?></h4>
<?= $this->render('_form', ['model' => $model]) ?>
