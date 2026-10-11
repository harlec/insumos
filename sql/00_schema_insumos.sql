-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 11-10-2026 a las 16:25:56
-- Versión del servidor: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- Versión de PHP: 8.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `admin_insumos` (esquema base heredado de Renacer; ejecutar luego 01_modulos_permisos.sql)
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `abonos`
--

CREATE TABLE `abonos` (
  `id_abono` int(11) NOT NULL,
  `cliente` int(3) NOT NULL,
  `monto` varchar(5) NOT NULL,
  `motivo` varchar(60) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id_asistencia` int(10) UNSIGNED NOT NULL,
  `id_empleado` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada_prog` time DEFAULT NULL,
  `hora_entrada_real` time DEFAULT NULL,
  `hora_salida_prog` time DEFAULT NULL,
  `hora_salida_real` time DEFAULT NULL,
  `minutos_tardanza` int(11) NOT NULL DEFAULT 0,
  `horas_trabajadas` decimal(5,2) DEFAULT NULL,
  `observacion` varchar(20) DEFAULT NULL COMMENT 'PUNTUAL, RETARDADO o FALTO',
  `usuario` int(11) DEFAULT NULL,
  `feriado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = trabajó en feriado (se paga doble ese día)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_periodos`
--

CREATE TABLE `caja_periodos` (
  `id_cperiodo` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `caja_inicial` decimal(12,2) NOT NULL DEFAULT 0.00,
  `usuario` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_periodo_saldos`
--

CREATE TABLE `caja_periodo_saldos` (
  `id_cperiodo` int(10) UNSIGNED NOT NULL,
  `metodo` varchar(30) NOT NULL,
  `monto` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id_cargo` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `estado` enum('1','0') NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nom_cat` varchar(45) DEFAULT NULL,
  `estado` enum('0','1') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `cliente` varchar(45) DEFAULT NULL,
  `doc_identidad` varchar(45) DEFAULT NULL,
  `telefono` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `estado` enum('0','1') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_fusiones`
--

CREATE TABLE `cliente_fusiones` (
  `id_fusion` int(10) UNSIGNED NOT NULL,
  `id_principal` int(10) UNSIGNED NOT NULL,
  `id_duplicado` int(10) UNSIGNED NOT NULL,
  `ventas_ids` mediumtext DEFAULT NULL COMMENT 'ids de ventas movidas (JSON)',
  `preventa_ids` mediumtext DEFAULT NULL COMMENT 'ids de preventas movidas (JSON)',
  `datos_rellenados` text DEFAULT NULL COMMENT 'doc/telefono/email que el principal heredó del duplicado (JSON)',
  `usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `deshecha` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `color`
--

CREATE TABLE `color` (
  `id_color` int(11) NOT NULL,
  `color` varchar(100) NOT NULL,
  `estado` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id_compra` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_despacho` date NOT NULL,
  `guia` varchar(20) NOT NULL,
  `serie_f` varchar(6) NOT NULL,
  `numero_f` varchar(10) NOT NULL,
  `total` varchar(45) DEFAULT NULL,
  `moneda` int(1) NOT NULL,
  `proveedor` int(11) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL,
  `observacion` varchar(300) NOT NULL,
  `exonerada` enum('no','si') NOT NULL DEFAULT 'no',
  `estado` enum('0','1','2') DEFAULT NULL,
  `forma_pago` enum('contado','credito') NOT NULL DEFAULT 'contado' COMMENT 'contado: se registra pagada de una vez al ingresar la compra. credito: queda pendiente en Cuentas x pagar',
  `deuda_anterior` char(1) NOT NULL DEFAULT '0',
  `fecha_compromiso_pago` date DEFAULT NULL COMMENT 'Solo aplica si forma_pago = credito: fecha en la que se compromete el pago al proveedor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_pagos`
--

CREATE TABLE `compra_pagos` (
  `id_pago` int(10) UNSIGNED NOT NULL,
  `compra` int(10) UNSIGNED NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo` varchar(30) NOT NULL DEFAULT 'efectivo',
  `usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes`
--

CREATE TABLE `comprobantes` (
  `id_comprobante` int(11) NOT NULL,
  `serie` varchar(5) DEFAULT NULL,
  `numero` varchar(45) DEFAULT NULL,
  `url` varchar(100) NOT NULL,
  `tipo` varchar(2) DEFAULT NULL,
  `venta` int(11) DEFAULT NULL,
  `tipo_doc` varchar(3) DEFAULT NULL,
  `doc` varchar(45) DEFAULT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `moneda` varchar(45) DEFAULT NULL,
  `tipo_cambio` varchar(5) DEFAULT NULL,
  `grabada` varchar(45) DEFAULT NULL,
  `igv` varchar(45) DEFAULT NULL,
  `total` varchar(10) NOT NULL,
  `fecha` date DEFAULT NULL,
  `state` varchar(200) DEFAULT NULL,
  `comprobante_modificado` int(10) UNSIGNED DEFAULT NULL COMMENT 'id_comprobante original que esta nota de crédito anula o corrige'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobante_ventas`
--

CREATE TABLE `comprobante_ventas` (
  `id` int(10) UNSIGNED NOT NULL,
  `comprobante` int(10) UNSIGNED NOT NULL,
  `venta` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id_parametro` int(11) NOT NULL,
  `parametro` varchar(50) NOT NULL,
  `valor` varchar(200) NOT NULL,
  `estado_parametro` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compras`
--

CREATE TABLE `detalle_compras` (
  `id_de_compra` int(11) NOT NULL,
  `compra` int(11) DEFAULT NULL,
  `producto` int(11) DEFAULT NULL,
  `cantidad` varchar(45) DEFAULT NULL,
  `precio` varchar(45) DEFAULT NULL,
  `total` varchar(45) DEFAULT NULL,
  `estado` enum('0','1') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_migracion`
--

CREATE TABLE `detalle_migracion` (
  `id_detallem` int(11) NOT NULL,
  `migracion` int(8) NOT NULL,
  `producto` varchar(4) NOT NULL,
  `cantidad` varchar(4) NOT NULL,
  `origen` varchar(2) NOT NULL,
  `estado` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_preventa`
--

CREATE TABLE `detalle_preventa` (
  `id_detalle` int(11) NOT NULL,
  `preventa` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `id_vp` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `estado` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_proforma`
--

CREATE TABLE `detalle_proforma` (
  `id_detalle` int(11) NOT NULL,
  `venta` int(11) DEFAULT NULL,
  `producto` int(11) DEFAULT NULL,
  `cantidad` varchar(45) DEFAULT NULL,
  `precio` varchar(45) DEFAULT NULL,
  `total` varchar(45) DEFAULT NULL,
  `estado` enum('0','1') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id_detalle` int(11) NOT NULL,
  `venta` int(11) DEFAULT NULL,
  `producto` int(11) DEFAULT NULL,
  `id_vp` int(11) NOT NULL,
  `cantidad` varchar(45) DEFAULT NULL,
  `precio` varchar(45) DEFAULT NULL,
  `total` varchar(45) DEFAULT NULL,
  `estado` enum('0','1') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int(11) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellidos` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `celular` varchar(15) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `ubicacion` int(1) NOT NULL,
  `puntos` varchar(6) NOT NULL,
  `estado` enum('0','1') NOT NULL,
  `cargo` varchar(60) DEFAULT NULL COMMENT 'Puesto/ocupación, ej. BOLETEADORA',
  `hora_ingreso` time DEFAULT NULL COMMENT 'Horario programado de entrada',
  `hora_salida` time DEFAULT NULL COMMENT 'Horario programado de salida',
  `sueldo_mensual` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Sueldo mensual base; el pago es quincenal (cada 15 días)',
  `hora_ingreso_sab` time DEFAULT NULL,
  `hora_salida_sab` time DEFAULT NULL,
  `hora_ingreso_dom` time DEFAULT NULL,
  `hora_salida_dom` time DEFAULT NULL,
  `afp` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Si el colaborador tiene descuento de AFP',
  `afp_monto_mensual` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto mensual de descuento AFP; se aplica la mitad en cada quincena',
  `fecha_ingreso` date DEFAULT NULL COMMENT 'Fecha en que el colaborador ingresó a trabajar',
  `asignacion_familiar` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Si el colaborador recibe asignación familiar (10% de la RMV)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado_descansos`
--

CREATE TABLE `empleado_descansos` (
  `id_descanso` int(10) UNSIGNED NOT NULL,
  `id_empleado` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `usuario` int(11) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equivalencias`
--

CREATE TABLE `equivalencias` (
  `id_equivalencia` int(11) NOT NULL,
  `equivalencia` varchar(4) NOT NULL,
  `cantidad` varchar(4) NOT NULL,
  `equivale` varchar(4) NOT NULL,
  `categoria` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id_gasto` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('fijo','diario','puntual') NOT NULL DEFAULT 'puntual',
  `categoria` varchar(60) NOT NULL COMMENT 'ej. Luz, Alquiler, Pasajes',
  `descripcion` varchar(160) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo` varchar(30) NOT NULL DEFAULT 'efectivo',
  `usuario` int(11) NOT NULL,
  `estado` char(1) NOT NULL DEFAULT '1' COMMENT '1 activo, 2 anulado',
  `fecha_registro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `estado` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migracion`
--

CREATE TABLE `migracion` (
  `id_migracion` int(11) NOT NULL,
  `destino` varchar(2) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_empleado`
--

CREATE TABLE `movimientos_empleado` (
  `id_movimiento` int(10) UNSIGNED NOT NULL,
  `id_empleado` int(10) UNSIGNED NOT NULL,
  `tipo` enum('adelanto','abarrotes') NOT NULL,
  `fecha` date NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `descripcion` varchar(120) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `id_venta` int(10) UNSIGNED DEFAULT NULL,
  `partes` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Cantidad de cuotas en las que se descuenta este movimiento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_cuotas`
--

CREATE TABLE `movimiento_cuotas` (
  `id_cuota` int(10) UNSIGNED NOT NULL,
  `id_movimiento` int(10) UNSIGNED NOT NULL,
  `numero_cuota` int(10) UNSIGNED NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `id_detalle_aplicado` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planilla_descuentos`
--

CREATE TABLE `planilla_descuentos` (
  `id_descuento` int(10) UNSIGNED NOT NULL,
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `tipo` enum('tardanza','abarrotes','adelanto','falta','prestamo','afp') NOT NULL,
  `fecha` date NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `descripcion` varchar(120) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planilla_detalle`
--

CREATE TABLE `planilla_detalle` (
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `id_periodo` int(10) UNSIGNED NOT NULL,
  `id_empleado` int(10) UNSIGNED NOT NULL,
  `sueldo_mensual` decimal(10,2) NOT NULL COMMENT 'snapshot al generar la planilla',
  `calculo_diario` decimal(10,2) NOT NULL COMMENT 'sueldo_mensual / 15',
  `sueldo_periodo` decimal(10,2) NOT NULL COMMENT 'calculo_diario * dias del periodo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planilla_ingresos`
--

CREATE TABLE `planilla_ingresos` (
  `id_ingreso` int(10) UNSIGNED NOT NULL,
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `tipo` enum('feriado','asignacion_familiar') NOT NULL,
  `fecha` date NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `descripcion` varchar(120) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planilla_periodos`
--

CREATE TABLE `planilla_periodos` (
  `id_periodo` int(10) UNSIGNED NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `dias` int(10) UNSIGNED NOT NULL COMMENT 'DATEDIFF(fecha_fin,fecha_inicio)+1',
  `estado` enum('abierto','cerrado') NOT NULL DEFAULT 'abierto',
  `fecha_creacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planilla_periodo_plantillas`
--

CREATE TABLE `planilla_periodo_plantillas` (
  `id_plantilla` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `dia_inicio` tinyint(3) UNSIGNED NOT NULL,
  `dia_fin_tipo` enum('fijo','fin_mes') NOT NULL DEFAULT 'fijo',
  `dia_fin` tinyint(3) UNSIGNED DEFAULT NULL,
  `estado` enum('1','0') NOT NULL DEFAULT '1',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preventa`
--

CREATE TABLE `preventa` (
  `id_preventa` int(11) NOT NULL,
  `cliente` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `estado` enum('0','1','2') NOT NULL DEFAULT '0',
  `origen` enum('manual','ia') NOT NULL DEFAULT 'manual',
  `texto_ia` text DEFAULT NULL,
  `venta_generada` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `codigo_producto` varchar(20) NOT NULL,
  `cod_sunat` varchar(12) NOT NULL,
  `serie` varchar(20) NOT NULL,
  `nom_prod` varchar(200) DEFAULT NULL,
  `des_prod` varchar(200) NOT NULL,
  `unidad_prod` int(11) DEFAULT NULL,
  `categoria` int(11) DEFAULT NULL,
  `precio_compra` varchar(45) DEFAULT NULL,
  `precio_venta` varchar(45) DEFAULT NULL,
  `stockp` decimal(10,3) DEFAULT NULL,
  `exonerada` enum('no','si') NOT NULL DEFAULT 'no',
  `proveedor` int(11) DEFAULT NULL,
  `estado` enum('0','1') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_alias`
--

CREATE TABLE `producto_alias` (
  `id_alias` int(11) NOT NULL,
  `alias` varchar(191) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proforma`
--

CREATE TABLE `proforma` (
  `id_venta` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `fecha_ope` datetime NOT NULL,
  `total` varchar(45) DEFAULT NULL,
  `cliente` int(11) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL,
  `tipo` varchar(1) NOT NULL,
  `estado` enum('0','1','2') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `proveedor` varchar(200) DEFAULT NULL,
  `doc_identidad` varchar(45) DEFAULT NULL,
  `direccion` varchar(200) NOT NULL,
  `telefono` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `estado` enum('0','1') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Puntos`
--

CREATE TABLE `Puntos` (
  `id_punto` int(11) NOT NULL,
  `empleado` int(2) NOT NULL,
  `venta` int(7) NOT NULL,
  `puntos` varchar(6) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sms`
--

CREATE TABLE `sms` (
  `id_sms` int(11) NOT NULL,
  `sms` varchar(2000) NOT NULL,
  `otro` varchar(100) NOT NULL,
  `state` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock`
--

CREATE TABLE `stock` (
  `id_stock` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `ingreso` varchar(20) NOT NULL,
  `egreso` varchar(20) NOT NULL,
  `stock` varchar(20) NOT NULL,
  `motivo` varchar(30) NOT NULL,
  `fv` date NOT NULL,
  `stockt` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablet_groups`
--

CREATE TABLE `tablet_groups` (
  `id` int(11) NOT NULL,
  `tab_id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablet_group_categories`
--

CREATE TABLE `tablet_group_categories` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablet_group_products`
--

CREATE TABLE `tablet_group_products` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `all_variants` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablet_group_product_variants`
--

CREATE TABLE `tablet_group_product_variants` (
  `id` int(11) NOT NULL,
  `group_product_id` int(11) NOT NULL,
  `variant_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablet_tabs`
--

CREATE TABLE `tablet_tabs` (
  `id` int(11) NOT NULL,
  `tab_key` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `icon` varchar(20) DEFAULT '',
  `color_accent` varchar(30) DEFAULT '#f5a623',
  `color_bg` varchar(80) DEFAULT 'rgba(245,166,35,.12)',
  `by_weight` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidades`
--

CREATE TABLE `unidades` (
  `id_unidad` int(11) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  `codigo` varchar(5) NOT NULL,
  `cantidad` varchar(45) DEFAULT NULL,
  `estado` enum('0','1') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `unidades`
--

INSERT INTO `unidades` (`id_unidad`, `nombre`, `codigo`, `cantidad`, `estado`) VALUES
(1, 'GALON', 'WG', '1', '1'),
(2, 'LITRO', 'LTR', '1', '1'),
(3, 'UNIDADES', 'NIU', '1', '1'),
(4, 'OTROS', 'ZZ', '1', '1'),
(5, 'KILOGRAMO', 'KGM', '1', '1'),
(6, 'CAJA', 'BX', '1', '1'),
(7, 'BOLSA', 'BG', '1', '1'),
(8, 'DOCENA', 'DZN', '12', '1'),
(9, 'BARRIL', 'KG', '1', '1'),
(10, 'GRAMO', 'GRM', '1', '1'),
(11, 'TONELADA', 'TNE', '1', '1'),
(12, 'PAR', 'PR', '1', '1'),
(13, 'ROLLO', 'RO', '1', '1'),
(14, 'METRO', 'MTR', '1', '1'),
(15, 'PAQUETE', 'PK', '1', '1'),
(16, 'BOTELLA', 'BO', '1', '1'),
(17, 'METRO CÚBICO', 'MTQ', '1', '1'),
(18, 'MIL', 'MIL', '1', '1'),
(19, 'SACO', 'SA', '1', '1'),
(20, 'ENVELOPE', 'EV', '1', '1'),
(21, 'JAR', 'JR', '1', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(15) DEFAULT NULL,
  `clave` varchar(100) DEFAULT NULL,
  `rol` varchar(20) NOT NULL,
  `nombres` varchar(45) DEFAULT NULL,
  `tienda` int(2) NOT NULL,
  `correo` varchar(45) DEFAULT NULL,
  `estado` enum('0','1') DEFAULT NULL,
  `tablet_mode` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variantes`
--

CREATE TABLE `variantes` (
  `id_variante` int(11) NOT NULL,
  `variante` varchar(100) NOT NULL,
  `cantidad` varchar(6) NOT NULL,
  `estado` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variante_p`
--

CREATE TABLE `variante_p` (
  `id_vp` int(11) NOT NULL,
  `producto_vp` int(11) NOT NULL,
  `variante_vp` int(11) NOT NULL,
  `cantidad_vp` varchar(7) NOT NULL,
  `precio_vp` varchar(7) NOT NULL,
  `precioc_vp` decimal(10,2) NOT NULL DEFAULT 0.00,
  `state_vp` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `fecha_ope` datetime NOT NULL,
  `total` varchar(45) DEFAULT NULL,
  `cliente` int(11) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL,
  `estado` enum('0','1','2') DEFAULT NULL,
  `fecha_compromiso_pago` date DEFAULT NULL COMMENT 'Si no es NULL, la venta está "a crédito": el cliente pagará después, en esta fecha aproximada',
  `id_empleado` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_pagos`
--

CREATE TABLE `venta_pagos` (
  `id_pago` int(11) NOT NULL,
  `venta` int(11) NOT NULL,
  `metodo` enum('efectivo','yape','plin','bbva','yape_susan','tarjeta','planilla') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `abonos`
--
ALTER TABLE `abonos`
  ADD PRIMARY KEY (`id_abono`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD UNIQUE KEY `uq_emp_fecha` (`id_empleado`,`fecha`);

--
-- Indices de la tabla `caja_periodos`
--
ALTER TABLE `caja_periodos`
  ADD PRIMARY KEY (`id_cperiodo`);

--
-- Indices de la tabla `caja_periodo_saldos`
--
ALTER TABLE `caja_periodo_saldos`
  ADD PRIMARY KEY (`id_cperiodo`,`metodo`);

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id_cargo`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `cliente_fusiones`
--
ALTER TABLE `cliente_fusiones`
  ADD PRIMARY KEY (`id_fusion`),
  ADD KEY `idx_dup` (`id_duplicado`),
  ADD KEY `idx_principal` (`id_principal`);

--
-- Indices de la tabla `color`
--
ALTER TABLE `color`
  ADD PRIMARY KEY (`id_color`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `proveedor_idx` (`proveedor`),
  ADD KEY `usuario_idx` (`usuario`);

--
-- Indices de la tabla `compra_pagos`
--
ALTER TABLE `compra_pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `idx_compra` (`compra`);

--
-- Indices de la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  ADD PRIMARY KEY (`id_comprobante`),
  ADD KEY `venta_idx` (`venta`);

--
-- Indices de la tabla `comprobante_ventas`
--
ALTER TABLE `comprobante_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comprobante` (`comprobante`),
  ADD KEY `idx_venta` (`venta`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id_parametro`);

--
-- Indices de la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD PRIMARY KEY (`id_de_compra`),
  ADD KEY `producto_idx` (`producto`),
  ADD KEY `compra_idx` (`compra`);

--
-- Indices de la tabla `detalle_migracion`
--
ALTER TABLE `detalle_migracion`
  ADD PRIMARY KEY (`id_detallem`);

--
-- Indices de la tabla `detalle_preventa`
--
ALTER TABLE `detalle_preventa`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_detalle_preventa` (`preventa`);

--
-- Indices de la tabla `detalle_proforma`
--
ALTER TABLE `detalle_proforma`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `venta_idx` (`venta`),
  ADD KEY `producto_idx` (`producto`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `venta_idx` (`venta`),
  ADD KEY `producto_idx` (`producto`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD UNIQUE KEY `id_empleado` (`id_empleado`);

--
-- Indices de la tabla `empleado_descansos`
--
ALTER TABLE `empleado_descansos`
  ADD PRIMARY KEY (`id_descanso`),
  ADD UNIQUE KEY `uq_emp_fecha` (`id_empleado`,`fecha`);

--
-- Indices de la tabla `equivalencias`
--
ALTER TABLE `equivalencias`
  ADD PRIMARY KEY (`id_equivalencia`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id_gasto`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id_marca`);

--
-- Indices de la tabla `migracion`
--
ALTER TABLE `migracion`
  ADD PRIMARY KEY (`id_migracion`);

--
-- Indices de la tabla `movimientos_empleado`
--
ALTER TABLE `movimientos_empleado`
  ADD PRIMARY KEY (`id_movimiento`);

--
-- Indices de la tabla `movimiento_cuotas`
--
ALTER TABLE `movimiento_cuotas`
  ADD PRIMARY KEY (`id_cuota`),
  ADD KEY `idx_movimiento` (`id_movimiento`);

--
-- Indices de la tabla `planilla_descuentos`
--
ALTER TABLE `planilla_descuentos`
  ADD PRIMARY KEY (`id_descuento`),
  ADD KEY `idx_detalle` (`id_detalle`);

--
-- Indices de la tabla `planilla_detalle`
--
ALTER TABLE `planilla_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD UNIQUE KEY `uq_periodo_emp` (`id_periodo`,`id_empleado`),
  ADD KEY `idx_periodo` (`id_periodo`);

--
-- Indices de la tabla `planilla_ingresos`
--
ALTER TABLE `planilla_ingresos`
  ADD PRIMARY KEY (`id_ingreso`),
  ADD KEY `idx_detalle` (`id_detalle`);

--
-- Indices de la tabla `planilla_periodos`
--
ALTER TABLE `planilla_periodos`
  ADD PRIMARY KEY (`id_periodo`);

--
-- Indices de la tabla `planilla_periodo_plantillas`
--
ALTER TABLE `planilla_periodo_plantillas`
  ADD PRIMARY KEY (`id_plantilla`);

--
-- Indices de la tabla `preventa`
--
ALTER TABLE `preventa`
  ADD PRIMARY KEY (`id_preventa`),
  ADD KEY `idx_preventa_estado` (`estado`),
  ADD KEY `idx_preventa_cliente` (`cliente`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `hugo_idx` (`categoria`),
  ADD KEY `unidad_idx` (`unidad_prod`),
  ADD KEY `proveedor_idx` (`proveedor`);

--
-- Indices de la tabla `producto_alias`
--
ALTER TABLE `producto_alias`
  ADD PRIMARY KEY (`id_alias`),
  ADD KEY `idx_producto_alias_producto` (`id_producto`);

--
-- Indices de la tabla `proforma`
--
ALTER TABLE `proforma`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `usuario_idx` (`usuario`),
  ADD KEY `cliente_idx` (`cliente`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `Puntos`
--
ALTER TABLE `Puntos`
  ADD PRIMARY KEY (`id_punto`);

--
-- Indices de la tabla `sms`
--
ALTER TABLE `sms`
  ADD PRIMARY KEY (`id_sms`);

--
-- Indices de la tabla `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id_stock`);

--
-- Indices de la tabla `tablet_groups`
--
ALTER TABLE `tablet_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tab` (`tab_id`);

--
-- Indices de la tabla `tablet_group_categories`
--
ALTER TABLE `tablet_group_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_gc` (`group_id`,`category_id`),
  ADD KEY `idx_grp` (`group_id`);

--
-- Indices de la tabla `tablet_group_products`
--
ALTER TABLE `tablet_group_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_gp` (`group_id`,`product_id`),
  ADD KEY `idx_grp` (`group_id`);

--
-- Indices de la tabla `tablet_group_product_variants`
--
ALTER TABLE `tablet_group_product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_gpv` (`group_product_id`,`variant_type_id`),
  ADD KEY `idx_gp` (`group_product_id`);

--
-- Indices de la tabla `tablet_tabs`
--
ALTER TABLE `tablet_tabs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tab_key` (`tab_key`);

--
-- Indices de la tabla `unidades`
--
ALTER TABLE `unidades`
  ADD PRIMARY KEY (`id_unidad`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `variantes`
--
ALTER TABLE `variantes`
  ADD PRIMARY KEY (`id_variante`);

--
-- Indices de la tabla `variante_p`
--
ALTER TABLE `variante_p`
  ADD PRIMARY KEY (`id_vp`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `usuario_idx` (`usuario`),
  ADD KEY `cliente_idx` (`cliente`);

--
-- Indices de la tabla `venta_pagos`
--
ALTER TABLE `venta_pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `idx_venta` (`venta`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `abonos`
--
ALTER TABLE `abonos`
  MODIFY `id_abono` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id_asistencia` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_periodos`
--
ALTER TABLE `caja_periodos`
  MODIFY `id_cperiodo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id_cargo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente_fusiones`
--
ALTER TABLE `cliente_fusiones`
  MODIFY `id_fusion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `color`
--
ALTER TABLE `color`
  MODIFY `id_color` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compra_pagos`
--
ALTER TABLE `compra_pagos`
  MODIFY `id_pago` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  MODIFY `id_comprobante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comprobante_ventas`
--
ALTER TABLE `comprobante_ventas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id_parametro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  MODIFY `id_de_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_migracion`
--
ALTER TABLE `detalle_migracion`
  MODIFY `id_detallem` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_preventa`
--
ALTER TABLE `detalle_preventa`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_proforma`
--
ALTER TABLE `detalle_proforma`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empleado_descansos`
--
ALTER TABLE `empleado_descansos`
  MODIFY `id_descanso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equivalencias`
--
ALTER TABLE `equivalencias`
  MODIFY `id_equivalencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migracion`
--
ALTER TABLE `migracion`
  MODIFY `id_migracion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_empleado`
--
ALTER TABLE `movimientos_empleado`
  MODIFY `id_movimiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimiento_cuotas`
--
ALTER TABLE `movimiento_cuotas`
  MODIFY `id_cuota` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planilla_descuentos`
--
ALTER TABLE `planilla_descuentos`
  MODIFY `id_descuento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planilla_detalle`
--
ALTER TABLE `planilla_detalle`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planilla_ingresos`
--
ALTER TABLE `planilla_ingresos`
  MODIFY `id_ingreso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planilla_periodos`
--
ALTER TABLE `planilla_periodos`
  MODIFY `id_periodo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planilla_periodo_plantillas`
--
ALTER TABLE `planilla_periodo_plantillas`
  MODIFY `id_plantilla` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preventa`
--
ALTER TABLE `preventa`
  MODIFY `id_preventa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto_alias`
--
ALTER TABLE `producto_alias`
  MODIFY `id_alias` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proforma`
--
ALTER TABLE `proforma`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Puntos`
--
ALTER TABLE `Puntos`
  MODIFY `id_punto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sms`
--
ALTER TABLE `sms`
  MODIFY `id_sms` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `stock`
--
ALTER TABLE `stock`
  MODIFY `id_stock` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tablet_groups`
--
ALTER TABLE `tablet_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tablet_group_categories`
--
ALTER TABLE `tablet_group_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tablet_group_products`
--
ALTER TABLE `tablet_group_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tablet_group_product_variants`
--
ALTER TABLE `tablet_group_product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tablet_tabs`
--
ALTER TABLE `tablet_tabs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `unidades`
--
ALTER TABLE `unidades`
  MODIFY `id_unidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `variantes`
--
ALTER TABLE `variantes`
  MODIFY `id_variante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `variante_p`
--
ALTER TABLE `variante_p`
  MODIFY `id_vp` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `venta_pagos`
--
ALTER TABLE `venta_pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `proveedor1` FOREIGN KEY (`proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usuario1` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  ADD CONSTRAINT `venta_compro` FOREIGN KEY (`venta`) REFERENCES `ventas` (`id_venta`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD CONSTRAINT `compra` FOREIGN KEY (`compra`) REFERENCES `compras` (`id_compra`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `producto3` FOREIGN KEY (`producto`) REFERENCES `productos` (`id_producto`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `producto` FOREIGN KEY (`producto`) REFERENCES `productos` (`id_producto`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `venta` FOREIGN KEY (`venta`) REFERENCES `ventas` (`id_venta`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `hugo` FOREIGN KEY (`categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proveedor` FOREIGN KEY (`proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `unidad` FOREIGN KEY (`unidad_prod`) REFERENCES `unidades` (`id_unidad`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `producto_alias`
--
ALTER TABLE `producto_alias`
  ADD CONSTRAINT `fk_producto_alias_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `cliente2` FOREIGN KEY (`cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usuario2` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
