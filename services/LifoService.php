<?php

declare(strict_types=1);

namespace app\services;

use app\models\DetalleVenta;

/**
 * LifoService — alias histórico.
 *
 * El sistema Dreams Factory utiliza el método LIFO (Last In First Out).
 * La lógica vive en FifoService (nombre conservado por compatibilidad de BD
 * con la tabla movimiento_fifo); este alias se mantiene para no romper código
 * que ya invoque LifoService::descontarStock().
 *
 * @deprecated Usar FifoService::descontarStock() (que internamente aplica LIFO).
 */
final class LifoService
{
    /**
     * @deprecated Usar FifoService::descontarStock()
     */
    public static function descontarStock(DetalleVenta $detalle): void
    {
        FifoService::descontarStock($detalle);
    }
}
