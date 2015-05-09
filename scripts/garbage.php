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

    $conn = Doctrine_Manager::connection($dsn);

    $bootstrap = new Bootstrap($conn, $config);
    $ftp=new ftpController();
   
   
    $sql= isset($argv[1]) && $argv[1]+0>0 ? "SELECT * FROM ftpgarbage WHERE id=".($argv[1]+0) : "SELECT * FROM ftpgarbage WHERE nd_complete=0 ORDER BY id";
   
    if (!isset($argv[1])) $argv[1]=0;
   
    $sql = $conn->modifyLimitQuery($sql,1);
    
    $garbage=$conn->fetchRow($sql);
    
    if (is_array($garbage) && count($garbage)>0)
    {
        $res = $ftp->remove($garbage['server'],$garbage['username'],$garbage['pass'],$garbage['passive'],$garbage['dir'],$argv[1]);
        
        $now=time();
        if (!$res) $now*=-1;
        $sql="UPDATE ftpgarbage SET nd_complete=$now WHERE id=".$garbage['id'];
        $conn->exec($sql);
    }
    $conn->close();
    
} catch (Exception $e) {
    die($e->getMessage());
}

