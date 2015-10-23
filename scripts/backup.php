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
    
    
    $user=new userModel();
    $admin=explode(',',$config['global.admin']);
    
    $u=$user->getByEmail($admin[0]);
    $u['admin']=true;
    
    $bootstrap->session('user',$u);
    $bootstrap->config2array();
    
    $_REQUEST['to']='drive';
    if (isset($argv[1])) {
        
        $folder_mime = "application/vnd.google-apps.folder";
        $folder_name = date('Y-m-d');
        
        $service = Google::getDriveService();
        $folder = new Google_Service_Drive_DriveFile();
        
        $folder->setTitle($folder_name);
        $folder->setMimeType($folder_mime);
       
        $parent = new Google_Service_Drive_ParentReference();
        $parent->setId($argv[1]);
        $folder->setParents(array($parent));       
       
       
        $fld=$service->files->insert($folder);
        
        if (isset($fld['id'])) $_REQUEST['folderId']=$fld['id'];

    }
    
    $admin=new adminController();
    $wizard=new wizardController();

   
    $sql="SELECT * FROM servers WHERE id>0 AND (nd_expire=0 OR nd_expire IS NULL OR nd_expire > ".time().")";
    $servers=$conn->fetchAll($sql);
    
    
    foreach($servers AS $s)
    {
        $s['owner']=1;
        $s['server']=$s['id'];
        Bootstrap::$main->session('server',$s);

        $admin->enter($s['id']);
        $wizard->export(true);
        echo $s['nazwa']."\n";
        //break;
    }
    
    
    
    
    $conn->close();
    
} catch (Exception $e) {
    die($e->getMessage());
}

