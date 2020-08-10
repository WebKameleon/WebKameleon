<?php

class webcatModel extends Model {
    
    public function getCats($server,$sid=0) {
        
        
        if ($server)
        {
            $sql="SELECT category FROM webcat WHERE server=? GROUP BY category ORDER BY category";
            $result=$this->conn->fetchAll($sql,array($server));
        
            if (!$sid) return $result;
        
        
            foreach($result AS $k=>$v)
            {
                $result[$k]['checked'] = $this->hasCat($server,$sid,$v['category']);
            }
        
            return $result;
        }
        else
        {
            $sql="SELECT category FROM webcat WHERE tdsid=? ORDER BY category";
            $result=$this->conn->fetchAll($sql,array($sid));
            
            $data=array();
            foreach($result AS $v) $data[]=$v['category'];
            return $data;            
        }
    }

    
    protected function cat($cat)
    {
        $cat=trim($cat);
        while(strlen($cat)>32) $cat=mb_substr($cat,0,mb_strlen($cat,'utf8')-1,'utf8');
        return $cat;
    }
    
    public function hasCat($server,$sid,$cat)
    {
        $cat=$this->cat($cat);
        
        $sql="SELECT 1 FROM webcat WHERE server=? AND tdsid=? AND category=?";
        return $this->conn->fetchOne($sql,array($server,$sid,$cat));
    }
    
    
    public function cats($sid)
    {
        $sql="SELECT * FROM webcat WHERE tdsid=?";
        return $this->conn->fetchAll($sql,array($sid));
    }
    
    
    public function add($server,$sid,$cat)
    {
        $cat=$this->cat($cat);
        
        $model = new self;
        
        $model->server=$server;
        $model->tdsid=$sid;
        $model->category=$cat;
        $model->save();
        
        return $model;
    }
    
    public function del ($server,$sid,$cat)
    {
        $cat=$this->cat($cat);        
        $sql="DELETE FROM webcat WHERE server=? AND tdsid=? AND category=?";
                
        return $this->conn->exec($sql,array($server,$sid,$cat));
    }
    
    
    public function purge($server)
    {
        $sql="DELETE FROM webcat WHERE server=? AND tdsid NOT IN (SELECT sid FROM webtd WHERE server=? AND sid=tdsid)";
        
        return $this->conn->exec($sql,array($server,$server));
    }

    public function catsOnPage($server,$lang,$ver,$lang,$page) {
        
        $sql="SELECT category FROM webcat WHERE server=? AND tdsid IN";
        $sql.=" (SELECT sid FROM webtd WHERE server=? AND lang=? AND ver=? AND page_id=? AND trash=0)";
        
        return $this->conn->fetchColumn($sql,array($server,$server,$lang,$ver,$page));
    }
}
