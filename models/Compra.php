<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $proveedor_id
 * @property string $fecha_compra
 * @property string|null $nota
 * @property float $importe_total
 * @property string|null $documento
 * @property string|null $nro_documento
 * @property int $gestion
 * @property int $estado
 * @property int|null $created_by
 * @property int|null $created_at
 * @property int|null $updated_by
 * @property int|null $updated_at
 *
 * @property-read Proveedor $proveedor
 * @property-read User|null $createdByUser
 * @property-read DetalleCompra[] $detalles
 */
final class Compra extends ActiveRecord
{
    public const ESTADO_BORRADOR = 1;
    public const ESTADO_CONFIRMADA = 2;

    public static function tableName(): string
    {
        return '{{%compra}}';
    }

    public function rules(): array
    {
        return [
            [['proveedor_id', 'fecha_compra', 'gestion'], 'required'],
            [['proveedor_id', 'gestion', 'estado', 'created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['fecha_compra'], 'safe'],
            [['importe_total'], 'number', 'min' => 0],
            [['nota'], 'string', 'max' => 255],
            [['documento'], 'string', 'max' => 50],
            [['nro_documento'], 'string', 'max' => 80],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now    = time();
        $userId = (Yii::$app->has('user') && !Yii::$app->user->isGuest)
            ? (int)Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'proveedor_id' => 'Proveedor',
            'fecha_compra' => 'Fecha compra',
            'nota' => 'Nota',
            'importe_total' => 'Importe total',
            'documento' => 'Documento',
            'nro_documento' => 'Nro documento',
            'gestion' => 'Gestión',
            'estado' => 'Estado',
        ];
    }

    public function getProveedor(): ActiveQuery
    {
        return $this->hasOne(Proveedor::class, ['id' => 'proveedor_id']);
    }

    public function getCreatedByUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getDetalles(): ActiveQuery
    {
        return $this->hasMany(DetalleCompra::class, ['compra_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }
}
