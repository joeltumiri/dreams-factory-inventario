<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * @property int      $id
 * @property int      $user_id
 * @property int      $cliente_id
 * @property int|null $venta_id
 * @property string   $fecha_visita
 * @property string|null $hora_visita
 * @property int      $estado        1=Cerrado, 2=Sin pedido, 3=Con pedido
 * @property string|null $obs
 * @property int|null $created_at
 *
 * @property-read User    $user
 * @property-read Cliente $cliente
 * @property-read Venta|null $venta
 */
class Visita extends ActiveRecord
{
    public const ESTADO_CERRADO    = 1;
    public const ESTADO_SIN_PEDIDO = 2;
    public const ESTADO_CON_PEDIDO = 3;

    public static function tableName(): string { return '{{%visita}}'; }

    public function rules(): array
    {
        return [
            [['user_id', 'cliente_id', 'fecha_visita', 'estado'], 'required'],
            [['user_id', 'cliente_id', 'venta_id', 'estado'], 'integer'],
            [['fecha_visita'], 'date', 'format' => 'php:Y-m-d'],
            [['hora_visita'], 'string'],
            [['estado'], 'in', 'range' => [1, 2, 3]],
            [['obs'], 'string', 'max' => 500],
            [['venta_id'], 'default', 'value' => null],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'user_id'      => 'Vendedor',
            'cliente_id'   => 'Cliente',
            'fecha_visita' => 'Fecha de visita',
            'hora_visita'  => 'Hora de visita',
            'estado'       => 'Estado',
            'obs'          => 'ObservaciÃ³n',
            'venta_id'     => 'Venta vinculada',
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCliente(): ActiveQuery
    {
        return $this->hasOne(Cliente::class, ['id' => 'cliente_id']);
    }

    public function getVenta(): ActiveQuery
    {
        return $this->hasOne(Venta::class, ['id' => 'venta_id']);
    }

    public static function estadoLabel(int $estado): string
    {
        return match ($estado) {
            self::ESTADO_CERRADO    => 'Cerrado',
            self::ESTADO_SIN_PEDIDO => 'Sin pedido',
            self::ESTADO_CON_PEDIDO => 'Visitado',
            default                 => 'Desconocido',
        };
    }

    public static function estadoColor(int $estado): string
    {
        return match ($estado) {
            self::ESTADO_CERRADO    => 'danger',
            self::ESTADO_SIN_PEDIDO => 'warning',
            self::ESTADO_CON_PEDIDO => 'success',
            default                 => 'secondary',
        };
    }

    public static function estadoEmoji(int $estado): string
    {
        return '';
    }
}

