<?php

class Model {
    /**
     * @var Doctrine_Connection
     */
    protected $conn;
    protected $savedData, $data;
    protected $_table = null;
    protected $_key = null;
    protected $_fields;
    
    public function __construct($data=null,$new=false) {
        $this->conn = Bootstrap::$main->getConn();
        
        
        
        $fields=Bootstrap::$main->session('dbfields');
        
        $table=$this->getTable();
        if (!is_array($fields) || !isset($fields[$table]) || !count($fields[$table]) ) {
            switch (strtolower($this->conn->getDriverName())) {
                case 'pgsql':
                    $sql="SELECT column_name FROM information_schema.columns WHERE table_name='$table'";
                    $_data=$this->conn->fetchColumn($sql);
                    $fields[$table]=$_data;
                    break;
                case 'mysql':
                    $db_name=Bootstrap::$main->session('db_name');
                    $sql="SELECT column_name FROM information_schema.columns WHERE table_schema='".$db_name."' AND table_name='$table'";
                    $_data=$this->conn->fetchColumn($sql);
                    $fields[$table]=$_data;
                    break;
                
                
                
            }
            if (isset($fields[$table])) Bootstrap::$main->session('dbfields',$fields);
        }

        if (isset($fields[$table])) $this->_fields=$fields[$table];
        

        
        if ($data) {
            if (!is_array($data)) {
                $data=$this->get($data);
            }
        
            if (is_array($data)) {
                $this->load($data,$new);
            }

        }
          
        
    }
    
    public function conn($conn=null)
    {
        if ($conn) $this->conn=$conn;
        return $this->conn;
    }
    

    public function __call($name, $args)
    {
        $name = strtolower($name);

        if (substr($name, 0, 4) == 'find') {

            $sql = "SELECT * FROM " . $this->getTable();
            $one = false;
            $what = null;

            if ($name == 'find') {
                $what = $this->getKey();
            }

            if ($name == 'find_one') {
                $one = true;
                $what = $this->getKey();
            }

            if (substr($name, 0, 12) == 'find_one_by_') {
                $one = true;
                $what = substr($name, 12);
            }

            if (substr($name, 0, 8) == 'find_by_') {
                $what = substr($name, 8);
            }

            $where = array();
            foreach ($args AS $arg) {
                $where[] = "$what='$arg'";
            }

            $sql .= ' WHERE (' . implode(' OR ', $where).')';
            
            if (!empty($this->_std_where)) $sql.=' AND '.$this->_std_where; 

            $sql .= ' ORDER BY ' . $this->getKey();

            if ($what) {
                if ($one) {
                    $ret = $this->conn->fetchRow($sql);
                    if ($ret)
                        $this->load($ret);

                    return $ret;
                }

                return $this->conn->fetchAll($sql);
            }
        }

        die ('Unknown function ' . $name);
    }
    
    public function __get($name) {
        $ret=null;
        
        if (isset($this->savedData[$name])) $ret=$this->savedData[$name];
        if (isset($this->data[$name])) $ret=$this->data[$name];
        
        if (is_string($ret)) $ret=trim($ret);
        return $ret;  
    }
    
    public function __set($k,$v) {
        $this->data[$k]=$v;
    }
    
    public function getTable() {
        if ($this->_table)
            return $this->_table;
        
        return str_replace('Model','', get_class($this));
    }

    public function getKey() {
        if ($this->_key)
            return $this->_key;

        return 'id';
    }
    
    public function get($id) {
        $sql="SELECT * FROM ".$this->getTable()." WHERE ".$this->getKey()."=?";
        $row=$this->conn->fetchRow($sql,array($id));
        if ($row) $this->load($row);
        return $row;
    }
    
    public function load ($data,$new=false) {
        
        if (is_array($data)) foreach ($data AS $k=>$v) {
            if (!$new) $this->savedData[$k]=$v;
            $this->data[$k]=$v;
        }
    }
    
    public function select($where=null,$order='',$limit=0, $offset=0)
    {
        if (is_null($where)) $where=array();
        
        $sql="SELECT * FROM ".$this->getTable();
        $where_v=array();
        if (count($where))
        {
            $where_k=array();
            
            foreach ($where AS $k=>$v)
            {
                $where_v[]=$v;
                $where_k[]="$k=?";
            }
            $sql.=" WHERE ".implode(' AND ',$where_k);
        }
        
        if ($order) $sql.=" ORDER BY $order";
        
        $sql = $this->conn->modifyLimitQuery($sql,$limit,$offset);
        
        
        return $this->conn->fetchAll($sql,$where_v);
    }
    
    protected static $counterer;
    
    public function save($default_update_key=true) {
        
        $server=Bootstrap::$main->session('server');
        if (isset($server['anonymous']) && $server['anonymous']=='1') return false;
        
        $key=$this->getKey();
    
        $inserts=array();
        $values=array();
        $sets=array();
        $pyt=array();
        $setvalues=array();
        $wheres=array();
        $wherevalues=array();
        
        
        foreach ($this->data AS $k=>$v) {
            
            if (!is_array($this->_fields) || !in_array($k,$this->_fields)) continue;
            
            if (isset($this->savedData[$this->getKey()])) {
                if ($default_update_key && $k=='autor_update') {
                    $user=Bootstrap::$main->session('user');
                    $v=$user['username'];
                }
                if ($default_update_key && $k=='nd_update') {
                    $v=Bootstrap::$main->now;
                }
            }
            
            if (!strlen($v)) $v=null;
            
            $inserts[]=$k;
            $values[]=$v;
            $pyt[]='?';
            
            if (is_array($this->savedData) && (!array_key_exists($k,$this->savedData) || $this->savedData[$k] !== $v)) {
                $sets[]="$k=?";
                $setvalues[]=strlen($v)?$v:null;
            }
            
            if (is_null($v)) 
                $wheres[]="$k IS NULL";
            else {
                $wheres[]="$k=?";
                $wherevalues[]=$v;
            }
        }
        
   
        
        if (isset($this->savedData[$this->getKey()])) {
         
            if (count($sets)) {
            
                $sql="UPDATE ".$this->getTable()." SET ".implode(',',$sets)." WHERE ".$this->getKey().'=?';
                
                $setvalues[]=$this->savedData[$this->getKey()];
                $res=$this->conn->execute($sql,$setvalues);
                
                
                
                if ($res) {
                    foreach ($this->data AS $k=>$v) $this->savedData[$k]=$v;
                }
            }
            
        } else {   
            $sql="INSERT INTO ".$this->getTable()." (".implode(',',$inserts).") VALUES (".implode(',',$pyt).")";
        
        
            if (count($inserts)) {
                $res=$this->conn->execute($sql,$values);
                if ($res) {
                    $sql="SELECT ".$this->getKey()." FROM ".$this->getTable()." WHERE ".implode(' AND ',$wheres).' ORDER BY '.$this->getKey().' DESC';
                    $keyValue=$this->conn->fetchOne($sql,$wherevalues);
                    foreach ($inserts AS $i=>$k) $this->savedData[$k]=$values[$i];
                    $this->savedData[$key]=$keyValue;
                    $this->data[$key]=$keyValue;
                }
            }
            
        }
        $ret=$this->savedData;
        
        return $ret;
        
    }
    
    public function clear() {
        $this->data=null;
        $this->savedData=null;
    }
    
    public function data() {
        return $this->savedData;
    }
    
    public function remove($id) {
        
        $sql="DELETE FROM ".$this->getTable()." WHERE ".$this->getKey()."=?";
        return $this->conn->execute($sql,array($id));

    }

    public function __toString()
    {
        return get_class($this);
    }
    
    
    public function d_xml(&$data) {
            if (isset($data['d_xml'])) {
                $a=unserialize(base64_decode($data['d_xml']));
                if (is_array($a)) foreach($a AS $k=>$v) $data[$k]=$v;
            }
    }
    
    
    public function begin()
    {
        return $this->conn->beginTransaction();
    }
    
    public function commit()
    {
        return $this->conn->commit();
    }
    
    
    public function rollback()
    {
        return $this->conn->rollback();
    }
    
    
    protected function _log()
    {
        $name=str_replace('Model','',get_class($this));
        if (!$name) return;
        $name.='_model';
        
        Tools::log($name,func_get_args());
    }    
    
}
