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


function usage($me)
{
    die('php '.$me.' to [-f from_mail] [-s subject] [-o observer_event] [-r reply_to_mail] [-html]'."\n");
}


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




    $mail = array('to'=>'','from'=> '','subject'=>'','observer'=>'','html'=>false,'replyto'=>false);
    $next='';
    for($i=1; $i<count($argv);$i++)
    {      
        if ($argv[$i][0]=='-')
        {
            switch (strtolower(substr($argv[$i],1,1)))
            {
                case 'f':
                    $next='from';
                    break;
                case 's':
                    $next='subject';
                    break;
                case 'r':
                    $next='replyto';
                    break;
                case 'o':
                    $next='observer';
                    break;
                case 'h':
                    $mail['html'] = true;
                    break;
            }
            continue;
        }
        
        $mail[$next?:'to'] = $argv[$i];
        $next='';
    }
    
    if (!$mail['to']) usage($argv[0]);

    $conn = Doctrine_Manager::connection($dsn);
   
    
    $bootstrap = new Bootstrap($conn, $config);
    $bootstrap->setGlobals();
    
    
    $wynik = Gmail::send($mail['to'],file_get_contents('php://stdin'),$mail['subject'],$mail['from'],$mail['html'],$mail['replyto']);
    
    if (strlen($wynik)>3) echo "$wynik\n";
    $conn->close();
    
} catch (Exception $e) {
    die($e->getMessage());
}

