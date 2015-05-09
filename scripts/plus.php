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
    $bootstrap->setGlobals();
    
    $email=$config['global.gplus'];
    
    
    if ($email)
    {
    
        $user=new userModel();   
        $email=current(explode(',',$email));
        
    
      
        
        $bootstrap->session('user',$user->getByEmail($email));    
    
    
        
        $people=Gplus::people_list();
        
        //$piotr=Gplus::circles_addPeople('38a05d9a0d2a5d17','editor@webkameleon.com');
        
        print_r($people);
        
        //$client=Google::getUserClient(null,false,'plus');
        //$service = Google::getPlusService($client);
     
     
        //$cirlces=$service->circles->list('me');
     
     
     
        //$sql="SELECT * FROM passwd WHERE";
        //$people=$conn->fetchAll($sql);
        
        

    }
    
 
    
    
    $conn->close();
    
} catch (Exception $e) {
    die($e->getMessage());
}

