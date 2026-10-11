<?php
// Copia de ejemplo. El instalador (instalar.php) genera config/config.php con tus valores.
// config/config.php NO se versiona.
return [
	'db' => [
		'host' => 'localhost',
		'port' => 3306,
		'name' => 'admin_insumos',
		'user' => '',
		'pass' => '',
	],
	// Secreto para las claves de usuario (hash_hmac sha256). No lo cambies después de crear usuarios.
	'pepper' => '',
];
