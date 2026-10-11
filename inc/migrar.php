<?php
// Migraciones: aplica una sola vez, en orden, cada sql/NN_nombre.sql.
// El estado se guarda en la tabla schema_migraciones. Los .sql sueltos (add_*, backfill_*) son del
// sistema original y ya están incluidos en 00_schema_insumos.sql; no se ejecutan.

function migrar_pendientes(mysqli $db, $dir) {
	$db->query("CREATE TABLE IF NOT EXISTS `schema_migraciones` (
		`archivo` VARCHAR(100) NOT NULL, `aplicada` DATETIME NOT NULL, PRIMARY KEY (`archivo`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
	$hechas = [];
	$r = $db->query("SELECT archivo FROM schema_migraciones");
	while ($r && ($f = $r->fetch_row())) { $hechas[$f[0]] = true; }
	$archivos = array_filter(scandir($dir), function ($f) { return preg_match('/^\d{2}_.+\.sql$/', $f); });
	sort($archivos);
	return array_values(array_filter($archivos, function ($f) use ($hechas) { return empty($hechas[$f]); }));
}

// Devuelve la lista de archivos aplicados; lanza RuntimeException con el error de MySQL si falla.
function migrar_aplicar(mysqli $db, $dir) {
	$aplicados = [];
	foreach (migrar_pendientes($db, $dir) as $archivo) {
		$sql = file_get_contents($dir . '/' . $archivo);
		if (!$db->multi_query($sql)) {
			throw new RuntimeException("$archivo: " . $db->error);
		}
		do {
			if ($res = $db->store_result()) $res->free();
			if ($db->errno) throw new RuntimeException("$archivo: " . $db->error);
		} while ($db->more_results() && $db->next_result());
		if ($db->errno) throw new RuntimeException("$archivo: " . $db->error);
		$st = $db->prepare("INSERT INTO schema_migraciones (archivo, aplicada) VALUES (?, NOW())");
		$st->bind_param('s', $archivo);
		$st->execute();
		$aplicados[] = $archivo;
	}
	return $aplicados;
}
