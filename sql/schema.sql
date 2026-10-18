7-- ============================================================
-- BASE DE DATOS: almacen
-- Sistema Dreams Factory - Villa Dolores, El Alto, Bolivia
-- Charset: utf8mb4 / collation: utf8mb4_spanish_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS `almacen`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `almacen`;

-- ----------------------------------------------------------------
-- ROLES
-- ----------------------------------------------------------------
CREATE TABLE `roles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(50)  NOT NULL,
  `descripcion` VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`nombre`,`descripcion`) VALUES
  ('Gerente',            'Acceso completo incluyendo BI y reportes'),
  ('Encargado Almacén',  'Compras, inventarios, proveedores y productos'),
  ('Vendedor',           'Ventas, clientes y consulta de productos');

-- ----------------------------------------------------------------
-- USUARIOS
-- ----------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)  NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email`         VARCHAR(100) DEFAULT NULL,
  `nombre`        VARCHAR(80)  NOT NULL,
  `apellido`      VARCHAR(80)  NOT NULL,
  `rol_id`        INT UNSIGNED NOT NULL,
  `estado`        TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1=activo 0=inactivo',
  `created_by`    INT UNSIGNED DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`    INT UNSIGNED DEFAULT NULL,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario de demostración: créalo con la contraseña que prefieras

-- ----------------------------------------------------------------
-- ALMACENES
-- ----------------------------------------------------------------
CREATE TABLE `almacenes` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(100) NOT NULL,
  `direccion`   VARCHAR(200) DEFAULT NULL,
  `contacto`    VARCHAR(100) DEFAULT NULL,
  `estado`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_by`  INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`  INT UNSIGNED DEFAULT NULL,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `almacenes` (`nombre`,`direccion`,`contacto`) VALUES
  ('Almacén Principal','Villa Dolores, El Alto, Bolivia','77000000');

-- ----------------------------------------------------------------
-- CATEGORIAS
-- ----------------------------------------------------------------
CREATE TABLE `categorias` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(80)  NOT NULL,
  `descripcion` VARCHAR(200) DEFAULT NULL,
  `estado`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_by`  INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`  INT UNSIGNED DEFAULT NULL,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categorias` (`nombre`) VALUES
  ('Pañales'),('Ropa de Bebé'),('Accesorios de Higiene'),
  ('Chupetes'),('Biberones'),('Accesorios Generales');

-- ----------------------------------------------------------------
-- UNIDADES DE MEDIDA
-- ----------------------------------------------------------------
CREATE TABLE `unidades` (
  `id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`  VARCHAR(50)  NOT NULL,
  `sigla`   VARCHAR(10)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `unidades` (`nombre`,`sigla`) VALUES
  ('Unidad','UND'),('Docena','DOC'),('Caja','CAJ'),('Pack','PCK'),('Bolsa','BOL');

-- ----------------------------------------------------------------
-- PRODUCTOS
-- ----------------------------------------------------------------
CREATE TABLE `productos` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo`          VARCHAR(20)  NOT NULL UNIQUE,
  `nombre`          VARCHAR(120) NOT NULL,
  `descripcion`     TEXT         DEFAULT NULL,
  `precio_compra`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `precio_venta`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `stock_actual`    INT          NOT NULL DEFAULT 0,
  `stock_minimo`    INT          NOT NULL DEFAULT 0,
  `unidad_id`       INT UNSIGNED NOT NULL,
  `categoria_id`    INT UNSIGNED NOT NULL,
  `estado`          TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1=activo 0=descontinuado',
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`      INT UNSIGNED DEFAULT NULL,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`unidad_id`)    REFERENCES `unidades`(`id`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- HISTORIAL PRECIOS VENTA
-- ----------------------------------------------------------------
CREATE TABLE `precios_venta` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_id`   INT UNSIGNED NOT NULL,
  `precio`        DECIMAL(12,2) NOT NULL,
  `fecha_inicio`  DATE NOT NULL,
  `fecha_fin`     DATE DEFAULT NULL,
  `created_by`    INT UNSIGNED DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- OFERTAS
-- ----------------------------------------------------------------
CREATE TABLE `ofertas` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`        VARCHAR(100) NOT NULL,
  `descripcion`   TEXT DEFAULT NULL,
  `fecha_inicio`  DATE NOT NULL,
  `fecha_fin`     DATE NOT NULL,
  `estado`        TINYINT(1) NOT NULL DEFAULT 1,
  `created_by`    INT UNSIGNED DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`    INT UNSIGNED DEFAULT NULL,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `detalle_oferta` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `oferta_id`   INT UNSIGNED NOT NULL,
  `producto_id` INT UNSIGNED NOT NULL,
  `precio_oferta` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`oferta_id`)   REFERENCES `ofertas`(`id`),
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- PROVEEDORES
-- ----------------------------------------------------------------
CREATE TABLE `proveedores` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `razon_social`  VARCHAR(150) NOT NULL,
  `contacto`      VARCHAR(100) DEFAULT NULL,
  `telefono`      VARCHAR(30)  DEFAULT NULL,
  `email`         VARCHAR(100) DEFAULT NULL,
  `direccion`     VARCHAR(200) DEFAULT NULL,
  `nit`           VARCHAR(20)  DEFAULT NULL,
  `notas`         TEXT         DEFAULT NULL,
  `estado`        TINYINT(1)   NOT NULL DEFAULT 1,
  `created_by`    INT UNSIGNED DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`    INT UNSIGNED DEFAULT NULL,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- GESTIONES (AÑO FISCAL)
-- ----------------------------------------------------------------
CREATE TABLE `gestiones` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `anio`        YEAR         NOT NULL UNIQUE,
  `cerrada`     TINYINT(1)   NOT NULL DEFAULT 0,
  `fecha_cierre` DATE        DEFAULT NULL,
  `created_by`  INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- COMPRAS
-- ----------------------------------------------------------------
CREATE TABLE `compras` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `proveedor_id`    INT UNSIGNED NOT NULL,
  `almacen_id`      INT UNSIGNED NOT NULL DEFAULT 1,
  `gestion_id`      INT UNSIGNED NOT NULL,
  `numero_doc`      VARCHAR(30)  DEFAULT NULL,
  `fecha`           DATE         NOT NULL,
  `importe_total`   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `estado_pago`     TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '0=pendiente 1=pagado',
  `notas`           TEXT         DEFAULT NULL,
  `usuario_id`      INT UNSIGNED NOT NULL,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`      INT UNSIGNED DEFAULT NULL,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`),
  FOREIGN KEY (`almacen_id`)   REFERENCES `almacenes`(`id`),
  FOREIGN KEY (`gestion_id`)   REFERENCES `gestiones`(`id`),
  FOREIGN KEY (`usuario_id`)   REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- DETALLE COMPRA
-- ----------------------------------------------------------------
CREATE TABLE `detalle_compra` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `compra_id`       INT UNSIGNED NOT NULL,
  `producto_id`     INT UNSIGNED NOT NULL,
  `cantidad`        INT          NOT NULL,
  `precio_unitario` DECIMAL(12,2) NOT NULL,
  `subtotal`        DECIMAL(14,2) NOT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`compra_id`)   REFERENCES `compras`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- LOTES (LIFO)
-- ----------------------------------------------------------------
CREATE TABLE `lotes` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_id`       INT UNSIGNED NOT NULL,
  `compra_id`         INT UNSIGNED NOT NULL,
  `detalle_compra_id` INT UNSIGNED NOT NULL,
  `fecha_ingreso`     DATE         NOT NULL,
  `cantidad_inicial`  INT          NOT NULL,
  `cantidad_disponible` INT        NOT NULL,
  `precio_unitario`   DECIMAL(12,2) NOT NULL,
  `estado`            TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1=activo 0=agotado',
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`producto_id`)       REFERENCES `productos`(`id`),
  FOREIGN KEY (`compra_id`)         REFERENCES `compras`(`id`),
  FOREIGN KEY (`detalle_compra_id`) REFERENCES `detalle_compra`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- DEPARTAMENTOS Y CIUDADES
-- ----------------------------------------------------------------
CREATE TABLE `departamentos` (
  `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(60)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `departamentos` (`nombre`) VALUES
  ('La Paz'),('Cochabamba'),('Santa Cruz'),('Oruro'),('Potosí'),
  ('Chuquisaca'),('Tarija'),('Beni'),('Pando');

CREATE TABLE `ciudades` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`           VARCHAR(80)  NOT NULL,
  `departamento_id`  INT UNSIGNED NOT NULL,
  `created_by`       INT UNSIGNED DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`       INT UNSIGNED DEFAULT NULL,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ciudades` (`nombre`,`departamento_id`) VALUES
  ('El Alto',1),('La Paz',1),('Cochabamba',2),('Santa Cruz',3);

-- ----------------------------------------------------------------
-- TIPOS DE CLIENTE
-- ----------------------------------------------------------------
CREATE TABLE `tipos_cliente` (
  `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tipos_cliente` (`nombre`) VALUES ('Farmacia'),('Tienda');

-- ----------------------------------------------------------------
-- CLIENTES
-- ----------------------------------------------------------------
CREATE TABLE `clientes` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `propietario`     VARCHAR(100) NOT NULL,
  `razon_social`    VARCHAR(150) NOT NULL,
  `zona`            VARCHAR(80)  DEFAULT NULL,
  `direccion`       VARCHAR(200) DEFAULT NULL,
  `contacto`        VARCHAR(80)  DEFAULT NULL,
  `telefono`        VARCHAR(30)  DEFAULT NULL,
  `email`           VARCHAR(100) DEFAULT NULL,
  `nit`             VARCHAR(20)  DEFAULT NULL,
  `latitud`         DECIMAL(10,7) DEFAULT NULL,
  `longitud`        DECIMAL(10,7) DEFAULT NULL,
  `tipo_cliente_id` INT UNSIGNED NOT NULL,
  `ciudad_id`       INT UNSIGNED NOT NULL,
  `estado`          TINYINT(1)   NOT NULL DEFAULT 1,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`      INT UNSIGNED DEFAULT NULL,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tipo_cliente_id`) REFERENCES `tipos_cliente`(`id`),
  FOREIGN KEY (`ciudad_id`)       REFERENCES `ciudades`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- ESTADOS DE VENTA / PAGO / TIPO PAGO
-- ----------------------------------------------------------------
CREATE TABLE `estados_venta` (
  `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(30)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `estados_venta` (`nombre`) VALUES ('Pendiente'),('Entregado'),('Cancelado');

CREATE TABLE `estados_pago` (
  `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(30)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `estados_pago` (`nombre`) VALUES ('Pagado'),('Pendiente'),('Parcial');

CREATE TABLE `tipos_pago` (
  `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(40)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tipos_pago` (`nombre`) VALUES ('Efectivo'),('Transferencia'),('Crédito');

-- ----------------------------------------------------------------
-- VENTAS
-- ----------------------------------------------------------------
CREATE TABLE `ventas` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id`      INT UNSIGNED NOT NULL,
  `usuario_id`      INT UNSIGNED NOT NULL,
  `gestion_id`      INT UNSIGNED NOT NULL,
  `fecha`           DATE         NOT NULL,
  `importe_total`   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `descuento`       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `estado_venta_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `estado_pago_id`  INT UNSIGNED NOT NULL DEFAULT 2,
  `tipo_pago_id`    INT UNSIGNED NOT NULL DEFAULT 1,
  `notas`           TEXT DEFAULT NULL,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`      INT UNSIGNED DEFAULT NULL,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`cliente_id`)      REFERENCES `clientes`(`id`),
  FOREIGN KEY (`usuario_id`)      REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`gestion_id`)      REFERENCES `gestiones`(`id`),
  FOREIGN KEY (`estado_venta_id`) REFERENCES `estados_venta`(`id`),
  FOREIGN KEY (`estado_pago_id`)  REFERENCES `estados_pago`(`id`),
  FOREIGN KEY (`tipo_pago_id`)    REFERENCES `tipos_pago`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- DETALLE VENTA
-- ----------------------------------------------------------------
CREATE TABLE `detalle_venta` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venta_id`        INT UNSIGNED NOT NULL,
  `producto_id`     INT UNSIGNED NOT NULL,
  `cantidad`        INT          NOT NULL,
  `precio_unitario` DECIMAL(12,2) NOT NULL,
  `subtotal`        DECIMAL(14,2) NOT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`venta_id`)    REFERENCES `ventas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- MOVIMIENTOS FIFO (trazabilidad de salidas por lote)
-- ----------------------------------------------------------------
CREATE TABLE `movimientos_fifo` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lote_id`           INT UNSIGNED NOT NULL,
  `detalle_venta_id`  INT UNSIGNED NOT NULL,
  `cantidad_descontada` INT        NOT NULL,
  `precio_compra`     DECIMAL(12,2) NOT NULL,
  `fecha`             DATE         NOT NULL,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`lote_id`)          REFERENCES `lotes`(`id`),
  FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalle_venta`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- PEDIDOS (estados de distribución)
-- ----------------------------------------------------------------
CREATE TABLE `estados_pedido` (
  `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(40)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `estados_pedido` (`nombre`) VALUES
  ('Por Enviar'),('Enviado'),('Entregado'),('Cancelado');

CREATE TABLE `pedidos` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venta_id`         INT UNSIGNED NOT NULL,
  `estado_pedido_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `fecha_pedido`     DATE         NOT NULL,
  `fecha_envio`      DATE         DEFAULT NULL,
  `fecha_entrega`    DATE         DEFAULT NULL,
  `usuario_id`       INT UNSIGNED NOT NULL,
  `notas`            TEXT DEFAULT NULL,
  `created_by`       INT UNSIGNED DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by`       INT UNSIGNED DEFAULT NULL,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`venta_id`)         REFERENCES `ventas`(`id`),
  FOREIGN KEY (`estado_pedido_id`) REFERENCES `estados_pedido`(`id`),
  FOREIGN KEY (`usuario_id`)       REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- VISITAS DE VENDEDORES
-- ----------------------------------------------------------------
CREATE TABLE `visitas` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id`  INT UNSIGNED NOT NULL,
  `usuario_id`  INT UNSIGNED NOT NULL,
  `fecha`       DATE         NOT NULL,
  `hora`        TIME         DEFAULT NULL,
  `latitud`     DECIMAL(10,7) DEFAULT NULL,
  `longitud`    DECIMAL(10,7) DEFAULT NULL,
  `observaciones` TEXT       DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
