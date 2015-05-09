<?php

class rightModel extends Model {
    protected $_table = 'rights';
    protected $_key = 'sid';
    
    
    public function getRight($server,$username)
    {
        $sql="SELECT * FROM rights WHERE server=? AND username=?";
        
        $data=$this->conn->fetchRow($sql,array($server,$username));
        if ($data) $this->load($data);
        return $data;
    }
    
}
