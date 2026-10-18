-- Nota: en este proyecto Yii2 la tabla existente se llama `user`.
-- Si tu base de datos usa `users`, cambia el nombre de la tabla en este script.

ALTER TABLE `user`
    ADD COLUMN `role` ENUM('userAdmin', 'admin', 'superadmin') NOT NULL DEFAULT 'userAdmin' AFTER `email`,
    ADD COLUMN `tipo_usuario` ENUM('venta', 'almacen', 'entrega', 'supervisor') NOT NULL DEFAULT 'venta' AFTER `role`,
    ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 1 AFTER `tipo_usuario`,
    MODIFY COLUMN `email` VARCHAR(150) NOT NULL;