<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

$statusCode = isset($exception) && method_exists($exception, 'statusCode')
    ? $exception->statusCode
    : Yii::$app->response->statusCode;

$is403 = $statusCode === 403;
$is404 = $statusCode === 404;

if ($is403) {
    $this->title = 'Acceso denegado';
    $icon        = 'fa-ban';
    $iconColor   = 'text-danger';
    $bgGradient  = 'linear-gradient(135deg,#7f1d1d 0%,#ef4444 100%)';
    $heading     = '403 — Acceso denegado';
    $sub         = 'No tienes permiso para ver esta página.';
    $detail      = 'No tienes acceso a la sección que intentaste visitar. Si crees que esto es un error, comunícate con el administrador del sistema.';
} elseif ($is404) {
    $this->title = 'Página no encontrada';
    $icon        = 'fa-circle-question';
    $iconColor   = 'text-warning';
    $bgGradient  = 'linear-gradient(135deg,#78350f 0%,#f59e0b 100%)';
    $heading     = '404 — Página no encontrada';
    $sub         = 'La dirección que ingresaste no existe.';
    $detail      = 'Es posible que la URL haya cambiado, o que hayas escrito una ruta incorrecta.';
} else {
    $this->title = $name;
    $icon        = 'fa-triangle-exclamation';
    $iconColor   = 'text-secondary';
    $bgGradient  = 'linear-gradient(135deg,#1e3a5f 0%,#3b82f6 100%)';
    $heading     = Html::encode($name);
    $sub         = 'Ocurrió un error inesperado.';
    $detail      = Html::encode($message);
}
?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
    <div class="text-center" style="max-width:520px; width:100%;">

        <!-- Ícono circular con gradiente -->
        <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle"
             style="width:110px;height:110px;background:<?= $bgGradient ?>;box-shadow:0 8px 32px rgba(0,0,0,.25);">
            <i class="fa-solid <?= $icon ?> fa-3x text-white"></i>
        </div>

        <h2 class="fw-bold mb-1"><?= $heading ?></h2>
        <p class="lead mb-2"><?= $sub ?></p>
        <p class="text-muted mb-4 small"><?= $detail ?></p>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver
            </a>
            <a href="<?= Yii::$app->homeUrl ?>" class="btn btn-primary">
                <i class="fa-solid fa-house me-1"></i> Ir al inicio
            </a>
        </div>

    </div>
</div>
