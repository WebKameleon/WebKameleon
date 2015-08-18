<?php

class wizardController extends Controller
{
    public function get()
    {
        $auth = new authController();
        $auth->getServers(true);

        $data = Bootstrap::$main->session();

        $shared = array();
        $templates = array();
        
        $s=new serverModel();
        
        
        
        
        foreach ($data['servers'] as $k => $server) {
            
            $data['servers'][$k]['expired'] = $server['nd_expire'] && $server['nd_expire']<Bootstrap::$main->now;
                 
            if (!$server['owner']) {
                $s->get($server['id']);
                $data['servers'][$k]['creator']=$s->creator()->data();
                $shared[] = $data['servers'][$k];
                unset($data['servers'][$k]);
            } else if ($server['social_template']) {
                $templates[] = $data['servers'][$k];
                unset($data['servers'][$k]);
                
            }
        }

        
        $data['shared'] = $shared;
        
        $data['templates'] = $templates;
        
        
        $data['all_servers_count'] = count($data['servers']) + count ($data['shared']) + count ($data['templates']);
        
        
        if (isset($data['trash'])) $data['all_servers_count']+=count($data['trash']);
        
        return $data;
    }

    public function trash()
    {
        $auth = new authController();
        $auth->getServers(true);
        $data = Bootstrap::$main->session();

        foreach ($data['trash'] as &$server) {
            $server['tmb'] = Tools::get_template_thumb($server);
        }

        return $data;
    }

    public function create_zip(&$filename = null)
    {
        $session = Bootstrap::$main->session();

        if ($filename == null)
            $filename = FILES_PATH . '/' . $session['server']['nazwa'] . '_' . sprintf('%08X', time()) . '.wkz';

        $dir = dirname($filename);

        $zip = new Zipper;

        if ($zip->open($filename, ZipArchive::CREATE) !== true) {
            Bootstrap::$main->error(ERROR_ERROR, 'Could not create a file in %s', $dir);
            return false;
        }

        if (isset($session['uimages_path']) && file_exists($session['uimages_path']) ) $zip->addDir($session['uimages_path'], 'uimages');
        if (isset($session['ufiles_path']) && file_exists($session['ufiles_path']) ) $zip->addDir($session['ufiles_path'], 'ufiles');
        if (isset($session['uincludes']) && file_exists($session['uincludes'])) $zip->addDir($session['uincludes'], 'include');

        if ($session['template_media'])
            $zip->addDir($session['template_path'], 'template');
        else
            $zip->addFromString('template.txt', $session['server']['szablon']);

        $zip->addFromString('server_d_xml.txt', $session['server']['d_xml']);

        $webtd = new webtdModel;
        $zip->addFromString('webtd.ser', serialize($webtd->export()));
        $webpage = new webpageModel;
        $zip->addFromString('webpage.ser', serialize($webpage->export()));
        $weblink = new weblinkModel;
        $zip->addFromString('weblink.ser', serialize($weblink->export()));

        return $zip->close();
    }

    public function export($return=false)
    {
        if ($this->id) {
            $path = FILES_PATH .'/'. $this->id;
            if (file_exists($path)) {
                header('Content-type: application/x-zip');
                header('Content-Disposition: attachment; filename=' . $this->id);
                readfile($path);
                unlink($path);
            } else {
                header('HTTP/1.0 404 Not Found');
            }
            die;
        }

        $session = Bootstrap::$main->session();

        $result = array(
            'status' => $this->create_zip($filename) ? 1 : 0
        );

        if ($return) return $filename;
        
        if ($this->_getParam('to') == 'drive') {
            $drive = Google::getDriveService();

            $file = new Google_Service_Drive_DriveFile();
            $file->setTitle($session['server']['nazwa'] . '.wkz');
            $file->setMimeType('application/x-zip');
            ini_set('memory_limit', '2048M');
            @$drive->files->insert($file, array(
                'data' => file_get_contents($filename),
                'mimeType'=>'application/x-zip',
                'uploadType'=>'multipart'
            ));
            unlink($filename);
        } else if ($this->_getParam('to') == 'template') {
	    /*
            $newfilename = MEDIA_PATH . '/templates/' . $session['server']['nazwa'] . '/' . $session['server']['ver'] . '/' . $session['server']['nazwa'] . '.wkz';
            $newdir = dirname($newfilename);

            if (!is_dir($newdir))
                mkdir($newdir, 0777, true);

            if (file_exists($newfilename))
                unlink($newfilename);

            rename($filename, $newfilename);
            */
        } else {
            $result['filename'] = basename($filename);
        }

        header('Content-type: application/json');
        die(json_encode($result));
    }

    public function import($s_id, $filename)
    {
        $server = new serverModel();

        if (!$server->isEmpty($s_id)) {
            Bootstrap::$main->error(ERROR_ERROR, 'Service contains data');

            return false;
        }

        $server->get($s_id);

        $zip = new Zipper();
        if ($zip->open($filename) !== true) {
            Bootstrap::$main->error(ERROR_ERROR, 'Could not open file %s', $filename);

            return false;
        }

        if ($entry = $zip->locateName('server_d_xml.txt')) {
            $d_xml = $zip->getFromIndex($entry);
            $server->d_xml = trim($d_xml);
        }

        if ($entry = $zip->locateName('template.txt')) {
            $template = $zip->getFromIndex($entry);
            $server->szablon = trim($template);
        } else {
            $name = $server->nazwa;
	    $path = MEDIA_PATH . '/' . $name . '/template';
            $zip->extractDirTo($path . '/' . $server->ver, 'template');
            $server->szablon = basename($name);
        }

        if ($entry = $zip->locateName('webpage.ser')) {
            $webppage = new webpageModel();
            $webppage->import($server->id, unserialize($zip->getFromIndex($entry)));
        }

        if ($entry = $zip->locateName('webtd.ser')) {
            $webptd = new webtdModel();
            $webptd->import($server->id, unserialize($zip->getFromIndex($entry)));
        }

        if ($entry = $zip->locateName('weblink.ser')) {
            $webplink = new weblinkModel();
            $webplink->import($server->id, unserialize($zip->getFromIndex($entry)));
        }

        $zip->extractDirTo(MEDIA_PATH . '/'. $server->nazwa . '/images/' . $server->ver, 'uimages');
        $zip->extractDirTo(MEDIA_PATH . '/'. $server->nazwa . '/files' , 'ufiles');
        $zip->extractDirTo(MEDIA_PATH . '/'. $server->nazwa . '/include/' . $server->ver, 'include');

        $zip->close();
        

        $server->save();

        $this->setPrimaryLang($server);
        $auth = new authController();
        $auth->getServers(true);


        return $server;
    }
    
    public function setPrimaryLang($server)
    {
        $webppage = new webpageModel();

        $ulang=Bootstrap::$main->session('ulang')?:'en';
        $langs=$webppage->count_pages($server->id);
    
        $max=0;
        foreach ($langs AS $lang)
        {
            if ($lang['count']>0 && $lang['lang']==$ulang)
            {
                $server->lang=$lang['lang'];
                break;
            }
            
            if ($lang['count']>$max || ($lang['lang']=='en' && $lang['count']>1))
            {
                $max=$lang['count'];
                $server->lang=$lang['lang'];
            }
        }
        
        $server->save();
    }

    public function copy_template()
    {
        $server   = Bootstrap::$main->session('server');
        $template = Bootstrap::$main->session('template');

        $dst = MEDIA_PATH . '/' . $server['nazwa'] .'/template/' . $server['ver'];

        if (file_exists($dst)) {
            $error = 'Copy of a template already exists';
        } else {
            Tools::cp_r($template, $dst);

            $serverModel = new serverModel($server);
            $serverModel->szablon = $server['nazwa'];
            $serverModel->save();

            //$auth = new authController();
            //$auth->getServers(true);
            Bootstrap::$main->setGlobals(true);
        }

        $ret = array(
            'status' => 1
        );
        if (isset($error)) {
            $ret = array(
                'status' => 0,
                'error' => $this->trans($error)
            );
        }

        die(json_encode($ret));

    }


    
    
    public function create()
    {
        $data = Bootstrap::$main->session();
        $templates = Tools::get_templates();
        
        Bootstrap::$main->session('wizard_template_tmb', false);
        
        if (isset($_POST['wizard']['template'])) {
        
            $template = $_POST['wizard']['template'];
            
            if ($template=='default') $template='.default';

            if (filter_var($template, FILTER_VALIDATE_URL) === true) {
                // URL
            } else if (strpos($template, 'local:') === 0) {
                // lokalny plik *.wkz
            } else if (strpos($template, 'drive:') === 0) {
                // z google drive
            } else if (strlen($template) && file_exists(APPLICATION_PATH.'/templates/'.$template)) {
                // z templatów
            } elseif ( $template=='.default' ) {
                // template
            } else if (strpos($template, 'media/')===0 && file_exists(MEDIA_PATH.'/'.substr($template,6))) {
                // ktoś zrobił z serwisu template
            } else {
                $error = 'Invalid template';
            }
            
            if (isset($error)) {
                Bootstrap::$main->error(ERROR_WARNING, $error);
            } else {
                Bootstrap::$main->session('wizard_template', $template);
                return $this->redirect('wizard/name');
            }
        } else {
            Bootstrap::$main->session('wizard_template', false);
        }

        $data['wizard'] = $templates;
        

        return $data;
    }

    public function name()
    {
        $data = Bootstrap::$main->session();
        $template = Bootstrap::$main->session('wizard_template');        

        
        if ($this->_getParam('media'))
        {
            $server=new serverModel();
            $server->find_one_by_nazwa($this->_getParam('media'));
    
            if ($server->social_template)
            {
                $template='media/'.$server->nazwa;
            }
        }
        elseif ($this->_getParam('state'))
        {
            $state=json_decode($this->_getParam('state'));
            if (is_array($state->ids) && count($state->ids) && strlen($state->ids[0])) Bootstrap::$main->session('wizard_template','drive:'.$state->ids[0]);
                
        
            $client=Google::getUserClient(null,false,'drive');
            $service = Google::getDriveService($client);
            $file=$service->files->get($state->ids[0]);
            
            $template='drive:'.$state->ids[0];
        
            $data['wizard']['suggested_name']=preg_replace('/\.wkz$/','',$file['title']);
            $data['wizard']['drive']=1;
            $data['wizard']['author']=$file['ownerNames'][0];
        }
        elseif (substr($template,0,6)=='drive:')
        {
            $id=substr($template,6);
            
            $client=Google::getUserClient(null,false,'drive');
            $service = Google::getDriveService($client);
            $file=$service->files->get($id);
            
            $data['wizard']['author']=$file['ownerNames'][0];
            $data['wizard']['suggested_name']=preg_replace('/\.wkz$/','',$file['title']);
            $data['wizard']['drive']=1;
        }
        
        
        


        if (empty($template)) {
            return $this->redirect('wizard/create');
        }

        Bootstrap::$main->session('wizard_template',$template);

        
        if (isset($_POST['wizard']['name'])) {
            
            $name = $_POST['wizard']['name'];
            $ajaxController = new ajaxController;
            
            
            
            if (isset($_POST['import']['server']) && strlen($_POST['import']['server']))
            {
                $data['suggested_name']=$_POST['wizard']['name'];
                $data['import'] = $_POST['import'];
            
                if ($a=$this->check_import($data['import'],$template))
                {
                    $ret = $ajaxController->wizard_create($name, $template,null,$a);  
                }
                else
                {
                    $ret['error']='No fullfilling data';
                }
            
            } else {          
                if (filter_var($template, FILTER_VALIDATE_URL) === false) {
                    $ret = $ajaxController->wizard_create($name, $template);
                } else {
                    $ret = $ajaxController->wizard_create($name, null, $template);
                }
    

            }

            if ($ret['error']) {
                Bootstrap::$main->error(ERROR_ERROR, $ret['error']);
            } else {
                return $this->redirect('index/get?setServer='.$ret['data']['nazwa']);
            }


        }

        $data['wizard']['template'] = $template;

        if (substr($template,0,6)=='media/')
        {
            $server=new serverModel();
            $server->find_one_by_nazwa(substr($template,6));
            
            $config = Bootstrap::$main->getConfig('payment');
            $lang = Bootstrap::$main->session('ulang');
            $info = $config['default'];
            if (isset($config[$lang])) {
                $info = array_merge($info, $config[$lang]);
            }            
            
            $field='social_template_price_'.$lang;
        
            $info['template_price']=$server->$field?:$server->social_template_price_en;
            $data['info']=$info;
        }
        
        return $data;
    }
    
    protected function check_import(&$data,$template)
    {
        if (!isset($data['adapter'])) $data['adapter']='pgsql';
        
        
        $dsn = $data['adapter'] . '://'
             . $data['user'] . ':'
             . $data['pass'] . '@'
             . $data['server'] . ':' . $data['port'] . '/'
             . $data['db'];
        
        
        $conn=Doctrine_Manager::connection($dsn);
        $conn->setCharset('utf-8');
        
        try {
            $connected=$conn->connect();    
        } catch (Exception $e) {
            Bootstrap::$main->error(ERROR_ERROR, $e->getMessage());         
            return false;
        }
        
        $server=new serverModel();
        $server->conn($conn);
        $website=$server->find_one_by_nazwa($data['website']);
        
        if (!$website)
        {
            Bootstrap::$main->error(ERROR_ERROR, "Couldn't find remote website: %s",array($data['website']));
            return false;
        }
        
        $webpage=new webpageModel();
        $webpage->conn($conn);
    
        $webpage->server=$website['id'];
        $webpage->lang=null;
        $webpage->ver=$website['ver'];
        
        $data['types']=$webpage->types();
        
        
        foreach($data['types'] AS &$type)
        {
            $type['type']+=0;
        }
        
        
        $webtd=new webtdModel();
        $webtd->conn($conn);
    
        $webtd->server=$website['id'];
        $webtd->lang=null;
        $webtd->ver=$website['ver'];
        
        $data['levels_body']=$webtd->levels(true);
        $data['levels_hf']=$webtd->levels(false);
        
        
        foreach($data['levels_hf'] AS &$level)
        {          
            $level['part'] = $level['page_id']%100==-5?'header':'footer';
        
            $level['type'] = floor(abs($level['page_id'])/100);
        
        }
        
    
        
        
        if (substr($template,0,6)=='media/')
        {
            $media=substr($template,6);
            $config=Bootstrap::$main->config2array(parse_ini_file(MEDIA_PATH.'/'.$media.'/template/1/config.ini'));
            
            $data['template']['levels']=$config['level'];
            $data['template']['types']=$config['webpage']['type'];
            
            
        }
        
        if (!isset($data['transcode'])) return false;
        if (!$this->isArrayFullyFilled($data['transcode'])) return false;
        
        $weblink=new weblinkModel();
        $weblink->conn($conn);
        
        $weblink->server=$website['id'];
        $weblink->lang=null;
        $weblink->ver=$website['ver'];
        
        
        
        $sqldata=array();
        
        $sqldata['webpage'] = $webpage->export(null,$data['ver']);
        foreach ($sqldata['webpage'] AS &$d)
        {
            $d['lang']=$this->change_lang($d['lang']);          
            $d['ver'] = 1;
            $d['type'] = $data['transcode']['type'][$d['type']+0];
            
            
            foreach ($d AS $k=>$v) if (!strlen(trim($v))) unset($d[$k]);
        }
        
        
        $sqldata['webtd'] = $webtd->export(null,$data['ver']);
        foreach ($sqldata['webtd'] AS &$d)
        {
            $d['lang']=$this->change_lang($d['lang']);
            $d['ver'] = 1;
            
            foreach ($d AS $k=>$v) if (!strlen(trim($v))) unset($d[$k]);
            
            if ($d['page_id']<0 && !isset($data['transcode']['level-hf'][$d['page_id']][$d['level']])) echo $d['page_id']." ".$d['level']."<br>";
            
            $d['level'] = ($d['page_id']<0)
                ? $data['transcode']['level-hf'][$d['page_id']][$d['level']]
                : ( isset($data['transcode']['level-body'][$d['level']]) ? $data['transcode']['level-body'][$d['level']] : $d['level']);
        
            if ($d['level']<0)
            {
                $d['level']=abs($d['level']);
                $d['contents_repeat']=-1;
            }
            $d['widget'] = ($d['menu_id']) ? 'menu' : 'article';
            $d['plain'] = stripslashes($d['plain']);
        }

        $sqldata['weblink'] = $weblink->export(null,$data['ver']);
        
    
        foreach ($sqldata['weblink'] AS &$d)
        {
            $d['lang']=$this->change_lang($d['lang']);          
            $d['ver'] = 1;
            
            if (!trim($d['lang_target'])) unset($d['lang_target']);
            
            foreach ($d AS $k=>$v) if (!strlen(trim($v))) unset($d[$k]);
        }
        
        
        return $sqldata;
        
    }
    
    private function isArrayFullyFilled($a)
    {
        if (!is_array($a)) return strlen($a);
        
        foreach ($a AS $v)
        {
            if (!$this->isArrayFullyFilled($v)) return false;
        }
        
        return true;
    }
    
    private function change_lang($l)
    {
        $l=trim($l);
        if (strlen($l)==1) $l=str_replace(array('i','e','r','f','d'),array('pl','en','ru','fr','de'),$l);
        if (strlen($l)==1) $l='en';
        
        return $l;
    }
}

class Zipper extends ZipArchive
{

    public function addDir($localpath, $remote = '')
    {
        if (!file_exists($localpath)) return false;

        if ($remote) {
            $this->addEmptyDir($remote);
            $remote .= '/';
        }
        foreach (scandir($localpath) AS $file) {
            if ($file == '.' || $file == '..' || $file == '.tmb' || $file == '.quarantine') continue;

            if (is_dir("$localpath/$file")) {
                $this->addDir("$localpath/$file", $remote . $file);
            } else {
                $this->addFile("$localpath/$file", $remote . $file);
            }
        }
    }

    public function extractDirTo($dest, $dir)
    {
        for ($i = 0; $i < $this->numFiles; $i++) {
            $filename = $this->getNameIndex($i);
            
            
            
            if (substr($filename, 0, strlen($dir)) != $dir) continue;
            $filename = substr($filename, strlen($dir) + 1);
            if (!strlen($filename)) continue;

            if (substr($filename, -1) == '/') {
                $filename = substr($filename, 0, strlen($filename) - 1);
                if (!file_exists("$dest/$filename")) {
                    mkdir("$dest/$filename", 0755, true);
                }

            } else {
                $localdir = dirname($filename)=='.' ? $dest : realpath($dest . '/' . dirname($filename));
                if (!file_exists($localdir)) mkdir($localdir, 0755, true);
                file_put_contents($localdir . '/' . basename($filename), $this->getFromIndex($i));
            }
        }
    
    }
}
