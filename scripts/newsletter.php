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
    die('php '.$me.' [-o] observer [-l lang_list] [-le lang_exclude_list] [-e email_substring] [-r] [-s] [-older x]'."\n");
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


    $root=isset($config['global.root'])?$config['global.root']:'/';
    $_SERVER['HTTP_HOST']=$config['global.http_host'];


    $mail = array('observer'=>'','lang'=>'','email'=>'','resend'=>false,'show_only'=>false,'older'=>0,'lang_exclude'=>'');
    $next='';
    for($i=1; $i<count($argv);$i++)
    {      
        if ($argv[$i][0]=='-')
        {
            switch (strtolower(substr($argv[$i],1)))
            {
                case 'e':
                    $next='email';
                    break;
                case 'l':
                    $next='lang';
                case 'le':
                    $next='lang_exclude';                    
                    break;
                case 'o':
                    $next='observer';
                    break;
                case 'older':
                    $next='older';
                    break;
                case 'r':
                    $mail['resend'] = true;
                    break;
                case 's':
                    $mail['show_only'] = true;
                    break;                
            }
            continue;
        }
        
        $mail[$next?:'observer'] = $argv[$i];
        $next='';
    }
    
    if (!$mail['observer']) usage($argv[0]);

    $conn = Doctrine_Manager::connection($dsn);
   
    $bootstrap = new Bootstrap($conn, $config);
    $bootstrap->setGlobals();
    
    $sent=new observersentModel();
    $model=new userModel();
    
    $sql="SELECT * FROM passwd WHERE email<>''";
    if ($mail['email']) $sql.=" AND email LIKE '%".$mail['email']."%'";

    if ($mail['lang']) $sql.=" AND ulang IN ('".implode("','",explode(',',$mail['lang']))."')";
    if ($mail['lang_exclude']) $sql.=" AND ulang NOT IN ('".implode("','",explode(',',$mail['lang_exclude']))."')";
    
    $sql.=" ORDER BY nlicense_agreement_date";
    $users=$conn->fetchAll($sql);
    
    $count=0;

    foreach ($users AS $user)
    {
        $user['him']=$user['email'];
        
        if ($user['nospam']) continue;
        if (!$user['username']) continue;
        
        if ($mail['older'])
        {
            if (!$user['nlicense_agreement_date']) continue;
            if ($user['nlicense_agreement_date'] > time()-$mail['older']*24*3600) continue;
        }
        
        $data = date('d-m-Y',$user['nlicense_agreement_date']);
        
        
        if (!$mail['resend'] && $sent->getSent($user['email'],$mail['observer'])) continue;
    
    
        $user['root']=$root;
        $user['time']=$model->login_time($user['username']);
    
        $count++;
        if (!$mail['show_only'])
        {
            echo "$count. Sending to ".$user['email'].' ...';
            flush();
            Observer::observe($mail['observer'],$user,$user['ulang']);
            echo " OK\n";
        }
        else
        {
            echo "$count. Would send <".$mail['observer']."> to ".$user['email']." [since $data, ".$user['time']." sek.]\n";
            
        }
    }
    
    echo "Total count: $count\n";
    
    

    $conn->close();
    
} catch (Exception $e) {
    die($e->getMessage());
}

