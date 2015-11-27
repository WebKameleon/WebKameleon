<?php

class ftpModel extends Model {
    
    public function getLast($server,$list_of__comma_sep_ids='',$size=15) {
        $sql="SELECT * FROM ftp WHERE server=? ORDER BY id DESC";
        $sql = $this->conn->modifyLimitQuery($sql,$size);
        $result=$this->conn->fetchAll($sql,array($server))?:[];
        
        $sql="SELECT * FROM ftp WHERE server=? AND t_begin>0 AND t_end IS NULL ORDER BY id DESC";
        $result2=$this->conn->fetchAll($sql,array($server))?:[];
        
        
        foreach ($result2 AS $r2) {
            $id=$r2['id'];
            $repeat=false;
            foreach ($result AS $r) {
                if ($r['id']===$id) {
                    $repeat=true;
                    break;
                }
            }
            if (!$repeat) $result[]=$r2;
        }
        
        if ($list_of__comma_sep_ids) {
            $ids=explode(',',$list_of__comma_sep_ids);
            $sql="SELECT * FROM ftp WHERE server=? AND id IN (?) ORDER BY id DESC";
            $result2=$this->conn->fetchAll($sql,array($server,$list_of__comma_sep_ids));
            if (is_array($result2)) foreach ($result2 AS $ftp) {
                if (!in_array($ftp,$result)) $result[]=$ftp;
            }
            rsort($result);
            
            $ftplog=new ftplogModel();
            foreach ($result AS $i=>$ftp) {
                if (in_array($ftp['id'],$ids)) {
                    $result[$i]['log']=$ftplog->find_by_ftp_id($ftp['id']);
                }
            }
        }
        
        
        
        return $result;
    }
    
    public function getUnfinished($server)
    {
        $sql="SELECT * FROM ftp WHERE server=? AND t_end IS NULL ORDER BY id DESC";        
        return $this->conn->fetchAll($sql,array($server));
    }
}
