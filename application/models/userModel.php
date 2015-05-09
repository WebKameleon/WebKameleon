<?php

class userModel extends Model {
    
    
    protected $_table = 'passwd';
    protected $_key = 'username';

    public function servers($trash = false)
    {
        $username = $this->username;

        if ($username=='anonymous') return Bootstrap::$main->session('servers');
        
        $sql = "SELECT * FROM rights
                LEFT JOIN servers ON servers.id" . ($trash ? '*-1' : '') . "=rights.server
                WHERE rights.username='$username'";

        $servers = $this->conn->fetchAll($sql);
        if (!$servers)
            return array();

        $tmp = array();
        foreach ($servers AS $server) {
            $sql = "SELECT max(id) FROM login WHERE username=? AND server=?";
            $id = $this->conn->fetchOne($sql, array($username,$server['id']));
            if (!$id) {
                $id = $server['id'];
                $server['login_id'] = 0;
            } else {
                //$server['login_id'] = $id;
            }
            
            $tmp[$id] = $server;
        }

        krsort($tmp);

        $servers = array();
        foreach ($tmp AS $server) {
            if ($server['id']) {
                $this->d_xml($server);
                $server['paths'] = Tools::getPaths($server);
                $servers[$server['id']] = $server;
            }
        }

        return $servers;
    }
    
    
    public function addUser($email,$fullname,$lang='',$skin='kameleon') {
        $email=trim(strtolower($email));
        $username=md5($email);
        
        

        if (!$lang) $lang=Bootstrap::$main->lang();


               
        if ($this->get($username)) {
            if (trim($fullname)) $this->fullname=$fullname;
            $this->skin=$skin;
            $this->ulang=$lang;
            $this->save();
        } else {
            $ile=$this->conn->fetchOne("SELECT count(*) FROM passwd");
            
            
            $data=array('email'=>$email,'username'=>$username,'fullname'=>$fullname,'ulang'=>$lang,'skin'=>$skin,'admin'=>$ile?0:1,'password'=>'!');
            
            $this->load($data,true);
            $this->nlicense_agreement_date=Bootstrap::$main->now;
            $this->save();
        }
        
        return $this->get($username);
    }
    
    public function friends($me,$starting)
    {
        if (!$starting) return;
        $starting=addslashes($starting);
        $sql="SELECT * FROM passwd WHERE (email LIKE '$starting%' OR fullname LIKE '$starting%')
                AND username<>'$me'
                AND username IN (
                    SELECT username FROM rights WHERE server IN (
                        SELECT server FROM rights WHERE username='$me' 
                    )
                )
                ORDER BY fullname";
        return $this->conn->fetchAll($sql);
    }
    
    public function getByEmail($email)
    {
        return $this->find_one_by_email(trim(strtolower($email)));
    }
    
    
    public function getAllLoggedIn($server=0,$exclude='')
    {
        if ($server)
        {
            $sql="SELECT * FROM passwd WHERE lastserver=$server";
            if ($exclude) $sql.=" AND username<>'$exclude'";
            
            $all=$this->conn->fetchAll($sql);
            
            $ret=array();
            
            foreach($all AS $person)
            {
                $sql="SELECT * FROM login WHERE username=? AND server=? ORDER BY id DESC";
                $sql = $this->conn->modifyLimitQuery($sql, 1);
                $rek=$this->conn->fetchRow($sql,array($person['username'],$server));
                
                $delta=time()-$rek['tout'];
                if ($delta>LOGIN_IDLE_TIME_MINS*60) continue;
                while(isset($ret[$delta])) $delta++;
                
                $ret[$delta]=$person;
            }
            ksort($ret);
        }
        else
        {
            $sql="SELECT count(distinct(username)) FROM login WHERE tout>".(Bootstrap::$main->now-LOGIN_IDLE_TIME_MINS*60);
            if ($exclude) $sql.=" AND username<>'$exclude'";
            $ret=$this->conn->fetchOne($sql);
        }
        
        return $ret;
    }
    
    
    protected function q2where($q)
    {
        $q=trim($q);
        if (!$q) return '';
        
        $where=array();
        foreach(explode(' ',$q) AS $qq)
        {
            if (!$qq) continue;
            
            if ($qq[0]==':')
            {
                switch (substr($qq,1))
                {
             
                    case 'gplus':
                        $where[]="link<>''";
                        break;
                    
                    case 'admin':
                        $where[]='admin=1';
                        break;
                    
                    case 'now':
                        $where[]='username IN (SELECT username FROM login WHERE tout>'.(Bootstrap::$main->now - LOGIN_IDLE_TIME_MINS * 60 ).')';
                        break;

                    case 'creator':
                        $where[]='username IN (SELECT creator FROM servers WHERE creator=username)';
                        break;

                    default:
                        if (strlen($qq)==3) $where[]="ulang='".substr($qq,1)."'";
                        break;
                        
                    
                }
            }
            else $where[]="(username='$qq' OR fullname LIKE '%$qq%' OR email LIKE '%$qq%' OR from_campaign='$qq')";
        }
        
        return 'WHERE '.implode(' AND ',$where);
    }
    
    public function count($q)
    {
        $sql="SELECT count(*) FROM passwd ".$this->q2where($q);
        return $this->conn->fetchOne($sql);
    }
    
    public function users($q,$order='fullname',$limit=0, $offset=0)
    {
     
        $sql="SELECT passwd.* FROM passwd ".$this->q2where($q).' ORDER BY '.$order;
        $sql = $this->conn->modifyLimitQuery($sql,$limit,$offset);
        
        $all=$this->conn->fetchAll($sql);
        
        foreach ($all AS &$user)
        {
            $user['last_login'] = $this->conn->fetchOne("SELECT max(tout) FROM login WHERE username='".$user['username']."'");
        }
        
        return $all;        
    }
    
    public function getCurrent()
    {
        $u=Bootstrap::$main->session('user');
        $this->get($u['username']);
        return $this;
    }
    
    
    public function hasAccess($scope)
    {
        $scopes=json_decode($this->access_token,true);
        
        $ret=isset($scopes[$scope]) && $scopes[$scope];

        return $ret;
    }
    
    
    public function login_time($username=null)
    {
        if (is_null($username)) $username=$this->username;
        
        $sql="SELECT sum(login_time) FROM login_arch WHERE username='$username'";
        $arch=$this->conn->fetchOne($sql);

        $sql="SELECT sum(tout-tin) FROM login WHERE username='$username'";
        $login=$this->conn->fetchOne($sql);
        
        return $arch+$login;
    }
    
    public function logins($username=null)
    {
        if (is_null($username)) $username=$this->username;
        
        $sql="SELECT count(*) FROM login_arch WHERE username='$username' AND login_time>0";
        $arch=$this->conn->fetchOne($sql);

        $sql="SELECT count(*) FROM login WHERE username='$username'  AND tout<>tin";
        $login=$this->conn->fetchOne($sql);
        
        return $arch+$login;
    }
    
    public function getlogins($username=null)
    {
        if (is_null($username)) $username=$this->username;

        $sql="SELECT * FROM login WHERE username='$username' AND tout<>tin ORDER BY id DESC";
        $logins=$this->conn->fetchAll($sql);
        
        $sql="SELECT * FROM login_arch WHERE username='$username' AND tout<>tin ORDER BY id DESC";
        $sql = $this->conn->modifyLimitQuery($sql, 50);
        $logins_arch=$this->conn->fetchAll($sql);        
        
        return array_merge($logins,$logins_arch);
    }
    
    public function isLoggedIn($username=null)
    {
        if (is_null($username)) $username=$this->username;
        
        $sql="SELECT server FROM login WHERE username='$username' AND tout>".(Bootstrap::$main->now-LOGIN_IDLE_TIME_MINS*60).' ORDER BY tout DESC';
        
        return 0+$this->conn->fetchOne($sql);
    }
    
    
    public function save($default_update_key=true)
    {
        if (!$this->username)
        {
            $this->_log($_REQUEST,$_SERVER);
        }
        
        return parent::save($default_update_key);
    }
    
    
    function remove($id)
    {
        $this->_log('Remove: '.$id. '('.$this->email.')');
        
        $sql="DELETE FROM rights WHERE username=?";
        $this->conn->execute($sql,array($id));

        $sql="DELETE FROM login WHERE username=?";
        $this->conn->execute($sql,array($id));
        
        $sql="DELETE FROM login_arch WHERE username=?";
        $this->conn->execute($sql,array($id));
        
        
        return parent::remove($id);
    }

    function get_current()
    {
        $user=Bootstrap::$main->session('user');
        return $this->get($user['username']);
    }

}
