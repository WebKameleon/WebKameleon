<?php

class observersentModel extends Model {
    
    public function getSent($email,$event)
    {
        $sql="SELECT * FROM observersent WHERE event=? AND email=?";
        
        return $this->conn->fetchAll($sql,array($event,$email));
    }
    
    public function removeAllForEvent($event) {
        $sql="DELETE FROM observersent WHERE event=?";
        
        return $this->conn->exec($sql,array($event));
    }
    
}
