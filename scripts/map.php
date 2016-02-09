<?php

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));
define('MEDIA_PATH', realpath(dirname(__FILE__) . '/../media'));

function mydie($txt,$title='Info')
{
    echo "$title:\n";
    die (print_r($txt,1)."\n");
}

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/classes'),
    realpath(APPLICATION_PATH . '/controllers'),
    realpath(APPLICATION_PATH . '/models'),
    get_include_path(),
)));


try {
    $config = include __DIR__ . '/db_config.php';
    $dsn = $config['db.adapter'] . '://' .
        $config['db.username'] . ':' .
        $config['db.password'] . '@' .
        $config['db.host'] . '/' .
        $config['db.dbname'];

    require_once __DIR__ . '/../library/Doctrine/Doctrine.php';
    spl_autoload_register(array('Doctrine', 'autoload'));


    spl_autoload_register(function ($name) {
        @include_once str_replace('_', '/', $name) . '.php';
    });


    $conn = Doctrine_Manager::connection($dsn);

    $bootstrap = new Bootstrap($conn, $config);
    
    
   	$servers=new serverModel(); 
	$all=$servers->select(array(),'id');
	foreach ($all AS $i=>$s)
	{
		if ($s['id']<0 || !$s['map_url']) continue;

		echo $s['nazwa'].':'.strtolower($s['map_url'])."\n";
	}
    
    
    $conn->close();
    
} catch (Exception $e) {
    die($e->getMessage());
}

