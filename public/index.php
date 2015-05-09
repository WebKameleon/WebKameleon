<?php


define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));

function mydie($txt,$h1='Info',$print_r=true)
{
    @header('Content-type: text/html; charset=utf-8');
    
    if ($h1) echo "<h1>$h1</h1>";
    die('<pre>' . ($print_r ? print_r($txt, 1) : var_export($txt,1)));
}

function myshutdown()
{
    @Bootstrap::$main->closeConn();
}

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/classes'),
    realpath(APPLICATION_PATH . '/controllers'),
    realpath(APPLICATION_PATH . '/models'),
    get_include_path(),
)));

$config = parse_ini_file(APPLICATION_PATH . '/configs/application.ini', true);
$config = $config['production'];
if (file_exists(APPLICATION_PATH . '/configs/local.ini')) {
    $localConfig = parse_ini_file(APPLICATION_PATH . '/configs/local.ini');
    $config = array_merge($config, $localConfig);
}

$dsn = $config['db.adapter'] . '://'
     . $config['db.username'] . ':'
     . $config['db.password'] . '@'
     . $config['db.host'] . '/'
     . $config['db.dbname'];

ini_set('display_errors', $config['app.display_errors']);

require_once 'Doctrine/Doctrine.php';
spl_autoload_register(array(
    'Doctrine', 'autoload'
));

spl_autoload_register(function ($name) {
    @include_once str_replace('_', '/', $name) . '.php';
});


if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) ) )
{
    $_POST = array_map( 'stripslashes', $_POST );
    $_GET = array_map( 'stripslashes', $_GET );
    $_COOKIE = array_map( 'stripslashes', $_COOKIE );
    
    ini_set('magic_quotes_gpc', 0);
}



session_start();


try {
    //$conn = Doctrine_Manager::connection($dsn);
    $conn=$dsn;
    register_shutdown_function('myshutdown');
    $bootstrap = new Bootstrap($conn, $config);
    $result=$bootstrap->run();
    session_write_close();
    echo $result;
    echo $bootstrap->debug();
    $bootstrap->closeConn();
} catch (Exception $e) {
    die($config['app.display_errors'] ? $e->getMessage() : '');
}