<?php

class aclpageModel extends Model {
    protected $_table = 'acl_pages';

    
    
    public function __construct($data=null,$new=false) {
        $server = Bootstrap::$main->session('server');
        $this->server = $server['id'];
        parent::__construct($data,$new);
    }    
    
    
    public function checkUser($username,$page) {
        
        if (!$page) return false;
        
        $res = $this->conn->fetchRow("SELECT * FROM " . $this->getTable() . " WHERE server=? AND username=? AND page=?",array($this->server,$username,$page));
        if ($res) return $res;
        
        $webpage=new webpageModel();
        $webpage->getOne($page);
        
        return $this->checkUser($username,$webpage->prev);
        
    }
    
    public function getAuthUsers($page)
    {
        $wynik=array();
        $pages=$this->getUsers($page);
        
        foreach($pages AS $k=>$p)
        {
            if ($p['ok']) $wynik[$k]=$p;
        }
        
        if (!count($wynik)) return false;
        return $wynik;
    }
    
    protected function getUsers($page) {
        if (!$page) return array();
        
        $webpage=new webpageModel();
        $webpage->getOne($page);
        
        $parent=$this->getUsers($webpage->prev);
        
        $sql="SELECT * FROM acl_pages LEFT JOIN acl_users ON acl_users.username=acl_pages.username AND acl_users.server=acl_pages.server
            WHERE acl_pages.server=? AND acl_pages.page=?";
        
        $res=$this->conn->fetchAll($sql,array($this->server,$page));
        
        $wynik=$parent;
        foreach($res AS $rek)
        {
            $wynik[$rek['username']]=$rek;
        }
        
        return $wynik;
    }
}
