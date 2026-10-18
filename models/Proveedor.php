<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $razon_social
 * @property string|null $direccion
 * @property string|null $contacto
 * @property string|null $nota
 * @property string|null $nit
 * @property string|null $email
 * @property string|null $telefono
 * @property string|null $celular
 * @property int|null $ciudad_id
 * @property string|null $rubro
 * @property int $estado
 *
 * @property Ciudad|null $ciudad
 * @property Compra[] $compras
 */
final class Proveedor extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%proveedor}}';
    }

    public function rules(): array
    {
        return [
            [['nombre'], 'required'],
            [['estado', 'ciudad_id'], 'integer'],
            [['nombre'], 'string', 'max' => 150],
            [['razon_social'], 'string', 'max' => 200],
            [['direccion', 'contacto'], 'string', 'max' => 255],
            [['nota'], 'string', 'max' => 255],
            [['nit'], 'string', 'max' => 30],
            [['email'], 'string', 'max' => 150],
            [['email'], 'email'],
            [['telefono', 'celular'], 'string', 'max' => 30],
            [['rubro'], 'string', 'max' => 150],
            [['nombre'], 'unique'],
            [['estado'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'           => 'ID',
            'nombre'       => 'Nombre comercial',
            'razon_social' => 'Razón social',
            'direccion'    => 'Dirección',
            'contacto'     => 'Contacto principal',
            'nota'         => 'Notas / Observaciones',
            'nit'          => 'NIT',
            'email'        => 'Email',
            'telefono'     => 'Teléfono fijo',
            'celular'      => 'Celular / WhatsApp',
            'ciudad_id'    => 'Ciudad',
            'rubro'        => 'Rubro',
            'estado'       => 'Estado',
        ];
    }

    public function getCiudad(): ActiveQuery
    {
        return $this->hasOne(Ciudad::class, ['id' => 'ciudad_id']);
    }

    public function getCompras(): ActiveQuery
    {
        return $this->hasMany(Compra::class, ['proveedor_id' => 'id']);
    }
}
