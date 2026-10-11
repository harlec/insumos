<?php

require_once __DIR__ . '/../config.php';

// Credenciales desde config/config.php (generado por instalar.php).
class Sdba_config
{
    public static $dbname = '';
    public static $dbuser = '';
    public static $dbpass = '';
    public static $dbhost = 'localhost'; // 'host' o 'host:puerto'
    public static $dbencoding = 'utf8'; // Your database encoding, default is 'utf8'. Do not change, if not sure.

    public static $autoreset = true; // Auto-resets conditions when you try to set new (after some db action, true is recommended);
}

Sdba_config::$dbname = cfg('db.name', '');
Sdba_config::$dbuser = cfg('db.user', '');
Sdba_config::$dbpass = cfg('db.pass', '');
$sdba_port = (int)cfg('db.port', 3306);
Sdba_config::$dbhost = cfg('db.host', 'localhost') . ($sdba_port && $sdba_port !== 3306 ? ':' . $sdba_port : '');
unset($sdba_port);
