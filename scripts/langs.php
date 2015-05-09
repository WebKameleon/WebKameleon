<?php

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));

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
    $bootstrap->setGlobals();
    
    $email=$config['global.admin'];
    
    $user=new userModel();
    
    $email=current(explode(',',$email));
    
    $bootstrap->session('user',$user->getByEmail($email));
    
    $ws=Spreadsheet::listWorksheets($config['oauth2.langs']);
    

    $langs=array();
    foreach($ws AS $k=>$w)
    {
	if (!strstr($w['title'],'lang') ) continue;
	
	$data=Spreadsheet::getWorksheet($config['oauth2.langs'],$k);

	$header=$data[0];
	
	
	$label=0;
	foreach($header AS $i=>$v) if (strtolower($v)=='label') $label=$i;
	
	for ($i=1;$i<count($data);$i++)
	{
	    foreach($data[$i] AS $j=>$word)
	    {
		if ($j==$label) continue;
		$langs[$header[$j]][$data[$i][$label]]=$data[$i][$j];
	    }
	    
	}

    }
    
    foreach($langs AS $lang=>$words)
    {
	file_put_contents(__DIR__.'/../application/lang/'.$lang.'.ser',serialize($words));
    }

    
    
    $conn->close();

    
} catch (Exception $e) {
    die($e->getMessage());
}

