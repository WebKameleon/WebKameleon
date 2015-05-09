<?php

class webmediaModel extends Model {
    
    protected $_std_where;
    
    
    public function __construct($data=null,$new=false) {
        $server = Bootstrap::$main->session('server');
        $this->_std_where = 'server = '. $server['id'];
        
        parent::__construct($data,$new);
    }

    public function getOwner($target) {
        $found=$this->find_one_by_target($target);
        if (!$found) {
            $user=Bootstrap::$main->session('user');
            $this->target=$target;
            $this->username=$user['username'];
            $server=Bootstrap::$main->session('server');
            $this->server=$server['id'];
            $this->save();
            return $user['username'];
        }
        
        return $found['username'];
        
    }
    
    
    public function getAll() {
        return $this->conn->fetchAll("SELECT * FROM webmedia WHERE ".$this->_std_where);
    }
    
}
