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


    $days=$config['trash.empty_after_days'];
    $now=time();
    $epoch=$now-24*3600*$days;
    $epoch2=$now-24*3600*2;
    $epoch7=$now-24*3600*7;
    
    $bootstrap = new Bootstrap($conn, $config);
    $ajax=new ajaxController();

    
    $sql="DELETE FROM webpage WHERE trash>0 AND trash<".$epoch;
    $conn->execute($sql);
    $sql="DELETE FROM weblink WHERE trash>0 AND trash<".$epoch;
    $conn->execute($sql);
    $sql="DELETE FROM webtd WHERE trash>0 AND trash<".$epoch;
    $conn->execute($sql);

    $sql="DELETE FROM webtd WHERE trash<0 AND -1*trash NOT IN (SELECT sid FROM webpage WHERE webpage.sid=-1*webtd.trash)";
    $conn->execute($sql);

    
    $sql="UPDATE webtd SET uniqueid='' WHERE uniqueid IS NOT NULL AND uniqueid<>'' AND (uniqueid,sid) IN (SELECT uniqueid,max(sid) FROM webtd GROUP BY uniqueid HAVING count(*)>1) ";
    $conn->execute($sql);
    
    
    $sql="INSERT INTO ftp_arch SELECT id,server,username,t_begin,t_end,ver,pid,lang FROM ftp WHERE t_start<".$epoch;
    $conn->execute($sql);
    
    $sql="DELETE FROM ftp WHERE t_start<".$epoch;
    $conn->execute($sql);

    $sql="INSERT INTO login_arch SELECT id,tin,tout,server,username,ip,groupid FROM login WHERE tout<".$epoch2;
    $conn->execute($sql);
        
    
    $sql="DELETE FROM login WHERE tout<".$epoch2;
    $conn->execute($sql);
    
    $sql="UPDATE login_arch SET login_time=tout-tin WHERE login_time IS NULL";
    $conn->execute($sql);    
    
    
    $sql="DELETE FROM authstate WHERE nd_create<".$epoch7;
    $conn->execute($sql);
    
    $sql="DELETE FROM authstate WHERE nd_complete>=nd_create AND nd_create<".$epoch2;
    $conn->execute($sql);
    
    $sql="UPDATE servers SET id=-1*id, nd_trash=$now WHERE nd_expire>0 AND id>0 AND nd_expire<".$epoch;
    $conn->execute($sql);

   
    $sql="SELECT * FROM servers WHERE id<0 AND nd_trash>0 AND nd_trash<".$epoch;
    $servers=$conn->fetchAll($sql);
    
    foreach($servers AS $s)
    {
        $s['owner']=1;
        $s['server']=$s['id'];
        Bootstrap::$main->session('trash',array($s));

        echo "Removing ".$s['nazwa']."\n";
        $r=$ajax->wizard_remove($s['id']);
    }
    
    
    $sql="SELECT * FROM servers LEFT JOIN passwd ON creator=username WHERE nd_expire>".$now." AND nd_expire<".($now+31*24*3600);
    $servers=$conn->fetchAll($sql);    
    $bootstrap->reminder($servers);
    
    
    $sql="SELECT * FROM passwd WHERE nlicense_agreement_date>".$epoch." AND nlicense_agreement_date<".($now-24*3600);
    $people=$conn->fetchAll($sql);
    
    $bootstrap->people_reminder($people); 
    
    
    $conn->close();
    
} catch (Exception $e) {
    die($e->getMessage());
}

