<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Tabla venta.
 *
 * @property int         $id
 * @property string      $codigo
 * @property string      $fecha_pedido
 * @property string|null $fecha_entrega
 * @property string|null $fecha_envio
 * @property string|null $fecha_entrega_real
 * @property int         $estado_venta_id
 * @property string|null $nota
 * @property int         $tipo_pago_id
 * @property int         $cliente_id
 * @property int         $gestion
 * @property int         $estado
 * @property int         $user_id
 * @property int         $estado_pago_id
 * @property float       $importe_total
 * @property float       $descuento
 * @property float       $total_efectivo
 * @property string|null $nota_descuento
 * @property string|null $documento
 * @property string|null $nro_documento
 * @property string|null $metodo_pago
 * @property int|null    $created_by
 * @property int|null    $created_at
 * @property int|null    $updated_by
 * @property int|null    $updated_at
 *
 * @property-read Cliente    $cliente
 * @property-read User       $user
 * @property-read DetalleVenta[] $detalles
 */
final class Venta extends ActiveRecord
{
    public const ESTADO_BORRADOR    = 0;
    public const ESTADO_CONFIRMADA  = 1;  // Para Entregar
    public const ESTADO_ENVIADA     = 2;  // Enviada / En tránsito
    public const ESTADO_ENTREGADA   = 3;  // Entregada al cliente

    public static function estadoLabel(int $id): string
    {
        return match ($id) {
            self::ESTADO_CONFIRMADA => 'Por Enviar',
            self::ESTADO_ENVIADA    => 'Enviada',
            self::ESTADO_ENTREGADA  => 'Entregada',
            default                 => 'Borrador',
        };
    }

    public static function estadoColor(int $id): string
    {
        return match ($id) {
            self::ESTADO_CONFIRMADA => 'warning',
            self::ESTADO_ENVIADA    => 'info',
            self::ESTADO_ENTREGADA  => 'success',
            default                 => 'secondary',
        };
    }

    public static function tableName(): string
    {
        return '{{%venta}}';
    }

    public function rules(): array
    {
        return [
            [['codigo', 'fecha_pedido', 'estado_venta_id', 'tipo_pago_id', 'cliente_id', 'gestion', 'user_id', 'estado_pago_id'], 'required'],
            [['estado_venta_id', 'tipo_pago_id', 'cliente_id', 'gestion', 'estado', 'user_id', 'estado_pago_id',
              'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['importe_total', 'descuento', 'total_efectivo'], 'number', 'min' => 0],
            [['importe_total', 'descuento', 'total_efectivo'], 'default', 'value' => 0],
            [['fecha_pedido', 'fecha_envio', 'fecha_entrega_real'], 'safe'],
            [['fecha_entrega'], 'required'],
            [['fecha_entrega'], 'date', 'format' => 'php:Y-m-d'],
            [['codigo'], 'string', 'max' => 40],
            [['nota', 'nota_descuento'], 'string', 'max' => 255],
            [['documento', 'metodo_pago'], 'string', 'max' => 30],
            [['nro_documento'], 'string', 'max' => 80],
            [['codigo'], 'unique'],
            [['estado'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'codigo' => 'Código',
            'fecha_pedido' => 'Fecha pedido',
            'fecha_entrega' => 'Fecha entrega',
            'estado_venta_id' => 'Estado venta',
            'tipo_pago_id' => 'Tipo pago',
            'cliente_id' => 'Cliente',
            'user_id' => 'Vendedor',
            'estado_pago_id' => 'Estado pago',
            'importe_total' => 'Importe total',
            'descuento' => 'Descuento',
            'total_efectivo' => 'Total',
            'nota' => 'Nota',
            'documento' => 'Documento',
            'nro_documento' => 'Nro. documento',
            'metodo_pago' => 'Método de pago',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = time();
        $userId = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int)Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    /**
     * Genera el código de venta: VTA-YYYY-NNN
     */
    public static function generarCodigo(int $gestion): string
    {
        $ultimo = static::find()
            ->where(['gestion' => $gestion])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $secuencia = 1;
        if ($ultimo !== null) {
            // extraer número del código VTA-YYYY-NNN
            $partes = explode('-', $ultimo->codigo);
            $secuencia = (int)end($partes) + 1;
        }

        return sprintf('VTA-%d-%03d', $gestion, $secuencia);
    }

    public function getCliente(): ActiveQuery
    {
        return $this->hasOne(Cliente::class, ['id' => 'cliente_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getDetalles(): ActiveQuery
    {
        return $this->hasMany(DetalleVenta::class, ['venta_id' => 'id']);
    }
}
