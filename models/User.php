<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\BaseActiveRecord;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $email
 * @property string|null $role
 * @property string|null $tipo_usuario
 * @property int|null $status
 * @property int $status_id
 * @property int $role_id
 * @property int $user_type_id
 * @property string|null $foto
 * @property string|null $nombre
 * @property string|null $paterno
 * @property string|null $materno
 * @property string|null $ci
 * @property string|null $slug
 * @property string|null $fono
 * @property string|null $direccion
 * @property string|null $created_at
 * @property int|null $updated_at
 *
 * @property-read Role $roleRelation
 */
final class User extends ActiveRecord implements IdentityInterface
{
    public string $password = '';

    public const ROLE_USER_ADMIN = 'userAdmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERADMIN = 'superadmin';

    public const TIPO_USUARIO_VENTA = 'venta';
    public const TIPO_USUARIO_ALMACEN = 'almacen';
    public const TIPO_USUARIO_ENTREGA = 'entrega';
    public const TIPO_USUARIO_SUPERVISOR = 'supervisor';

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => static fn (): int => time(),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['username', 'email', 'password', 'tipo_usuario', 'status'], 'required', 'when' => static fn (self $model): bool => $model->isNewRecord],
            [['password'], 'required', 'when' => static fn (self $model): bool => $model->isNewRecord],
            [['password'], 'string', 'min' => 8],
            [['status_id', 'role_id', 'user_type_id', 'created_at', 'updated_at'], 'integer'],
            [['email'], 'string', 'max' => 150],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['role'], 'string', 'max' => 20],
            [['role'], 'in', 'range' => [self::ROLE_USER_ADMIN, self::ROLE_ADMIN, self::ROLE_SUPERADMIN]],
            [['tipo_usuario'], 'string', 'max' => 20],
            [['tipo_usuario'], 'in', 'range' => [self::TIPO_USUARIO_VENTA, self::TIPO_USUARIO_ALMACEN, self::TIPO_USUARIO_ENTREGA, self::TIPO_USUARIO_SUPERVISOR]],
            [['status'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['created_at'], 'safe'],
            [['username'], 'string', 'max' => 50],
            [['auth_key'], 'string', 'max' => 32],
            [['password_hash'], 'string', 'max' => 255],
            [['nombre', 'paterno', 'materno'], 'string', 'max' => 80],
            [['foto', 'slug'], 'string', 'max' => 255],
            [['ci'], 'string', 'max' => 30],
            [['fono'], 'string', 'max' => 30],
            [['direccion'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['slug'], 'unique'],
        ];
    }

    public function getRoleRelation(): ActiveQuery
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    public function getNombreCompleto(): string
    {
        $nombre = trim(implode(' ', array_filter([$this->nombre, $this->paterno, $this->materno])));
        return $nombre !== '' ? $nombre : (string)$this->username;
    }

    public function isGerente(): bool
    {
        return (int)$this->role_id === Role::GERENTE;
    }

    public function isEncargado(): bool
    {
        return (int)$this->role_id === Role::ALMACEN;
    }

    public function isVendedor(): bool
    {
        return (int)$this->role_id === Role::VENDEDOR;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, (string)$this->password_hash);
    }

    public static function findIdentity($id): ?static
    {
        return static::findOne(['id' => $id, 'status_id' => 1]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        return null;
    }

    public static function findByUsername(string $username): ?static
    {
        return static::findOne(['username' => $username, 'status_id' => 1]);
    }

    public function getId(): int|string|null
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return (string)$this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    public function roleBadgeClass(): string
    {
        return match ((string)$this->getAttribute('role')) {
            self::ROLE_ADMIN => 'primary',
            self::ROLE_SUPERADMIN => 'danger',
            default => 'secondary',
        };
    }

    public function roleLabel(): string
    {
        return (string)$this->getAttribute('role');
    }

    public function setDefaultRole(): void
    {
        if ($this->getAttribute('role') === null || $this->getAttribute('role') === '') {
            $this->setAttribute('role', self::ROLE_USER_ADMIN);
        }
    }

    public function statusBadgeClass(): string
    {
        return (int)$this->status === self::STATUS_ACTIVE ? 'success' : 'danger';
    }

    public function statusLabel(): string
    {
        return (int)$this->status === self::STATUS_ACTIVE ? 'Activo' : 'Inactivo';
    }

    public function createdAtFormatted(string $format = 'd/m/Y H:i'): string
    {
        $value = $this->getAttribute('created_at');
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_numeric($value)) {
            return date($format, (int)$value);
        }

        $timestamp = strtotime((string)$value);
        return $timestamp !== false ? date($format, $timestamp) : (string)$value;
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Usuario',
            'password' => 'Contraseña',
            'auth_key' => 'Clave de autenticación',
            'password_hash' => 'Contraseña',
            'email' => 'Correo electrónico',
            'role' => 'Rol',
            'tipo_usuario' => 'Tipo de usuario',
            'status' => 'Estado',
            'status_id' => 'Estado ID',
            'role_id' => 'Rol ID',
            'user_type_id' => 'Tipo de usuario ID',
            'foto' => 'Foto',
            'nombre' => 'Nombre',
            'paterno' => 'Apellido paterno',
            'materno' => 'Apellido materno',
            'ci' => 'CI',
            'slug' => 'Slug',
            'fono' => 'Teléfono',
            'direccion' => 'Dirección',
            'created_at' => 'Fecha de creación',
            'updated_at' => 'Fecha de actualización',
        ];
    }
}
