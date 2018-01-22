<?php

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));
define('MEDIA_PATH', realpath(dirname(__FILE__) . '/../media'));


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


    if (!isset($argv[3])) {
        die($argv[0]." server_id old_category_name new_category_name\n");
    }

    $conn = Doctrine_Manager::connection($dsn);
    $now=time();
    
    $bootstrap = new Bootstrap($conn, $config);
    $ajax=new ajaxController();

    
    $sql="UPDATE webcat SET category='".$argv[3]."' WHERE category='".$argv[2]."' AND server=".$argv[1];
    $conn->execute($sql);
    
   
    $sql="SELECT sid,widget_data FROM webtd WHERE widget='articlelist' AND widget_data<>'' AND server=".$argv[1];
    $tds=$conn->fetchAll($sql);
    
    
    foreach($tds AS $td)
    {
        $data=unserialize(base64_decode($td['widget_data']));
        $data['category'] = str_replace($argv[2],$argv[3],$data['category']);
        $sql="UPDATE webtd SET widget_data='".base64_encode(serialize($data))."' WHERE sid=".$td['sid'];
        $conn->execute($sql);
    }
    
    

    
    
    $conn->close();
    
} catch (Exception $e) {
    die($e->getMessage());
}

