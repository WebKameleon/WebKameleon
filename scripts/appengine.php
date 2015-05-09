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


function usage($me,$conn)
{
    $conn->close();
    die('php '.$me.' [-v] [-s path_to_appengine_sdk] [-a] path_to_published_application'."\n");
}

die("This function is depreceated ... ftp took care of this\n");

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
    $appengine=$bootstrap->getConfig('appengine');

    
    $appengine['app'] = ''; 
    
    $next='';
    $verbose=false;
    for($i=1; $i<count($argv);$i++)
    {      
        if ($argv[$i][0]=='-')
        {
            switch (strtolower(substr($argv[$i],1,1)))
            {
                case 'a':
                    $next='app';
                    break;
                case 's':
                    $next='sdk';
                    break;
                case 'v':
                    $next='';
                    $verbose=true;
                    break;

            }
            continue;
        }
        
        $appengine[$next?:'app'] = $argv[$i];
        $next='';
    }
    
    if (!$appengine['app'] || !$appengine['sdk'] ) usage($argv[0],$conn);

    $app_yaml=realpath($appengine['app'].'/app.yaml');
    
    if (!$app_yaml)
    {
        $conn->close();
        die($appengine['app'].'/app.yaml does not exist!'."\n");
    }
    
    $author_path=realpath($appengine['app'].'/.author');

    if (!$author_path)
    {
        $conn->close();
        die($appengine['app'].'/.author does not exist!'."\n");
    }
    
    $author_time=filemtime($author_path);
    
    $done_time = file_exists($appengine['app'].'/.done') ? filemtime($appengine['app'].'/.done') : 0;
    
    
    if ($author_time > $done_time)
    {
    
        $author=explode(',',trim(file_get_contents($author_path)));
        $cc='';
        if (count($author)>1) $cc=$author[1];
        
        $author=$author[0];
    
        $user=new userModel($author);
        
        if ($user->username != $author) die();
        
        $client=Google::getUserClient($author,false,'appengine');
        $access_token=json_decode($client->getAccessToken())->access_token;    
        
        
        $tmp = sys_get_temp_dir ().'/'.md5(time().rand(1000,9999)).'.tmp';
        $cmd=$appengine['sdk'].'/appcfg.py -e '.$user->email.' --oauth2 --oauth2_access_token='.$access_token.' update '.$appengine['app'].' >'.$tmp.' 2>&1';
        if ($verbose) echo "$cmd\n";
        system($cmd);
        $result=file_get_contents($tmp);
        unlink($tmp);
        
        
        $email=$user->email;
        if ($cc) $email.=','.$cc;
        $wynik = Gmail::send($email,$result,'appengine update result');
        
        if ($verbose) echo $result;
        
        file_put_contents($appengine['app'].'/.done',$wynik);
        
    }
    
    $conn->close();
    
    
    
    
} catch (Exception $e) {
    die($e->getMessage());
}

