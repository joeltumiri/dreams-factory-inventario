<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;

$this->beginPage(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #222; background: #fff; }

        /* ===== PRINT HEADER ===== */
        .print-header {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 14px 20px 12px;
            border-bottom: 2px solid #c0c0c0;
        }
        .print-logo {
            background: #1a3060;
            color: #fff;
            font-weight: 900;
            font-size: 22px;
            line-height: 1;
            padding: 12px 14px;
            letter-spacing: 1px;
            min-width: 120px;
            text-align: center;
        }
        .print-logo span { display: block; font-size: 14px; letter-spacing: 3px; margin-top: 2px; }
        .print-company {
            font-size: 12px;
            color: #444;
            line-height: 1.6;
        }
        .print-company strong { font-size: 15px; color: #111; letter-spacing: 1px; }

        /* ===== PAGE TITLE BAND ===== */
        .print-title-band {
            background: linear-gradient(135deg, #e8e8e8 0%, #d0d0d0 50%, #e8e8e8 100%);
            text-align: center;
            padding: 16px 0 14px;
            margin: 0;
            position: relative;
            overflow: hidden;
        }
        .print-title-band::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(circle, #bbb 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.25;
        }
        .print-title-band h2 {
            position: relative;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 3px;
            color: #222;
            text-transform: uppercase;
        }

        /* ===== CONTENT AREA ===== */
        .print-body { padding: 18px 24px; }

        /* ===== PRINT MEDIA ===== */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }

        /* ===== SCREEN TOOLBAR ===== */
        .print-toolbar {
            background: #f4f4f4;
            border-bottom: 1px solid #ddd;
            padding: 8px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .print-toolbar button, .print-toolbar a {
            padding: 5px 14px;
            font-size: 13px;
            border-radius: 4px;
            border: 1px solid #999;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .print-toolbar button { background: #1a3060; color: #fff; border-color: #1a3060; }
        .print-toolbar a { background: #fff; color: #333; }
    </style>
</head>
<body>
<?php $this->beginBody() ?>
<div class="no-print print-toolbar">
    <button onclick="window.print()"><i class="fa-solid fa-print" style="margin-right:4px"></i> Imprimir</button>
    <a href="javascript:history.back()">Volver</a>
</div>

<div class="print-header">
    <div class="print-logo">DREAM<span>FACTORY</span></div>
    <div class="print-company">
        <strong>DREAMS FACTORY</strong><br>
        LINEA PARA BEBES<br>
        CALLE 110 NRO. 61<br>
        ZONA VILLA BOLIVAR D<br>
        (591) 777-13672
    </div>
</div>

<?= $content ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
