<?php
// Página -> módulo. Una página que no figura aquí solo exige sesión iniciada.
// La acción se deduce del prefijo del archivo (ver permisos_accion_de_archivo en permisos.php).
return [
	'dashboard'     => ['dashboard.php'],
	'usuarios'      => ['ver_usuarios.php', 'agregar_usuario.php', 'editar_usuario.php'],
	'clientes'      => ['ver_clientes.php', 'ver_cliente.php', 'agregar_cliente.php', 'editar_cliente.php', 'clientes_duplicados.php'],
	'productos'     => ['ver_productos.php', 'agregar_producto.php', 'editar_producto.php', 'agregar_stock.php',
	                    'categorias.php', 'marcas.php', 'variantes.php', 'alias_productos.php', 'revisar_costos_variantes.php',
	                    'migracion.php', 'migraciones.php', 'ver_migracion.php', 'ver_ajustes.php'],
	'ventas'        => ['venta.php', 'ventas.php', 'editar_venta.php', 'notas_venta.php', 'venta_m.php', 'ventas_m.php', 'ventap.php',
	                    'ver_venta.php', 'recibo.php', 'recibop.php', 'ver_proforma.php', 'verificar_anulacion.php'],
	'caja'          => ['caja_pagos.php', 'cuentas_x_pagar.php'],
	'compras'       => ['compra.php', 'compras.php', 'ver_compra.php', 'descargar_cmp.php'],
	'proveedores'   => ['proveedores.php'],
	'gastos'        => ['gastos.php', 'balance.php'],
	'reportes'      => ['reportes.php', 'reporte_c.php', 'reporte_compras.php', 'reporte_evolucion.php', 'reporte_huevos.php',
	                    'reporte_k.php', 'reporte_kardex.php', 'reporte_mv.php', 'reporte_mvk.php', 'reporte_prediccion_stock.php',
	                    'reporte_v.php', 'reporte_ventas.php', 'reportes_f.php', 'reportes_vd.php'],
	'facturacion'   => ['comprobantes.php', 'factura.php', 'boleta.php', 'venta_comprobantes.php', 'anular_comprobante.php',
	                    'anular_factura.php', 'generar_nota_credito_b.php', 'generar_nota_credito_f.php', 'configuracion_facturacion.php'],
	'preventas'     => ['preventas.php', 'procesar_preventa.php', 'pedido_ia.php'],
	'empleados'     => ['ver_empleados.php', 'agregar_empleado.php', 'editar_empleado.php', 'asistencia.php', 'ver_asistencias.php', 'descansos.php'],
	'planillas'     => ['planillas.php', 'ver_planilla.php', 'ver_planilla_detalle.php', 'configuracion_planillas.php', 'boleta_pago.php', 'movimientos.php'],
	'tablet'        => ['tablet_config_admin.php'],
	'auditoria'     => ['logs_auditoria.php'],
	'configuracion' => ['modulos_config.php', 'modulos_api.php', 'actualizar_bd.php'],
];
