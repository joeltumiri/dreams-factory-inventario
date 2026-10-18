<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Ajusta los tipos de pago al manual: Contado / Cuotas.
 */
final class m260430_000004_update_tipo_pago_seeds extends Migration
{
    public function safeUp(): void
    {
        // Si los nombres ya coinciden no hace nada.
        $this->update('{{%tipo_pago}}', ['nombre' => 'Contado'], ['id' => 1]);
        $this->update('{{%tipo_pago}}', ['nombre' => 'Cuotas'],  ['id' => 2]);
    }

    public function safeDown(): void
    {
        $this->update('{{%tipo_pago}}', ['nombre' => 'Efectivo'], ['id' => 1]);
        $this->update('{{%tipo_pago}}', ['nombre' => 'Crédito'],  ['id' => 2]);
    }
}
