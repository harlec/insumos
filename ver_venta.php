<?php
include('inc/control.php');
include('inc/sdba/sdba.php'); // include main file

$id = $_GET['id'];
$ventas = Sdba::table('detalle_ventas'); // creating table object
$ventas->where('venta', $id);
$ventas->left_join('producto','productos','id_producto');
$ventas_list = $ventas->get(); 

//print_r($ventas_list);
$ocultar = '';
$ventas1 = Sdba::table('ventas');
$ventas1->where('id_venta', $id);
$ventas_list1 = $ventas1->get_one();
if ($ventas_list1['estado']=='1') {
	$ocultar = 'ocultar';
} 


$datos = '';
$i = 1;
$tot = 0;
foreach ($ventas_list as $value) {

	$tot = $tot + $value['total'];

	$datos .='<tr> 
    			<th scope="row">'.$i.'</th> 
    			<td>'.$value['nom_prod'].'</td> 
    			<td>'.$value['cantidad'].'</td>
    			<td>'.$value['precio'].'</td> 
    			<td>'.$value['total'].'</td>
    		  </tr>';
    $i++;
}

// Resultado de esta venta (solo para quien ve reportes): ventas − costo real − entrega (gasto de la venta).
$resultado = null;
if (puede('reportes', 'ver')) {
	$cx = db_connect();
	$idv = (int)$id;
	$r = @$cx->query("SELECT COALESCE(SUM((dv.cantidad + 0) * COALESCE(dv.costo_unit, NULLIF(p.costo_real, 0), NULLIF(p.precio_compra + 0, 0), 0)), 0) AS costo
		FROM detalle_ventas dv LEFT JOIN productos p ON p.id_producto = dv.producto WHERE dv.venta = $idv");
	$costo_venta = $r ? (float)$r->fetch_assoc()['costo'] : 0;
	$r = @$cx->query("SELECT COALESCE(SUM(monto), 0) AS m FROM gastos WHERE id_venta = $idv AND estado = '1'");
	$entrega_venta = $r ? (float)$r->fetch_assoc()['m'] : 0;
	$cx->close();
	$resultado = ['costo' => round($costo_venta, 2), 'entrega' => round($entrega_venta, 2), 'utilidad' => round($tot - $costo_venta - $entrega_venta, 2)];
}

?>


<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Sistema - Menu Principal</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/custom.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/10.5.0/sweetalert2.min.css" integrity="sha512-YpZXdiMhuP3woCdvg0ou2UPj6l4KQUuf3gbMXTNMgtqTakMInX7h+64CTh+UIvYdA7ctBU2BAA/h4eEhoMEmsg==" crossorigin="anonymous" />
    <?php esc_head(); ?>
</head>

<body class="mobile dashboard esc esc-legacy">
	<div class="">
		<?php menu_v2('4'); ?>
		<nav class="esc-subnav">
	      <div class="submenu">
	      	<ul class="subtop-tabs">
	      		<li >
	      			<a href="venta.php">Registrar venta</a>
	      		</li>
	      		<li >
	      			<a href="ventas.php">Listar ventas</a>
	      		</li>
<?php if (puede('facturacion', 'ver')): ?>
	      		<li>
	      			<a href="notas_venta.php">Facturar</a>
	      		</li>
<?php endif; ?>
<?php if (puede('facturacion', 'ver')): ?>
	      		<li>
	      			<a href="comprobantes.php">Comprobantes</a>
	      		</li>
<?php endif; ?>
	      	</ul>
	      </div>
	    </nav>
		<div class="kbg">
			<div class="cuerpofull">
				<div class="titulo">
					<h3>Venta <?php echo $id; ?></h3>
				</div>
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">
							<div class="kdashboard">
								<div class="row">
									<div class="col-md-6">
										<div class="panel panel-default pa">
											<div class="panel-body">
												<p><strong>Venta id: <?php echo $id; ?></strong></p>
											    <table id="datos" class="table table-hover"> 
											    	<thead> 
											    		<tr> 
											    			<th>#</th> 
											    			<th>Nombre</th> 
											    			<th>Cantidad</th> 
											    			<th>Precio</th>
											    			<th>Total</th>  
											    		</tr> 
											    	</thead> 
											    	<tbody> 
											    		<?php echo $datos; ?>

											    	</tbody> 
											    </table>
											    <p class="text-right"><strong>Total: S/ <?php echo $tot; ?></strong></p>
											    <?php if ($resultado): ?>
											    <table class="table table-condensed" style="max-width:360px;margin-left:auto">
											    	<tr><td class="text-muted">Costo de lo vendido</td><td class="text-right">S/ <?php echo number_format($resultado['costo'], 2); ?></td></tr>
											    	<tr><td class="text-muted">Costo de entrega <small>(lo asume la empresa)</small></td><td class="text-right">S/ <?php echo number_format($resultado['entrega'], 2); ?></td></tr>
											    	<tr><td><strong>Utilidad de esta venta</strong></td><td class="text-right"><strong style="color:<?php echo $resultado['utilidad'] >= 0 ? '#2d6b3a' : '#c2361a'; ?>">S/ <?php echo number_format($resultado['utilidad'], 2); ?></strong></td></tr>
											    </table>
											    <?php endif; ?>
											    <center>
											    	
												    <a target="_blank" class="btn btn-primary btn-lg <?php echo $ocultar;?>" href="recibo.php?id=<?php echo $id; ?>">Recibo</a>
												    <?php if (puede('facturacion', 'crear')): ?>
												    <button type="button" class="btn btn-success btn-lg <?php echo $ocultar;?>" id="btn-ver-factura" data-href="factura.php?ids=<?php echo $id; ?>"><i class="fas fa-file-invoice-dollar"></i> Factura</button>
												    <button type="button" class="btn btn-danger btn-lg <?php echo $ocultar;?>" id="btn-ver-boleta" data-href="boleta.php?ids=<?php echo $id; ?>"><i class="fab fa-bitcoin"></i> Boleta</button>
												    <?php endif; ?>
												</center>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	 	<!-- Tab panes -->
		

	  
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="assets/js/sweetalert2.all.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/10.5.0/sweetalert2.min.js" integrity="sha512-V9JHp52ZkrbVVjJqNz/XXYMUOyUfzaGKEGrcD2Ual7n39+UR1yJK0numAHZqkhhGTAH/Klj0KUe4btAZXccw9w==" crossorigin="anonymous"></script>
	<script >
	// A $( document ).ready() block.
	$(document ).ready(function() {
	    console.log( "ready!" );

	    $('#btn-ver-factura, #btn-ver-boleta').on('click', function () {
	    	var url = $(this).data('href');
	    	Swal.fire({
	    	  title: 'Emitir comprobante',
	    	  text: 'Está a punto de emitir un comprobante de pago, esto tiene implicaciones legales.',
	    	  icon: 'warning',
	    	  showCancelButton: true,
	    	  confirmButtonColor: '#3085d6',
	    	  cancelButtonColor: '#d33',
	    	  confirmButtonText: 'Sí, continuar',
	    	  cancelButtonText: 'Cancelar'
	    	}).then((result) => {
	    	  if (result.isConfirmed) {
	    	    window.location.href = url;
	    	  }
	    	});
	    });

	});
		
	</script>
</body>
</html>