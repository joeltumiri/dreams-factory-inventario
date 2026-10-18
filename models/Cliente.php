<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * @property int         $id
 * @property string|null $propietario
 * @property string|null $nombre_completo
 * @property string|null $tipo_contacto
 * @property string|null $razon_social
 * @property string|null $zona
 * @property string|null $direccion
 * @property string|null $contacto
 * @property string|null $geo
 * @property int         $departamento_id
 * @property int         $ciudad_id
 * @property int         $tipo_id
 * @property string|null $nit
 * @property string|null $slug
 * @property string|null $nombre_factura
 * @property float|null  $latitud
 * @property float|null  $longitud
 * @property string|null $iframe
 * @property string|null $email
 * @property int|null    $created_by
 * @property int|null    $created_at
 * @property int|null    $updated_by
 * @property int|null    $updated_at
 *
 * @property-read Ciudad       $ciudad
 * @property-read Departamento $departamento
 */
final class Cliente extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%cliente}}';
    }

    public function rules(): array
    {
        return [
            [['departamento_id', 'ciudad_id', 'tipo_id'], 'required'],
            [['departamento_id', 'ciudad_id', 'tipo_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['latitud', 'longitud'], 'number'],
            [['geo', 'iframe'], 'string'],
            [['nombre_completo'], 'required'],
            [['propietario', 'contacto'], 'string', 'max' => 150],
            [['nombre_completo', 'razon_social', 'nombre_factura'], 'string', 'max' => 200],
            [['tipo_contacto'], 'in', 'range' => ['Propietario', 'Empleado']],
            [['tipo_contacto'], 'default', 'value' => 'Propietario'],
            [['zona'], 'string', 'max' => 120],
            [['direccion'], 'string', 'max' => 255],
            [['nit'], 'string', 'max' => 30],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 150],
            [['slug'], 'string', 'max' => 220],
            [['slug'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'propietario' => 'Propietario / Regente',
            'nombre_completo' => 'Nombre Completo',
            'tipo_contacto' => 'Tipo de Contacto',
            'razon_social' => 'Nombre de la Farmacia',
            'zona' => 'Zona',
            'direccion' => 'Dirección',
            'contacto' => 'Celular / WhatsApp',
            'nit' => 'NIT',
            'nombre_factura' => 'Nombre para Factura',
            'email' => 'Email',
            'departamento_id' => 'Departamento',
            'ciudad_id' => 'Ciudad',
            'tipo_id' => 'Tipo',
            'latitud' => 'Latitud',
            'longitud' => 'Longitud',
            'iframe' => 'Google Maps (iframe)',
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
            if (empty($this->slug)) {
                $base = Inflector::slug((string)($this->razon_social ?: $this->propietario ?: 'cliente'));
                $this->slug = $base . '-' . uniqid();
            }
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    /** Nombre para mostrar en listas. */
    public function getNombreDisplay(): string
    {
        return $this->razon_social ?: $this->propietario ?: 'Cliente #' . $this->id;
    }

    public function getCiudad(): ActiveQuery
    {
        return $this->hasOne(Ciudad::class, ['id' => 'ciudad_id']);
    }

    public function getDepartamento(): ActiveQuery
    {
        return $this->hasOne(Departamento::class, ['id' => 'departamento_id']);
    }

    public function getTipo(): ActiveQuery
    {
        return $this->hasOne(Tipo::class, ['id' => 'tipo_id']);
    }
}
