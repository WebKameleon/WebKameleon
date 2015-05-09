<?php

class webpagetrashModel extends Model {
    
    
    public function getUnTrashed($server,$lang,$ver)
    {
        $sql="SELECT * FROM webpagetrash WHERE server=? AND ver=? AND lang=? AND status='N'";
        return $this->conn->fetchAll($sql, array($server, $ver, $lang));
        
    }
    
    public function markAsTrashed($id,$status='D')
    {
        $sql="UPDATE webpagetrash SET status=?, nd_complete=? WHERE id=?";
        $this->conn->execute($sql,array($status,Bootstrap::$main->now,$id));
    }
}
