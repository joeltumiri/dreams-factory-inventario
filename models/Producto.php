<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * @property int $id
 * @property int|null $numero
 * @property string|null $codigo
 * @property string $nombre
 * @property string|null $slug
 * @property string|null $descripcion
 * @property float $precio_compra
 * @property float $precio_venta
 * @property int $stock
 * @property int $stock_minimo
 * @property int $categoria_id
 * @property string|null $nota
 * @property int $activo
 *
 * @property-read Categoria $categoria
 */
final class Producto extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%producto}}';
    }

    public function rules(): array
    {
        return [
            [['nombre', 'categoria_id'], 'required'],
            [['descripcion'], 'string'],
            [['precio_compra', 'precio_venta'], 'default', 'value' => 0],
            [['stock', 'stock_minimo'], 'default', 'value' => 0],
            [['precio_compra', 'precio_venta'], 'number', 'min' => 0],
            [['stock', 'stock_minimo', 'categoria_id', 'numero', 'activo'], 'integer'],
            [['activo'], 'default', 'value' => 1],
            [['nombre'], 'string', 'max' => 200],
            [['codigo'], 'string', 'max' => 32],
            [['slug'], 'string', 'max' => 220],
            [['nota'], 'string', 'max' => 255],
            [['codigo'], 'unique'],
            [['slug'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'codigo' => 'Código',
            'nombre' => 'Nombre',
            'categoria_id' => 'Categoría',
            'precio_compra' => 'Precio compra',
            'precio_venta' => 'Precio venta',
            'stock' => 'Stock',
            'stock_minimo' => 'Stock mínimo',
            'descripcion' => 'Descripción',
            'nota' => 'Nota',
            'activo' => 'Estado',
        ];
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if ($this->precio_compra === null || $this->precio_compra === '') {
            $this->precio_compra = 0;
        }
        if ($this->precio_venta === null || $this->precio_venta === '') {
            $this->precio_venta = 0;
        }
        if ($this->stock === null || $this->stock === '') {
            $this->stock = 0;
        }
        if ($this->stock_minimo === null || $this->stock_minimo === '') {
            $this->stock_minimo = 0;
        }

        if ($this->slug === null || $this->slug === '') {
            $this->slug = Inflector::slug((string)$this->nombre);
        }
        return true;
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = time();
        if ($insert && ($this->created_at === null || $this->created_at === 0)) {
            $this->created_at = $now;
            $this->created_by = (Yii::$app->has('user') && Yii::$app->user && !Yii::$app->user->isGuest)
                ? (int)Yii::$app->user->id
                : ($this->created_by ?? null);
        }

        $this->updated_at = $now;
        $this->updated_by = (Yii::$app->has('user') && Yii::$app->user && !Yii::$app->user->isGuest)
            ? (int)Yii::$app->user->id
            : ($this->updated_by ?? null);

        return true;
    }

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $updates = [];
            if ($this->numero === null) {
                $updates['numero'] = $this->id;
            }
            if ($this->codigo === null || $this->codigo === '') {
                $updates['codigo'] = 'BG-P' . $this->id;
            }
            if ($updates !== []) {
                $this->updateAttributes($updates);
            }
        }
    }

    public function getCategoria(): ActiveQuery
    {
        return $this->hasOne(Categoria::class, ['id' => 'categoria_id']);
    }

    /** Detalles de compra (historial de compras del producto). */
    public function getDetallesCompra(): ActiveQuery
    {
        return $this->hasMany(DetalleCompra::class, ['producto_id' => 'id']);
    }

    /** Detalles de venta (historial de ventas del producto). */
    public function getDetallesVenta(): ActiveQuery
    {
        return $this->hasMany(DetalleVenta::class, ['producto_id' => 'id']);
    }
}
