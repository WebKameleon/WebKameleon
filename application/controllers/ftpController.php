<?php

class ftpController extends Controller
{
    protected $error;
    protected $conn=null;
    protected $ftp;
    private $statcache;
    private $appengine_path;
    private $appengine_static_host=null;
    private $appengine_need_transfer=false;
    private $remote_ftp;
    private $gcs_bucket;
    private $gcs_service;
    private $gcs_data;
    
    
    
    public function init()
    {
        if (!function_exists('ftp_connect')) Bootstrap::$main->error(ERROR_ERROR,'FTP support not enabled');
        
        
        $server=Bootstrap::$main->session('server');
        
        
        if ($server['map_url'])
        {
            $config=Bootstrap::$main->getConfig();
            
            $cname=gethostbyname($config['ftp']['map']['cname']);
            $map=@gethostbyname($server['map_url']);
            
            if ($cname != $map)
            {
                Bootstrap::$main->error(ERROR_WARNING,'%s is not yet mapped to %s. Check your DNS service provider.',array($server['map_url'],$config['ftp']['map']['cname']),'ftp/map');
            }
        }
        
        
        parent::init();
    }


    public function sftp()
    {
        $session_server = Bootstrap::$main->session('server');
        
        $ret=array();
        
        if (isset($_POST['sftp']))
        {

            $server=new serverModel($session_server['id']);
            
            
            if (!$_POST['sftp'] && $server->media_pass)
            {
                $session_server['media_pass']='';
                $server->media_pass = null;
                $server->save();
            }
            
            if ($_POST['sftp'] && (!$server->media_pass || isset($_POST['regenerate'])))
            {
                $pass='';
                for ($i=0;$i<6;$i++) $pass.=chr(97+rand(0,25));
                
                $pass[rand(0,5)]='#';
                $pass[rand(0,5)]='@';
                
                $ret['password'] = $pass;
                
                $epass=crypt($pass);
                
                $session_server['media_pass']=$epass;
                $server->media_pass = $epass;
                $server->save();

            }
            
            Bootstrap::$main->session('server',$session_server);
        }
        
        $ret['server']=$session_server;    
    
        return $ret;        
        
    }
    
    public function map()
    {
        $session_server = Bootstrap::$main->session('server');
        
        if (isset($_POST['map_url']))
        {
            $map_url=strtolower(trim($_POST['map_url']));
            if (!preg_match('/^[a-z0-9_\-\.]+\.[a-z0-9_\-\.]+$/',$map_url)) {
                Bootstrap::$main->error(ERROR_ERROR,'Invalid character in domain name');
            } else {
                
                $server=new serverModel($session_server['id']);
                
                $the_same=$server->find_by_map_url($map_url);
                
                if (count($the_same)) {
                    Bootstrap::$main->error(ERROR_ERROR,'Address %s exists',array($map_url));
                } else {
                    $config=Bootstrap::$main->getConfig('ftp');
                    $check=$config['map']['check'] . urlencode($map_url);
                    
                    $ok=0+@file_get_contents($check);
                    
                    if (!$ok) {
                        Bootstrap::$main->error(ERROR_ERROR,'Address %s exists',array($map_url));
                    } else {
                        $server->setDefaultValues();
                        $server->map_url=$map_url;
                        $server->http_url='http://'.$map_url;
                        $server->save();
                        foreach($server->data() AS $k=>$v) $session_server[$k]=$v;
                        Bootstrap::$main->session('server',$session_server);
                        $this->redirect('ftp/get');
                    }
                }   
            }
            
        }
        
        if (isset($_POST['unmap_url'])) {
            $server=new serverModel($session_server['id']);
            $server->setDefaultValues();
            $server->map_url=null;
            $server->save();
            
            foreach($server->data() AS $k=>$v) $session_server[$k]=$v;
            Bootstrap::$main->session('server',$session_server);
            $this->redirect('ftp/get');            
        }
        
        
        $ret = array('map_url'=>isset($_POST['map_url'])?$map_url:'www.', 'server'=>$session_server);    
    
        return $ret;
    }
    
    public function get()
    {
     
        $ret = array('session'=>Bootstrap::$main->session());
        
        if (!$ret['session']['server']['ftp_user'] || !$ret['session']['server']['ftp_pass']){
            $this->redirect('ftp/setup');
            return;
        }
    
        
        
        $ftp=new ftpModel();
        $ftps=$ftp->getLast($ret['session']['server']['id'],$this->id);
        $user=new userModel();
        
        $users=array();
        $lp=0;
        foreach($ftps AS $i=>$f) {
            if (!isset($users[$f['username']])) {
                $users[$f['username']]=$user->get($f['username']);
            }
            $ftps[$i]['user']=$users[$f['username']];
            $ftps[$i]['lp']=++$lp;
            
            if (isset($f['log'])) foreach ($f['log'] AS $j=>$log) {
                $ftps[$i]['log'][$j]['ok']=true;
                if ($log['wynik']=='FAIL' || $log['wynik']==Tools::translate('FAIL'))
                    $ftps[$i]['log'][$j]['ok']=false;
            }
        }
        
        if ($ret['session']['referpage'])
        {
            $webpage=new webpageModel();
            $ret['page']=$webpage->getOne($ret['session']['referpage'],true);
            
            //mydie($ret['page']);//file_name
        }
        
        //echo '<pre>'; print_r($ftps); die();
        
        $ret['ftp']=$ftps;
        return $ret;
    }

    
    public function stop()
    {
        $ftp=new ftpModel($this->id);
        $server=Bootstrap::$main->session('server');
        
        
        $this->redirect('ftp');
        
        if ($ftp->server != $server['id']) {
            Bootstrap::$main->error(ERROR_WARNING,'Insufficient rights');
            return;
        }
        
        
        if ($ftp->username != $server['username'] && !$server['owner']) {
            Bootstrap::$main->error(ERROR_WARNING,'Insufficient rights');
            return;
        }
        
        
        $this->end_process($ftp,true);
        
    }
    
    public function ftp_start($limit,$all='',$deteach=true)
    {
        $session=Bootstrap::$main->session();
        $config=Bootstrap::$main->getConfig();
         
        $vers=array($session['ver']);
        $langs=array($session['lang']);
        
        if (isset($config['ftp']['more']['vers'])) $vers=array_unique(array_merge($vers,explode(',',$config['ftp']['more']['vers'])));
        $ids=array();
        
        //$this->_log($langs,$vers,getmypid());
        
        foreach ($vers AS $ver) {
            foreach ($langs AS $lang) {
                $ftp=null;
                $ftp=new ftpModel();
         
                $ftp->ver=$ver;
                $ftp->lang=$lang;
                $ftp->server=$session['server']['id'];
                $ftp->username=$session['user']['username'];
                
                $ftp->save();
                $ids[]=$ftp->id;
                
                Bootstrap::$main->session('ftp_id', $ftp->id);
                Bootstrap::$main->session('ftp_start', Bootstrap::$main->now);
            }
        }

        $this->process($ids,$limit,$all,$deteach);        
    }
    
    public function start()
    {
        //$this->_log('start',getmypid(),$_SERVER);
        if (Bootstrap::$main->session('ftp_start') && Bootstrap::$main->now - Bootstrap::$main->session('ftp_start')<4 )
        {
            $this->redirect('ftp/get/'.Bootstrap::$main->session('ftp_id'));
            return;
        }
        $this->ftp_start($_GET['ftplimit'],$_GET['ftpall']);
        die();
    }
    
    protected function end_process($ftp,$kill=false) {
        if (!is_object($ftp)) {
            $ftp=new ftpModel($ftp);
        }
        if ($kill) {
            $ftp->killed=1;
            if ($ftp->pid && function_exists('posix_kill')) posix_kill($ftp->pid , 9);
        }
        $ftp->t_end=time();
        $ftp->save();
        $this->log($ftp,$kill&&$ftp->pid?'Killing':'Disconnecting','OK');
        $this->close();
    }
    
    
    protected function disconnect($id)
    {
        //mydie(Bootstrap::$main->getDebug());
        //Bootstrap::$main->setDebug(1); return;
        
        if (Bootstrap::$main->getDebug()>0) return false;
        //mydie(Bootstrap::$main->getDebug());
        
        ini_set('display_errors',false);
        ini_set('max_execution_time',0);

        $redirect=Bootstrap::$main->getRoot().'ftp/get/'.$id;
        usleep(2000);

        $reload = '<html>
                    <head>
                        <title>Publication</title>
                    </head>
                    <body>
                        <script language="javascript">
                                function kameleon_ftp_reload()
                                {
                                    location.href="'.$redirect.'"
                                }
                                setTimeout(kameleon_ftp_reload,300);
                        </script>';


        $cotrzebawyswietlic='';
        if (ini_get('output_buffering') == 1)
        {
                @ob_implicit_flush();
                while (ob_get_level())
                {
                        $cotrzebawyswietlic.=ob_get_contents();
                        
                        @ob_end_clean();
                        echo '('.strlen($cotrzebawyswietlic).')';
                }
        }


        if (ini_get('output_buffering')*1 > 1)
        {
                for ($i=0; $i<3*ini_get('output_buffering')*1; $i++) 
                {
                    echo "\n";
                    @ob_end_flush();
                }
                $cotrzebawyswietlic='cos';
        }

        
        echo $reload;


        if (!strlen($cotrzebawyswietlic))
        {
                for ($i=0; $i<10; $i++) 
                {
                        echo "\n";
                        flush();
                        @ob_flush();
                }
        }
        
        @ob_end_clean();

        for ($i=0; $i<4096; $i++) 
        {
                echo "\n";
                flush();
                @ob_flush();
        }	
        session_write_close();     
        
    }
    
    protected function connect_chdir($server,$user,$pass,$passive,$dir,$ftp=null,$mkdir=true)
    {
        if (!$server || !$user || !$pass)
        {
            $this->_log("Some of ($server|$user|$pass) is missing");
            return false;
        }
        $default=Bootstrap::$main->getConfig('default');
        
        $default_server = false;
        if (isset($default['ftp_server']) && $default['ftp_server'])
        {
            $s1=explode(':',$default['ftp_server']);
            $s2=explode(':',$server);
        
            if (gethostbyname($s1[0]) == gethostbyname($s2[0]) )
            {
                $default_server = true;
                if (!preg_match('/[a-zA-Z0-9]+/',$dir))
                {
                    if ($ftp)
                    {
                        $command=Tools::translate('Destination directory missing');
                        $this->_log($command);
                        $this->log($ftp,$command,Tools::translate($this->error),false);
                        $this->end_process($ftp);
                    }                 
                    return false;
                }
            }
        }
        
        $res=$this->connect($server,$user,$pass);
        if ($ftp)
        {
            $command=Tools::translate('Connecting');
            if (!$default_server) $command.=' '.$user.'@'.$server;
            $this->log($ftp,$command,$res?'OK':Tools::translate($this->error),false);
        }        

        if (!$res) {
            $this->_log('Not connected');
            if ($ftp) $this->end_process($ftp);
            return false;
        }

        if ($passive) {
           ftp_pasv($this->conn,1);
        }
        

        if ($dir) {
            $res=$this->chdir($dir,$mkdir);
            
            if ($ftp) {
                $command=Tools::translate('Chdir').' '.$dir;
                $this->log($ftp,$command,$res?'OK':Tools::translate('FAIL'),false);
            }
            
            if (!$res) {
                $this->_log("Could not chdir/mkdir $dir");
                if ($ftp) $this->end_process($ftp);
                return false; 
            }
            
        }

        return true;
    }
    
    protected function gcs_init()
    {
        $client=Google::getUserClient($this->gcs_data['creator'],false,'gcs');
        $this->gcs_service = Google::getStorageService($client);                
    
        try {
            $this->gcs_bucket = $this->gcs_service->buckets->get($this->gcs_data['website']);   
        } catch (Exception $e) {
            $this->gcs_bucket = null;
            $this->log($this->ftp,$e->getMessage(),Tools::translate('FAIL'),false);
            $this->debug($e->getMessage());
        }        
    }
    
    
    protected function process($ftpids,$limit,$all,$deteach=true)
    {

        if ($deteach) $this->disconnect(implode(',',$ftpids));
        session_write_close();
        
        foreach ($ftpids AS $id) {
            $ftp=new ftpModel($id);
            $server=new serverModel($ftp->server);
            
            $resitemap=0;
            
            $data=$server->data();
            $server->d_xml($data);
            Bootstrap::$main->session('server',$data);
            Bootstrap::$main->setGlobals(true,true);
                        
            
            $this->debug("Start FTP process=$id");
            
            $ftp->t_begin=time();
            $ftp->pid=getmypid();
            $ftp->save();
            
            $this->remote_ftp = false;
            $this->appengine_path = null;

            
            $this->ftp=$ftp;
            
            if (!$server->appengine_id && !$server->gcs_website) {
                if ($this->connect_chdir($server->ftp_server,$server->ftp_user,$server->ftp_pass,$server->ftp_pasive,$server->ftp_dir,$ftp)) $this->remote_ftp=true;
                else continue;
            }
               
            if ($server->appengine_id) {
                $this->appengine_path = FILES_PATH . '/appengine/'. $server->nazwa ;
                if (!file_exists($this->appengine_path)) mkdir($this->appengine_path,0755,true);
                if (!file_exists($this->appengine_path) || !is_writable($this->appengine_path))
                {
                    $this->appengine_path=null;
                    $server->appengine_id=null;
                }
                
                if ($server->gcs_website)
                    $this->appengine_static_host='http://'.$server->gcs_website;
                else
                    $server->gcs_website=$server->appengine_id.'.appspot.com';
 
            }
            
            if ($server->gcs_website) {
                $lang=$ftp->lang;

                $gcs_website = GN_Smekta::smektuj($server->gcs_website,get_defined_vars());
                
                $this->gcs_data['creator']=$server->creator;
                $this->gcs_data['website']=$gcs_website;
                $this->gcs_init();
            }
            
            
            if (count($ftp->getUnfinished($ftp->server))==1 && $this->appengine_path)
            {
                $lock=$this->appengine_path.'/.appengine_lock';
                if (file_exists($lock)) unlink($lock);
            }
            
            
            if (!strlen($limit) || $limit=='img') {
                $this->debug("Start transfering images");
                $res=$this->transfer_images($all);
                $result = ($res===false) ? Tools::translate('FAIL') : $res.' '.Tools::translate('files');
                $this->log($ftp,Tools::translate('Files report').' '.$res,$result,false);

                $res=$this->transfer_template($all,$server->social_template);
                $result = ($res===false) ? Tools::translate('FAIL') : $res.' '.Tools::translate('files');
                $this->log($ftp,Tools::translate('Files report').' '.$res,$result,false);     
                
                $res=$this->transfer_media($all);
                $result = ($res===false) ? Tools::translate('FAIL') : $res.' '.Tools::translate('files');
                $this->log($ftp,Tools::translate('Files report').' '.$res,$result,false);
            
                $res=$this->transfer_uimages($all);
                $result = ($res===false) ? Tools::translate('FAIL') : $res.' '.Tools::translate('files');
                $this->log($ftp,Tools::translate('Files report').' '.$res,$result,false);
                       
            
            }
            
            if (!strlen($limit) || $limit=='inc') {
                $this->debug("Start transfering includes");
                $res=$this->transfer_includes($all);
                $result = ($res===false) ? Tools::translate('FAIL') : $res.' '.Tools::translate('files');
                $this->log($ftp,Tools::translate('Files report').' '.$res,$result,false);
            }            
            
            
            if (!strlen($limit) || $limit=='att') {
                $this->debug("Start transfering user files");
                
                $res=$this->transfer_ufiles($all);
                $result = ($res===false) ? Tools::translate('FAIL') : $res.' '.Tools::translate('files');
                $this->log($ftp,Tools::translate('Files report').' '.$res,$result,false);

                $res=$this->transfer_root($all);
                $result = ($res===false) ? Tools::translate('FAIL') : $res.' '.Tools::translate('root files');
                $this->log($ftp,Tools::translate('Files report').' '.$res,$result,false);

                
            }
            
            
            
            
            if (!strlen($limit) || strstr($limit,'+') || preg_match('/^[0-9]+$/',$limit)) {
                $this->debug("Start transfering pages <b>$limit</b>");
                $res=$this->transfer_pages($limit,$all,$ftp->server,$ftp->lang,$ftp->ver);
                $result = ($res===false) ? Tools::translate('FAIL') : $res.' '.Tools::translate('files');
                $this->log($ftp,Tools::translate('Files report').' '.$res,$result,false);
                
                $resitemap=$server->resitemap;
                
                if ($resitemap) {
                    $this->transfer_sitemap();
                    $server->resitemap=0;
                    $server->save();
                }
                
                
            }
            
            
            if ($server->appengine_id) {
                $this->remote_ftp = false;
                $md5=$this->appengine($server->appengine_id,$server->appengine_ver,$server->appengine_scripts,$server->appengine_cron,$server->appengine_md5,$server->appengine_rewrite,$server->creator,$ftp,$resitemap);
                if ($md5)
                {
                    $server->appengine_md5=$md5;
                    $server->save();
                }
            }
            
            $this->end_process($ftp);

        }
        
        if (!Bootstrap::$main->getDebug() && $deteach) die();
        else {
            @ob_end_flush();
        }
    }
    
    protected function debug($txt)
    {
        if (!Bootstrap::$main->getDebug()) return;   
        echo $txt."<br/>\n";
        flush();
    }
    
    protected function transfer($local,$remote,$all,$info='',$rename=array(),$forceappengine=false,$local_prefix='')
    {
        if (!file_exists($local)) {
            $this->debug("Local file $local does not exist!");
            return false;
        }
        
        if (!$remote) {
            $this->debug("Remote can not be empty!");
            return false;
        }
        
        
        
        if(isset($rename[basename($remote)])) $remote=str_replace(basename($remote),$rename[basename($remote)],$remote);
        
        
        
        
        $count=0;
        if (is_dir($local)) {
            $dir=scandir($local);
            foreach($dir AS $file) {
                if ($file[0]=='.') {
                    continue;
                }
                $count+=$this->transfer($local.'/'.$file,$remote.'/'.$file,$all,$info,$rename,$forceappengine,$local_prefix);
            }
        } else {
            $this->statdir(dirname($remote));
            
            $ts_local=filemtime($local);
            
            $local_compare_file=false;
            $touch_time=time();
            if (!$all) {
                $md5=md5($local_prefix.$local);
                $local_compare_file=FILES_PATH . '/ftp_ts/'.$md5[0].'/'.$md5[1].'/'.$md5[2].'/'.$md5;
                if (!file_exists(dirname($local_compare_file))) mkdir(dirname($local_compare_file),0755,true);
                $ts_remote_cache=file_exists($local_compare_file)?filemtime($local_compare_file):0;
                if ($ts_remote_cache>$ts_local) return 0;
            }
        
        
            if ($this->remote_ftp)
            {
                if (!$all) {
                    $ts_remote=0+@ftp_mdtm($this->conn,$remote);
                }

                if ($all || $ts_local>$ts_remote)
                {
                    $tmp_remote=dirname($remote).'/.'.time().rand(1000,9999).'.tmp';
                    $res=Tools::translate('FAIL');
                    $cmd=Tools::translate('Upload').': '.$remote.$info;
                    
                        
                    if (ftp_put($this->conn,$tmp_remote,$local,FTP_BINARY))
                    {
                        @ftp_chmod($this->conn,0644,$tmp_remote);
                        @ftp_delete($this->conn,$remote);
                        if (ftp_rename($this->conn,$tmp_remote,$remote))
                        {
                            $count++;
                            $res='OK';
                            if ($local_compare_file) touch($local_compare_file,$touch_time);
                        }
                    }
                    else 
                    
                    if (!$all) $cmd.=' ['.Kameleon::datetime($ts_local).' > '.Kameleon::datetime($ts_remote).']';
                    
                    $this->debug("Transfer $local &nbsp; &raquo; &nbsp; $remote$info <b>$res</b>");
                    $this->log($this->ftp,$cmd,$res,false);                    
                    
                } else {
                    if ($local_compare_file) touch($local_compare_file,$touch_time);
                }
            }
            
            
            if ($this->appengine_path && $forceappengine)
            {
                $appengine_local = $this->appengine_path.'/'.$remote; 
                if (!$all) {
                    $ts_remote=0+@filemtime($appengine_local);
                }                
                
                if ($all || $ts_local>$ts_remote)
                {
                    $this->debug("Appengine: $local -> $appengine_local");
                    if (!file_exists(dirname($appengine_local))) mkdir (dirname($appengine_local),0755,true);
                    copy($local,$appengine_local);
                    $count++;
                    $this->appengine_need_transfer=true;
                    
                }
                if ($local_compare_file) touch($local_compare_file,$touch_time);
            } elseif ($this->gcs_bucket) {

                $gs_config=Bootstrap::$main->getConfig('gs')?:array();
                
                
                $gs_gzip=isset($gs_config['gzip'])?explode(',',$gs_config['gzip']):[];
                $gso = new Google_Service_Storage_StorageObject();
                $gso->setName($remote);
                
                
                $data=file_get_contents($local);
                if (!filesize($local)) $data=' ';
                $ext=strtolower(end(explode('.',$remote)));
                switch ( $ext )
                {
                    case 'css':
                        $ct='text/css';
                        break;
                    
                    case 'js':
                        $ct='application/javascript';
                        break;
                    
                    default:
                        $finfo = new finfo();                
                        $ct=$finfo->file($local, FILEINFO_MIME_TYPE);
                        if (!filesize($local)) $ct='text/plain';
                        break;
                }
                $gso->setContentType($ct);
                if (in_array($ext,$gs_gzip)) {
                    $gso->setContentEncoding('gzip');
                    $data=gzencode($data);
                }
                $gso->setCacheControl('public,max-age='.(7*24*3600));

                $postbody = array('data' => $data,
                                    'mimeType'=>$ct,
                                    'uploadType'=>'media');
                
                try {
                    $o=$this->gcs_service->objects->get($this->gcs_bucket->name,$remote);
                    $ts_remote=strtotime($o->updated);
                    
                } catch (Exception $e) {
                    $ts_remote=0;
                    
                }
                
                if ($all || $ts_local>$ts_remote)
                {
                    $this->debug("Cloud Storage: $local ($ct) -> $remote");
                    
                    try {
                        $o=$this->gcs_service->objects->insert($this->gcs_bucket->name,$gso,$postbody);
                        $cmd=Tools::translate('Upload').' [GCS]: '.$remote.$info;
                        if (!$all) $cmd.=' ['.Kameleon::datetime($ts_local).' > '.Kameleon::datetime($ts_remote).']';
                        $this->log($this->ftp,$cmd,'OK',false);
                        $count++;
                        if ($local_compare_file) touch($local_compare_file,$touch_time);
                        
                    } catch (Exception $e) {
                        $this->log($this->ftp,$e->getMessage(),Tools::translate('FAIL'),false);
                        sleep(1);
                        $this->gcs_init();
                        $o=$this->gcs_service->objects->insert($this->gcs_bucket->name,$gso,$postbody);
                    }
                    
                } else {
                    if ($local_compare_file) touch($local_compare_file,$touch_time);
                }
                
                //mydie($o,date('d-m-Y H:i',$updateTime));
            }
            
        }
        
        return $count;
    }
    
    
    protected function transfer_page($sid){
        $this->debug("Transfer page sid=$sid");
        $index=new indexController();
        $webpage=new webpageModel($sid);
        
        if (!strlen($webpage->id)) return;
        $session=Bootstrap::$main->session();
        
        $path=$session['path'];

        if ($webpage->hidden) return;
        if ($webpage->noproof) return;
        
        
        $file_name=$webpage->file_name();
        
        Bootstrap::$main->session('webpage_file_name',$file_name);
        

        $ufiles=Bootstrap::$main->kameleon->relative_dir($file_name,$path['ufiles']);
        Bootstrap::$main->session('ufiles',$ufiles);

        $uimages=Bootstrap::$main->kameleon->relative_dir($file_name,$path['uimages']);
        Bootstrap::$main->session('uimages',$uimages);
        
        $images=Bootstrap::$main->kameleon->relative_dir($file_name,$path['images']);
        Bootstrap::$main->session('template_images',$images);
        
        $media=Bootstrap::$main->kameleon->relative_dir($file_name,$path['media']);
        Bootstrap::$main->session('media',$media);

        $include_path=Bootstrap::$main->kameleon->relative_dir($file_name,$path['include']);
        Bootstrap::$main->session('include_path',$include_path);
        
        $pages_path=Bootstrap::$main->kameleon->relative_dir($file_name,$path['pages']);
        Bootstrap::$main->session('pages_path',$pages_path);        
        
        if (!$path['template']) $path['template']='.';
        $template_dir=Bootstrap::$main->kameleon->relative_dir($file_name,$path['template']);
        Bootstrap::$main->session('template_dir',$template_dir);        

        if ($this->appengine_static_host) {
            if ($path['template']=='.') Bootstrap::$main->session('template_dir',$this->appengine_static_host); 
            else Bootstrap::$main->session('template_dir',$this->appengine_static_host.'/'.$path['template']); 
        
            Bootstrap::$main->session('template_images',$this->appengine_static_host.'/'.$path['images']);
            Bootstrap::$main->session('uimages',$this->appengine_static_host.'/'.$path['uimages']);
            Bootstrap::$main->session('media',$this->appengine_static_host.'/'.$path['media']);        
            Bootstrap::$main->session('ufiles',$this->appengine_static_host.'/'.$path['ufiles']);        
        
        }
        
        $content=$index->getPage(null,PAGE_MODE_PURE,$sid);
        
        $data=$content['data'];
        $data=array_merge(Bootstrap::$main->globals(),$data);
        
        $html=file_get_contents($content['template']);
        $html=$index->addKameleonTags($html, PAGE_MODE_PURE);
        
        $html=GN_Smekta::smektuj($html,$data,false,array(dirname($content['template']), VIEW_PATH . '/scripts', VIEW_PATH . '/replace'));
        
        
        $code_change=Bootstrap::$main->getConfig('default.ftp.code_change');        
        $html = Bootstrap::$main->tokens->code_change($html,$code_change);   

        
        
        if (strlen($html)) {
            $tmp = sys_get_temp_dir ().'/'.md5(time().rand(1000,9999)).'.tmp';
            file_put_contents($tmp,$html);
            $webpage->nd_ftp=time();
            $webpage->save(false);

            $ret=$this->transfer($tmp,$file_name,true,' ['.$webpage->id.']',array(),substr($file_name,-4)=='.php');
            unlink($tmp);
            
            return $ret;
        }
        
    }
    
    protected function transfer_pages($limit,$all,$server,$lang,$ver) {
        $count=0;
        
        $webpagetrash=new webpagetrashModel();
        $trash=$webpagetrash->getUnTrashed($server,$lang,$ver);
        
        foreach ($trash AS $wpt) {

            $err=Tools::translate('FAIL');
            $cmd=Tools::translate('Remove file').': '.$wpt['file_name'];
            
            $removed=false;
            
            if ($this->conn && @ftp_delete($this->conn,$wpt['file_name']))
            {
                $this->debug($cmd);
                $this->log($this->ftp,$cmd,'OK',false);
                $removed=true;
            }
               
            if ($this->appengine_path) {
                @unlink($this->appengine_path.'/'.$wpt['file_name']);
                
                $removed=true;
            }
            
            if ($this->gcs_bucket) {
                try {
                    $o=$this->gcs_service->objects->get($this->gcs_bucket->name,$wpt['file_name']);
                    if ($o->name) $this->gcs_service->objects->delete($this->gcs_bucket->name,$wpt['file_name']);
                    $removed=true;
                } catch (Exception $e) {
                    $removed=false;
                }
            
            }
            
            if ($removed) {
                $webpagetrash->markAsTrashed($wpt['id']);
            }
            else
            {
                $webpagetrash->markAsTrashed($wpt['id'],'U');
            }
        }
        
        $webpage=new webpageModel();

        $sids=$webpage->sidsForFtp($limit,$all,$server,$lang,$ver);
        
        if (count($sids) > 500) GN_Smekta::set_debug_fun(null);
        
        foreach($sids AS $sid) $count+=$this->transfer_page($sid);
        return $count;
    }
    
    
    protected function transfer_includes ($all)
    {
        $session=Bootstrap::$main->session();
        $config=Bootstrap::$main->getConfig();

        if (isset($config['webtd']['widget']['transfer']) && is_array($config['webtd']['widget']['transfer']))
        {
            foreach ($config['webtd']['widget']['transfer'] AS $widget=>$dst)
            {
                $this->transfer(__DIR__.'/../widgets/'.$widget.'/include',
                                $session['path']['include'].'/'.$dst,
                                $all,'',array(),true); 
            }
        }
        
        $res=$this->transfer($session['uincludes'],$session['path']['include'],$all,'',array(),true);
        
        
        if ($res===false) return false;
        
        $res+=$this->transfer(__DIR__.'/../../library/GN/Smekta.php',$session['path']['include'].'/_smekta.php',$all,'',array(),true);
        
        return $res;
    }

    protected function transfer_images($all)
    {
        $session = Bootstrap::$main->session();
        $config = Bootstrap::$main->getConfig();
        
        $c = 0;

        require_once APPLICATION_PATH.'/classes/Widget.php';
        foreach ($config['webtd']['type'] AS $td) {
            if (isset($td['widget']) && strlen($td['widget']) && file_exists(WIDGETS_PATH . '/' . $td['widget'] . '/widget.php')) {
                require_once WIDGETS_PATH . '/' . $td['widget'] . '/widget.php';
                $classname=str_replace('/','_',$td['widget']).'Widget';
                $w=new $classname();
                $img=$w->default_images_path();
                
                if (file_exists($img)) {
                    $res = $this->transfer($img, $session['path']['images'].'/'.$w->default_images_dest(), $all,'',array(),false,$session['server']['id']);
                    if ($res) $c += $res;
                }
            
            }
        }
        

        $this->transfer(WIDGETS_PATH . '/common/images', $session['path']['images'] . '/widgets/common', $all,'',array(),false,$session['server']['id']);

        if (file_exists($session['template'] . '/images')) {
            $res = $this->transfer($session['template'] . '/images', $session['path']['images'], $all,'',array(),false,'templateimages/');
            if ($res === false) return false;
            $c += $res;
        }
        
        return $c;
    }
    
    
    protected function transfer_media($all)
    {
        $webmedia=new webmediaModel();
        $gallery=new galleryController();
        $path=Bootstrap::$main->session('path');
        
        
        $count=0;
        foreach($webmedia->getAll() AS $media) {
            
            $info=$gallery->info($media['target'],$media['username']);
            
            if (!isset($info['name'])) continue;
            $remote=$path['media'].'/'.$info['name'];
            if (!$all) {
                $ts_remote=0+@ftp_mdtm($this->conn,$remote);
                $ts_local=$info['ts'];
                if ($ts_local<=$ts_remote) continue;
            }            

            if (strstr($info['mime'],'svg')) $remote.='.svg';
                     
            $tmp = sys_get_temp_dir ().'/'.md5(time().rand(1000,9999)).'.tmp';
            file_put_contents($tmp,$gallery->get_content($media['target'],$media['username']));
            $this->transfer($tmp,$remote,true);
            unlink($tmp);

            
        }
    }
    
    protected function transfer_uimages($all)
    {
        $session=Bootstrap::$main->session();
        
        
        $res=$this->transfer($session['uimages_path'],$session['path']['uimages'],$all);
        if ($res===false) return false;
        return $res;
        
    }
    
    
    protected function template_dirs()
    {
        $session=Bootstrap::$main->session();
        $config=Bootstrap::$main->getConfig();

        $template_path=$session['path']['template'];

        $exclude=explode(',',$config['template']['exclude']);
        $exclude[]='.';
        $exclude[]='..';
        foreach ($exclude AS &$ex) $ex=trim($ex);
        
        
        $dir=scandir($session['template_path']);

        $dirs=array();
        
        foreach($dir AS $file) {
                if (in_array($file,$exclude)) continue;
                $dirs[]=$template_path ? $template_path.'/'.$file : $file;
        }
        
        return $dirs;
    }
    
    
    
    protected function transfer_template($all,$isTemplate=false)
    {
        $session=Bootstrap::$main->session();
        
        $dirs=$this->template_dirs();
        $sum=0;
    
        foreach($dirs AS $dir) {
            $sum+=$this->transfer($session['template_path'].'/'.$dir,$dir,$all);
        }
        $sum+=$this->transfer(APPLICATION_PATH.'/views/replace/scripts.jquery-noconflict.js','jquery-noconflict.js',$all,'',array(),false,$session['server']['id']);
        if (count($dirs) && $isTemplate)
        {
            $dir=dirname($session['template_path'].'/'.$dirs[0]);
            if (file_exists($dir.'/.thumbnail.jpg')) $sum+=$this->transfer($dir.'/.thumbnail.jpg','.thumbnail.jpg',$all);
            if (file_exists($dir.'/.thumbnail.png')) $sum+=$this->transfer($dir.'/.thumbnail.png','.thumbnail.png',$all);
        }
        
        
        
        return $sum;
        
    }

    protected function transfer_ufiles($all)
    {
        $session=Bootstrap::$main->session();
        
        $res=$this->transfer($session['ufiles_path'],$session['path']['ufiles'],$all);
        if ($res===false) return false;
        return $res;
        
    }


    protected function transfer_root($all)
    {
        $session=Bootstrap::$main->session();
        
        $res=$this->transfer($session['ufiles_path'].'/.root','.',$all,'',array('htaccess'=>'.htaccess'));
        if ($res===false) return false;
        return $res;
    }    
    
    protected function root_dir($dir,$level=0)
    {
        $dira=explode('/',$dir);
        

        $cdira=count($dira);
        for ($i=$level+1;$i<$cdira;$i++) unset($dira[$i]);

        
        return implode('/',$dira);
    }
    
    protected function transfer_sitemap()
    {
        $session=Bootstrap::$main->session();
        $default = Bootstrap::$main->getConfig('default');
        
        $http_url=$session['server']['http_url'];
        if (substr($http_url,-1)!='/') $http_url.='/';
        
        $webpage=new webpageModel();
        
        $sids=$webpage->sidsForFtp('',true,$session['server']['id'],$session['langs_used'],$session['ver']);
        
        if (count($sids) > 500) GN_Smekta::set_debug_fun(null);
        
        sort($sids);
        $files=array();
        foreach($sids AS $sid) {
            $webpage->get($sid);
            $file_name_missing=!$webpage->file_name;
            Bootstrap::$main->setPath($webpage->lang);
            $file_name=$webpage->file_name();
            if ($webpage->hidden) continue;
            if ($webpage->nositemap) continue;
            if (!$webpage->nd_ftp) continue;
            
            
            
            foreach ($default['directory_index'] AS $index) {
                if (substr($file_name, -1 * strlen($index)) == $index) {
                    $file_name = substr($file_name, 0, strlen($file_name) - strlen($index));
                    continue;
                }
            }            
            
            if (!strlen($file_name))
            {
                $priority=1;
            }
            elseif ($file_name==$webpage->lang.'/')
            {
                $priority=0.9;
            }
            else
            {
                $priority=0.9-round(substr_count($file_name,'/')/16,2);
                if ($priority<0.3) $priority=0.25;
                if ($file_name_missing) $priority=0.2;
            }
            
            $url=$http_url.$file_name;
            
            
            $data=$webpage->data();
            $data['file_name']=$file_name;
            $data['url']=$url;
            $data['priority']=$priority;
            
            if ($webpage->og_image) {
                $urlimg=$http_url;
                if ($this->appengine_path && isset($this->gcs_bucket->name)) $urlimg='http://'.$this->gcs_bucket->name.'/';
                $data['image']=$urlimg.$session['path']['uimages'].'/'.$webpage->og_image;
            }
            
            $files[sprintf('%03d',100*$priority).$file_name]=$data;

        }
        krsort($files);
        
        $sitemap=GN_Smekta::smektuj(VIEW_PATH.'/scripts/sitemap.xml',array('pages'=>$files,'kameleon'=>Bootstrap::$main->kameleon));
        
        $tmp = sys_get_temp_dir ().'/'.md5(time().rand(1000,9999)).'.tmp';
        file_put_contents($tmp,$sitemap);
        $this->transfer($tmp,'sitemap.xml',true);        
        unlink($tmp);
    }
    
    protected function appengine($id,$ver,$scripts,$cron,$md5,$rewrite,$user,$ftp,$resitemap)
    {
        $this->log($this->ftp,"Appengine transfer started",true);
        $session=Bootstrap::$main->session();
        
        $app="application: $id\nversion: $ver\nruntime: php55\napi_version: 1\n\nhandlers:\n";
    
        $static_dirs=array();
        $static_files=array();
        
        /*
        
        foreach($this->template_dirs() AS $dir)
        {
            if (is_dir($session['template_path'].'/'.$this->root_dir($dir))) $static_dirs[$this->root_dir($dir)]=1;
            else $static_files[$this->root_dir($dir)]=1;
        }
        
        $static_dirs[$this->root_dir($session['path']['ufiles'])]=1;
        $static_dirs[$this->root_dir($session['path']['uimages'])]=1;
        $static_dirs[$this->root_dir($session['path']['images'])]=1;
    
    
    
        $static_files['sitemap.xml']=1;
        
        
    
        foreach (array_keys($static_dirs) AS $dir)
        {
            $app.="- url: /$dir\n  static_dir: $dir\n\n";
        }

        foreach (array_keys($static_files) AS $file)
        {
            $app.="- url: /$file\n  static_files: $file\n  upload: $file\n\n";
        }

        */
    
        $webpage=new webpageModel();
        
        $notfound='';
        $pages=array();
        $calculate_md5=false;
        $tmp = sys_get_temp_dir ().'/'.md5(time().rand(1000,9999)).'.tmp';
        
        if ($resitemap) {

            $sids=$webpage->sidsForFtp('',true,$ftp->server,$session['langs_used'],$ftp->ver);
            if (count($sids) > 500) GN_Smekta::set_debug_fun(null);
            sort($sids);
            foreach($sids AS $sid) {
                $webpage->get($sid);
                Bootstrap::$main->setPath($webpage->lang);
                $file_name=$webpage->file_name();
                if ($webpage->hidden) continue;
                
                $login = $webpage->appengine_login();
                
                if (!$notfound)
                {
                    $notfound=$file_name;    
                    $notfound_login=$login;
                }
                
                $basename=basename($file_name);
                $dirname=dirname($file_name);
                if ($dirname=='.') $dirname='/';
                else $dirname='/'.$dirname;
                 
                $scriptorstatic=substr($file_name,-4)=='.php'?'script':'static';
                
                if (substr($basename,0,5)=='index')
                {
                    $pages[$login?:'free'][$scriptorstatic][$dirname] = $file_name;
                    if ($dirname!='/') $pages[$login?:'free'][$scriptorstatic][$dirname.'/'] = $file_name;
                }
        
                $pages[$login?:'free'][$scriptorstatic]['/'.$file_name] = $file_name;
    
            
            }
            
            $rootlogin='free';
            $app_json=array();
            
            for ($level=0;$level<10;$level++)
            {
                $dirs=array();
                foreach($pages AS $login=>$types)
                {
                    foreach($types AS $type=>$files)
                    {
                        foreach($files AS $d=>$file)
                        {
                            $rd=$this->root_dir($d,$level)?:'/';
                            
                            if ($level>0)
                            {
                                $rdd=$this->root_dir($d,$level-1)?:'/';
                                
                                if (!in_array($rdd,$dirs_conflicted)) continue;
                            }
                            else
                            {
                                $app_json['s'][$type][$d]=$file;
                            }
                            
                            $dirs[$rd][$login]=1;
                        }
                    }
                }
                
                $dirs_conflicted = array();
                
                foreach($dirs AS $dir=>$logins)
                {
                    if (count($logins)==1)
                    {
                        $login=current(array_keys($logins));
                        
                        if ($dir=='/')
                        {
                            $rootlogin=$login;
                            continue;
                        }
                        
                        $app.="- url: ".str_replace('.','\\.',$dir).".*\n";
                        $app.="  script: _app.php\n";
                        if ($login!='free') $app.="  login: ".$login."\n";
                        $app.="\n";
                    }
                    else
                    {
                        $dirs_conflicted[]=$dir;
                    }
                }
                
                if (count($dirs_conflicted)==0) break;
            }
            
            
              
            
            if ($scripts) {
                if (!is_array($scripts)) $scripts=explode("\n",$scripts);
                foreach ($scripts AS $script)
                {
                    if (!trim($script)) continue;
                    $script=explode(':',$script);
                    
                    //$res=$this->transfer($session['uincludes'],$session['path']['include'],$all);
                    if (!file_exists($session['uincludes'].'/'.$script[0])) continue;
                    $app.="- url: /".$session['path']['include'].'/'.$script[0]."\n";
                    $app.="  script: ".$session['path']['include'].'/'.$script[0]."\n";
                    if (isset($script[1]) && $script[1]) $app.="  login: ".$script[1]."\n";
                    $app.="\n";   
                }
            }
            
    

            
            
            if ($rewrite)
            {
                foreach(explode("\n",$rewrite) AS $r)
                {
                    $app_json['r'][]=explode('~',$r);
                }           
            }
            
            $app_json['b']=$this->gcs_bucket->name;
            
            $app_json=serialize($app_json);
        
            $cron=trim($cron);
            
            
            $template_appengine=Bootstrap::$main->session('template_path').'/appengine';
            if (file_exists($template_appengine)) {
                $dir=scandir($template_appengine);
                foreach($dir AS $file) {
                    if ($file[0]=='.') {
                        continue;
                    }
                    
                    if ($file=='app.yaml') {
                        $app.="\n\n". file_get_contents("$template_appengine/$file");
                    } else {
                        $this->transfer("$template_appengine/$file",$file,false,'',array(),true);                        
                    }
                }
    
            }

            $app.="\n\n- url: /.*\n";
            $app.="  script: _app.php\n";
            if ($rootlogin!='free') $app.="  login: ".$rootlogin."\n";
            $app.="\n";



            $calculate_md5=md5($app.$cron.$app_json);
            
            
            if ($md5 != $calculate_md5) 
            {
                file_put_contents($tmp,$app);  
                $this->transfer($tmp,'app.yaml',true,'',array(),true);
    
                file_put_contents($tmp,$app_json);  
                $this->transfer($tmp,'_app.ser',true,'',array(),true);
                
                if ($cron) {
                    
                    $cron_yaml="cron:\n";
                    foreach(explode("\n",$cron) AS $c)
                    {
                        $c=explode('|',$c);
                        $cron_yaml.="- description: ".$c[0]."\n  url: ".$c[1]."\n  schedule: ".$c[2]."\n\n";
                    }
                    
                    file_put_contents($tmp,$cron_yaml);
                    $this->transfer($tmp,'cron.yaml',true,'',array(),true);
                }           
            }
            else
            {
                $calculate_md5=false;
            }
        }
        
        $this->transfer(__DIR__.'/../../library/GoogleAppEngine/_app.php','_app.php',false,'',array(),true);
        $this->transfer(__DIR__.'/../../library/GoogleAppEngine/mime.ser','_mime.ser',false,'',array(),true);
       

        
        
        $client=Google::getUserClient($user,false,'appengine');
        $access_token=json_decode($client->getAccessToken())->access_token;
        $appengine=Bootstrap::$main->getConfig('appengine');

        $user=new userModel($user);        
        
        $cmd=$appengine['sdk'].'/appcfg.py -e '.$user->email.' --oauth2_access_token='.$access_token.' update '.$this->appengine_path.' >'.$tmp.' 2>&1';
        
        
        $lock=$this->appengine_path.'/.appengine_lock';
        
        if (file_exists($lock))
        {
            $this->debug("Appengine transfer locked: $lock");
            $this->log($this->ftp,"Appengine locked: $lock",false,false);             
        }
        else
        {
            if ($this->appengine_need_transfer) {
                file_put_contents($lock,'a');
                $this->debug("Run: $cmd");
                    
                system($cmd);
                unlink($lock);
                $result=file_get_contents($tmp);
                
                $this->debug(nl2br($result));
                
                foreach(explode("\n",$result) AS $res)
                {
                    if (trim($res)) $this->log($this->ftp,trim($res),"OK",false); 
                }
            } 
            $this->log($this->ftp,"Appengine transfer ended",true);
            
            
        }
    
        
        
        if (file_exists($tmp)) unlink($tmp);
        
        return $calculate_md5;
    }
    
    
    
    
    protected function log($ftp,$command,$result,$trans=true) {
        $ftplog=new ftplogModel();
    
        $ftplog->nczas=time();
        $ftplog->ftp_id=$ftp->id;
        $ftplog->rozkaz=$trans?Tools::translate($command):$command;
        $ftplog->wynik=$trans?Tools::translate($result):$result;
                
        $ftplog->save();
        
    }
    
    
    
    
    public function setup()
    {
        $session=Bootstrap::$main->session();
        $ret = array('session'=>$session,'ftp'=>$session['server']);
        
        $session_server=$session['server'];
        $server=new serverModel($session_server['id']);
        
        if (isset($_POST['ftp'])) {
            
            
            
            if (!$session['user']['admin'])
            {
                if (!$session_server['owner'])
                {
                    Bootstrap::$main->error(ERROR_ERROR,'You are not the owner');
                    return $ret;
                }
                
                if ($session_server['nd_expire'] && (!$session_server['nd_last_payment'] || $session_server['nd_expire']<Bootstrap::$main->now) )
                {
                    Bootstrap::$main->error(ERROR_ERROR,'Publishing parameters will be changeable after the payment is made');
                    return $ret;                    
                }
            }
            
            
            $ftp=$_POST['ftp'];

            if (!$ftp['ftp_pass']) $ftp['ftp_pass']=$server->ftp_pass; 
            if (!$this->connect($ftp['ftp_server'],$ftp['ftp_user'],$ftp['ftp_pass']))
            {
                $ret['error']=$this->error;
            } else {

                foreach ($ftp AS $k=>$v) if (substr($k,0,4)=='ftp_') {
                    $server->$k=$v;
                }
                if ($server->save())
                {
                    foreach ($server->data() AS $k=>$v) if (substr($k,0,4)=='ftp_') {
                        $session_server[$k]=$v;
                    }   
                    $this->redirect('ftp');
                    Bootstrap::$main->session('server',$session_server);
                }
                
                
                 
            }
            
            
            $ret['ftp']=$ftp;
        }
        
        return $ret;        
    }
    
    
    protected function connect ($host,$user,$pass) {
        if (!function_exists('ftp_connect')) {
            $this->error='No FTP module';
            return false;
        }
        
        $host=explode(':',$host);
        if (!isset($host[1]) || !$host[1]) $host[1]=21;
        $this->conn=ftp_connect($host[0],$host[1]);
       
        if (!$this->conn) {
            $this->error='Couldn\'t connect to host';
            return false;
        }
    
        $result=ftp_login($this->conn, $user, $pass);
        if (!$result) {
            $this->error='Couldn\'t login to host using supplied data';
            return false;
        }
        
        return $this->conn;
    }
    
    protected function close()
    {
        $this->statcache=array();
        if ($this->conn) ftp_quit($this->conn);
    }
    
    protected function mkdir($dir)
    {
        if (!$dir || $dir=='/') return false;
        
        $res=@ftp_mkdir($this->conn,$dir);
        if (!$res) {
            if ($this->mkdir(dirname($dir))) {
                return $this->mkdir($dir);
            }
            return false;
            
        }
        if ($this->appengine_path) mkdir($this->appengine_path.'/'.$dir,0755,true);
        @ftp_chmod($this->conn,0755,$dir);
        
        $this->log($this->ftp,Tools::translate('Create').' '.$dir,'OK',false);
        return true;
    }
    
    protected function chdir($dir,$mkdir=true)
    {
        if (!$this->conn) return true;
        
        if (!@ftp_chdir($this->conn,$dir) ) {

            if (!$mkdir) return false;
        
            if ($this->mkdir($dir)) {
                return ftp_chdir($this->conn,$dir);
            }
            
            $this->log($this->ftp,Tools::translate('Create').' '.$dir,Tools::translate('FAIL'),false);
            return false;
        }
        return true;
    }
    
    protected function statdir($dir)
    {
        if (!$this->conn) return null;
        if (isset($this->statcache[$dir])) return $this->statcache[$dir];
        
        $pwd=ftp_pwd($this->conn);
        
        $this->statcache[$dir]=$this->chdir($dir);
        $this->chdir($pwd);
        
        return $this->statcache[$dir];
    }
    
    public function remove($server,$user,$pass,$passive,$dir,$debug=false)
    {
        if (!$this->connect_chdir($server,$user,$pass,$passive,$dir,null,false))
        {
            if ($debug) echo "Could not connect to $user:$pass@$server/$dir\n";
            return false;
        }
        
        $pwd=ftp_pwd($this->conn);
        
        if ($pwd=='/')
        {
            $dir='/';
        }
        else
        {
            ftp_chdir($this->conn,'..');
            $dir=basename($pwd);
        }
        
        
        if ($dir=='.' || $dir=='/')
        {
            return $this->rm(ftp_nlist($this->conn,"-la $dir"),$debug);
        } else {
            return $this->rm($dir,$debug);
        }
        
    
        
    }
    
    private $rmdircache=array();
    
    protected function rm($f,$debug=false)
    {       
        if (!is_array($f)) $f=array($f);
        

        foreach ($f AS $file)
        {
            if (basename($file)=='.' || basename($file)=='..') continue;
            
            if (isset($this->rmdircache[$file]) && $this->rmdircache[$file])
            {
                echo "False due to cycle: $file\n";
                return false;
            }
            $this->rmdircache[$file]=true;
            

            $res=@ftp_delete($this->conn,$file);
                        
            if (!$res)
            {
                $res=@ftp_rmdir($this->conn,$file);
                if ($res) continue;
                
                $list=ftp_nlist($this->conn,"-la $file");
                
                if (is_array($list) && count($list)>0) 
                {
                    $res=$this->rm($list,$debug);
                    if (!$res) return false;
                }
          
                $res=@ftp_delete($this->conn,$file);
                if (!$res) $res=@ftp_rmdir($this->conn,$file);
                if (!$res)
                {
                    echo "False - couldn't remove $file\n";
                    return false;
                }
            }
        }
        
        return true;
        
    }
    
}
