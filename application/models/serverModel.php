<?php

class serverModel extends Model
{
    protected $_table = 'servers';

    public function __construct($data=null,$new=false) {
            $user=Bootstrap::$main->session('user');
            
            $this->creator=$user['username'];
            $this->header=-5;
            $this->footer=-6;
            $this->ver=1;
            $this->lang=$user['ulang']?:Bootstrap::$main->lang();

            parent::__construct($data,$new);
    }    
    
    public function getUsers($id = 0)
    {
        if (!$id) $id = $this->id;
        if (!$id) return;

        $sql = "SELECT * FROM rights
                LEFT JOIN passwd ON passwd.username = rights.username
                WHERE server = ?
                ORDER BY fullname, email";

        $res = $this->conn->fetchAll($sql, array($id));

        foreach ($res AS $i => $u) {
            $res[$i]['is_owner'] = $u['owner'] ? 1 : 0;
            $res[$i]['can_ftp']  = $u['ftp']   ? 1 : 0;
            $res[$i]['can_edit'] = $u['pages'] ? 0 : 1;
            $res[$i]['can_view'] = 1;
        }

        return $res;
    }

    public function addUser($username, $owner = 0, $edit = 0, $ftp = 0, $id = 0)
    {
        if (!$id) $id = $this->id;
        if (!$id) return;

        $rights = new rightModel();

        if ($owner)
            $ftp = 1;

        if ($ftp)
            $edit = 1;

        $rights->getRight($id, $username);
        $rights->username = $username;
        $rights->server = $id;
        $rights->owner = $owner ? 1 : 0;
        $rights->template = $owner ? 1 : 0;
        $rights->pages = $edit ? '' : '-';
        $rights->menus = $edit ? '' : '-';
        $rights->ftp = $ftp ? 1 : 0;

        $rights->save();
    }

    public function deleteUser($username, $id = 0)
    {
        if (!$id) $id = $this->id;
        if (!$id) return;

        $this->conn->exec('DELETE FROM rights WHERE server = ? AND username = ? AND server NOT IN (SELECT id FROM servers WHERE creator=?)',
            array($id, $username,$username)
        );
    }

    public function changeUser($username, $owner = 0, $edit = 0, $ftp = 0, $id = 0)
    {
        $this->deleteUser($username, $id);
        $this->addUser($username, $owner, $edit, $ftp, $id);
    }
    
    public function option($option,$set=null) {
        $s=Bootstrap::$main->session('server');
        if (!isset($this->data['id'])) $this->load($this->get($s['id']));

        
        $options=unserialize(base64_decode($this->d_xml));
        
        
        if (!is_null($set)) {
            $options[$option]=$set;
            $this->d_xml = base64_encode(serialize($options));
            $this->save();
            $s['d_xml']=$this->d_xml;
            Bootstrap::$main->session('server',$s);
        }
        return isset($options[$option])?$options[$option]:'';
    }
    
    
    public function isEmpty($id=null) {
        if (!$id) $id=$this->id;
        
        
        if ($this->conn->fetchOne("SELECT count(*) FROM webpage WHERE server=?",array($id))>0) return false;
        if ($this->conn->fetchOne("SELECT count(*) FROM webtd WHERE server=?",array($id))>0) return false;
        if ($this->conn->fetchOne("SELECT count(*) FROM weblink WHERE server=?",array($id))>0) return false;
        return true;
    }
    
    
    public function setDefaultValues() {
        $config=Bootstrap::$main->getConfig();
        
        $nazwa=$this->nazwa;
        
        foreach($config['default'] AS $k=>$v) {
            if (substr($k,0,4)=='ftp_') {
                $this->$k = GN_Smekta::smektuj($v,get_defined_vars());
            }
        }
        
        if (isset($config['default']['http_url'])) {
            $this->http_url = GN_Smekta::smektuj($config['default']['http_url'],get_defined_vars());
        }        
    }
    
    public function add($name,$copyofarray=null) {
                
        if (!is_writable(MEDIA_PATH)) {
            Bootstrap::$main->error(ERROR_ERROR,'Directory %s is not writable',array(MEDIA_PATH));
            return;
        }
        
        $nazwa_long=$name=trim($name);
        $nazwa_long=str_replace(array('<','>'),array('',''),$nazwa_long);
        $nazwa=Bootstrap::$main->kameleon->str_to_url(trim(str_replace('/','-',$name)), -1, true);
        
        if (!strlen(trim($nazwa))) return false;
        
        if ($this->conn->fetchOne("SELECT count(*) FROM servers WHERE nazwa=?",array($nazwa))>0) {
            Bootstrap::$main->error(ERROR_ERROR,'Service with submitted name (%s) exists',$nazwa);
            return false;
        }
        
        $session=Bootstrap::$main->session();
        $config=Bootstrap::$main->getConfig();
        
        
        if (!$session['user']['admin']) {
            $limit=$config['security']['server']['limit'];
            if ($this->conn->fetchOne("SELECT count(*) FROM servers WHERE creator=? AND nd_last_payment IS NULL",array($session['user']['username']))>=$limit) {
                Bootstrap::$main->error(ERROR_ERROR,'Free website limit exceeded %d, please pay for one of those you use',$limit,'wizard/trash');
                return false;
            }
            
        }
        
        
        $this->nazwa=$nazwa;
        $this->nazwa_long=$name;
        
        $this->setDefaultValues();
        
        if ($config['security']['server_expires_in_days'] && !$session['user']['admin'])
        {
            $creator_expire = $this->creator()->nd_last_expire;
            $this->nd_expire=$creator_expire?:time()+$config['security']['server_expires_in_days']*24*3600;
        }
        
        
        if ($this->save()) {
            $this->addUser($this->creator,1,1,1);
            
            if ($copyofarray)
            {
                Tools::cp_r(MEDIA_PATH.'/'.$copyofarray['nazwa'], MEDIA_PATH.'/'.$this->nazwa);
                $this->d_xml = $copyofarray['d_xml'];
                $this->szablon = ($copyofarray['nazwa']==$copyofarray['szablon']) ? $this->nazwa : $copyofarray['szablon']; 
                
                $this->save();
            }
            else
            {
                mkdir (MEDIA_PATH.'/'.$this->nazwa.'/images/'.$this->ver,0755,true);
                mkdir (MEDIA_PATH.'/'.$this->nazwa.'/files',0755,true);
                mkdir (MEDIA_PATH.'/'.$this->nazwa.'/include/'.$this->ver,0755,true);            
            }
            
        }
        return $this;
    }

    public function trash($id = null)
    {
        if (!$id) $id = $this->id;
        if (!$id) return;
        
        Tools::activity('server',$id,'T');

        $this->conn->execute('UPDATE servers SET id = id * -1 WHERE id = ?', array(
            $id
        ));

        if ($id>0)
        {
            $this->conn->execute('UPDATE servers SET nd_trash=? WHERE id = ?', array(
                Bootstrap::$main->now, -1*$id
            ));
        }
        else
        {
            $this->conn->execute('UPDATE servers SET nd_trash=null WHERE id = ?', array(
                -1*$id
            ));

        }
    }
    

    
    public function creator()
    {
        return new userModel($this->creator);
    }
    
    public function save()
    {
        
        if (!trim($this->nazwa)) return false;
        
        $this->ftp_dir=str_replace(array('<','>','|','&',' ',';','{','}','[',']','!','?','*','$'),'',$this->ftp_dir);
        
        
        
        if ($this->ftp_server) {
            $sql="SELECT count(*) FROM servers WHERE id<>? AND ftp_server=? AND ftp_user=? AND ftp_dir=?";
            if ($this->conn->fetchOne($sql,array($this->id,$this->ftp_server,$this->ftp_user,$this->ftp_dir)) > 0 ) {
                Bootstrap::$main->error(ERROR_ERROR,'Directory ftp://%s/%s already exists',array($this->ftp_server,$this->ftp_dir));
                return false;
            }
        

            if ($this->ftp_dir && strstr($this->ftp_dir,'..') ) {
                Bootstrap::$main->error(ERROR_ERROR,'Directory contains illegal substring: ".."');
                return false;               
            }
            
            
            
        
        }
        
        
        
        if (isset($this->savedData['ftp_server']) && $this->savedData['ftp_server'] && $this->savedData['ftp_user'] && $this->savedData['ftp_pass'] )
        {
            if ($this->savedData['ftp_server']!=$this->data['ftp_server']
                || $this->savedData['ftp_user']!=$this->data['ftp_user']
                || $this->savedData['ftp_pass']!=$this->data['ftp_pass']
                || $this->savedData['ftp_dir']!=$this->data['ftp_dir']
                )
            {
                $garbage=new ftpgarbageModel(array(
                    'server'=>$this->savedData['ftp_server'],
                    'username'=>$this->savedData['ftp_user'],
                    'pass'=>$this->savedData['ftp_pass'],
                    'dir'=>$this->savedData['ftp_dir'],
                    'passive'=>$this->savedData['ftp_pasive'],
                    'nd_create'=>Bootstrap::$main->now
                ));
                $garbage->save();
            }
        }
        
        if (isset($this->savedData['nd_last_payment']) && $this->savedData['nd_last_payment'] != $this->data['nd_last_payment'])
        {
            $creator=$this->creator();
            $creator->nd_last_expire=null;
            $creator->save();            
        }
        
        
        return parent::save();
    }
    
    public function remove($id) {

        $s=new self($id);
        $garbage=new ftpgarbageModel(array(
            'server'=>$s->ftp_server,
            'username'=>$s->ftp_user,
            'pass'=>$s->ftp_pass,
            'dir'=>$s->ftp_dir,
            'passive'=>$s->ftp_pasive,
            'nd_create'=>Bootstrap::$main->now
        ));
        
        if (!$s->nd_last_payment)
        {
            $creator=$s->creator();
            $creator->nd_last_expire=$s->nd_expire;
            $creator->save();
        }

        
        $this->begin();
        foreach (array(
             'rights' => '',
             'ftp' => '',
             'webpage' => '',
             'webpagetrash' => '',
             'webmedia' => '',
             'weblink' => '',
             'webtd' => '',
             'webfile' => 'wf_server',
             'webfav' => 'wf_server',
             'servers' => 'abs(id)'
         ) AS $table => $key) {
            if (!$key) $key = 'server';
            $sql = "DELETE FROM $table WHERE $key=?";
            $this->conn->execute($sql, array(abs($id)));
        }
        
        if ($s->ftp_server) $garbage->save();
        
        Tools::activity('server',$id,'D');

        //$this->conn->rollback();
        return $this->commit() ? 1 : 0;
    }
    
    
    public function getAll()
    {
        $sql = "SELECT * FROM servers
                LEFT JOIN passwd ON passwd.username = creator
                ORDER BY nd_expire";

        $res = $this->conn->fetchAll($sql);        
    
        return $res;
    }
    

    public function getForUser($creator)
    {
        $sql = "SELECT * FROM servers 
                WHERE creator = '$creator'
                ORDER BY nd_expire";

        $res = $this->conn->fetchAll($sql);        
    
        return $res;
    }

    
    
    public function templates($lang='')
    {
        $user=new userModel();
        $user->getCurrent();
        
        $and=$lang?" AND (servers.lang='$lang' OR servers.lang='en')":'';
        
        $sql = "SELECT * FROM servers
                LEFT JOIN passwd ON passwd.username = creator
                WHERE ((social_template<>'' $and) OR id IN (SELECT server FROM rights WHERE username='".$user->username."') )
                AND (nd_expire>".Bootstrap::$main->now." OR nd_expire=0 OR nd_expire IS NULL)
                ORDER BY nazwa";

        $res = $this->conn->fetchAll($sql);        
        $res2=array();
        foreach ($res AS $template)
        {
            unset($template['access_token']);
            $res2[$template['nazwa']]=$template;
        }
    
        return $res2;  
    }
    


    protected function qu2where($q,$u)
    {
        $q=trim($q);
        if (!$q && !$u) return '';
        
        $where=array();
        if ($q) foreach(explode(' ',$q) AS $qq)
        {
            if (!$qq) continue;
            
            if ($qq[0]==':')
            {
                switch (substr($qq,1))
                {
                    case 'user':
                        $where[]='nd_expire>0';
                        break;

                    case 'admin':
                        $where[]='(nd_expire=0 OR nd_expire IS NULL)';
                        break;
                    
                    case 'paid':
                        $where[]='nd_last_payment>0';
                        break;
          
                    case 'template':
                        $where[]="social_template<>''";
                        break;

                    case 'trash':
                        $where[]="id<0";
                        break;
                    
                }
            }
            else $where[]="(nazwa LIKE '%$qq%' OR nazwa_long LIKE '%$qq%' OR from_social_template='$qq')";
        }
        
        if ($u) $where[]="id IN (SELECT server FROM rights WHERE username='$u')";
        
        
        return 'WHERE '.implode(' AND ',$where);
    }
    
    public function count($q,$u)
    {
        $sql="SELECT count(*) FROM servers ".$this->qu2where($q,$u);
        return $this->conn->fetchOne($sql);
    }
    
    public function servers($q,$u,$order='nazwa',$limit=0, $offset=0)
    {
     
        $sql="SELECT * FROM servers ".$this->qu2where($q,$u).' ORDER BY '.$order;
        $sql = $this->conn->modifyLimitQuery($sql,$limit,$offset);
        
        return $this->conn->fetchAll($sql);        
    }    
    
    
    public function from_social_template_count($social_template)
    {
        $sql="SELECT count(*) FROM servers WHERE from_social_template=?";
        
        return $this->conn->fetchOne($sql,array($social_template));
    }
    
    
    public function login_time($id=null)
    {
        if (is_null($id)) $id=$this->id;
        
        $sql="SELECT sum(login_time) FROM login_arch WHERE server=$id";
        $arch=$this->conn->fetchOne($sql);

        $sql="SELECT sum(tout-tin) FROM login WHERE server=$id";
        $login=$this->conn->fetchOne($sql);
        
        return $arch+$login;
    }    
    
}
