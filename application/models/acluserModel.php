<?php

class acluserModel extends Model {
    protected $_table = 'acl_users';

    
    
    public function __construct($data=null,$new=false) {
        $server = Bootstrap::$main->session('server');
        $this->server = $server['id'];
        parent::__construct($data,$new);
    }    
    
    
    
    public function find_by_username($username)
    {
        return $this->conn->fetchRow("SELECT * FROM " . $this->getTable() . " WHERE  server = ? AND username=? ", array(
            $this->server, $username
        ));        
    }
    
    public function getall()
    {
        return $this->conn->fetchAll("SELECT * FROM " . $this->getTable() . " WHERE  server = ? ORDER BY username", array(
            $this->server
        )); 
        
    }
    
    
    public function remove($username)
    {
        $sql="DELETE FROM acl_users WHERE server=? AND username=?";
        $this->conn->exec($sql,array($this->server,$username));
        $sql="DELETE FROM acl_pages WHERE server=? AND username=?";
        $this->conn->exec($sql,array($this->server,$username));
        
    }
}
