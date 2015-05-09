<?php

class webModel extends Model {
	protected $_key = 'sid';
	
	public function __construct($data=null,$new=false) {
		$this->ver = Bootstrap::$main->session('ver')?:1;
		$this->lang = Bootstrap::$main->session('lang')?:'en';
		$this->olang = $this->lang;
		$server = Bootstrap::$main->session('server');
		$this->server = $server['id'];
		
		$user=Bootstrap::$main->session('user');
		
		if ($this->getTable()=='webtd') $this->autor = $user['username'];
		
		$this->nd_create=Bootstrap::$main->now;	
		parent::__construct($data,$new);
	}
	
	
	
	public function export($lang=null,$ver=0) {
		$sql="SELECT * FROM ".$this->getTable()." WHERE server=?";
		if ($lang) $sql.=" AND lang='$lang'";
		if ($ver) $sql.=" AND ver=$ver";
		$sql.=" ORDER BY sid";
		$data=$this->conn->fetchAll($sql,array($this->server));
		
		$webcat=new webcatModel();
		
		foreach ($data AS $i=>$rek) {
			
			if ($this->getTable()=='webtd') $data[$i]['_categories'] = implode('~',$webcat->getCats(0,$data[$i]['sid']));
			
			foreach ($rek AS $k=>$v) {
				if (in_array($k,array('sid','menu_sid','server','nd_create','nd_update','nd_ftp','proof_autor','proof_date','unproof_autor','unproof_date','unproof_counter','accesslevel','unproof_sids','unproof_comment','nd_ftp'))) unset($data[$i][$k]);
				
				if (isset($data[$i]['trash']) && $data[$i]['trash']) continue;
				
				if ($k=='plain') {
					$data[$i][$k]=preg_replace('#/*uimages/[0-9]+/[0-9]+#',UIMAGES_TOKEN,$data[$i][$k]);
					$data[$i][$k]=preg_replace('#/*ufiles/[0-9]+\-att#',UFILES_TOKEN,$data[$i][$k]);
				}
			}
		}
		
		return $data;
	}
	
	public function import($server,$data,$lang=null,$ver=0) {
		$user=Bootstrap::$main->session('user');
		$uniqueids=array();
		$classname=get_class($this);
		
		$webcat=new webcatModel();
		
		$firstsid=0;
		
		foreach ($data AS $rek) {
			$rek['server']=$server;
			if ($lang) $rek['lang']=$lang;
			if ($ver) $rek['ver']=$ver;
			
			foreach ($rek AS $k=>$v) {
				if ($k=='nd_create') $rek[$k]=Bootstrap::$main->now;
				if ($k=='nd_update') unset($rek[$k]);
			
				if ($k=='autor') $rek[$k]=$user['username'];
				if ($k=='autor_update') unset($rek[$k]);
				
				if ($k=='nd_ftp') unset($rek[$k]);
			
				if ($k=='uniqueid') {
					$sql="SELECT count(*) AS c2 FROM webtd WHERE uniqueid=?";
					$c=$this->conn->fetchOne($sql,array($v));
					if ($c) {
						$rek[$k]=$uniqueids[$v]=$this->uniqueid($this->max_sid());
					}
				}
			}
			
			if ($rek['trash']) continue;
			
			$web=new $classname($rek,true);
			$web->server=$server;
			$web->save();
			
			if (!$firstsid) $firstsid=$web->sid;
			
			
			if ($this->getTable()=='webtd' && $rek['_categories'])
			{
				foreach(explode('~',$rek['_categories']) AS $cat)
				{
					$webcat->add($server,$web->sid,$cat);
				}
			}
		}
	
		
		if (count($uniqueids)) $this->replace_uniqueids($server,$uniqueids,$ver,$firstsid);
		
		$sql="DELETE FROM webpage WHERE id IS NULL OR id<0";
		$this->conn->exec($sql);
	}
	
	public function max_sid() {
		return $this->conn->fetchOne('SELECT max(sid) FROM '.$this->getTable());
	}
	
	
	protected function checkRightRange($resource,$range,$tree='') {
		if ($range=='-') return false;
		if (!strlen($range)) return true;
		if (!strlen($resource)) return false;
	
		$range=str_replace(',',';',$range);
		$ranges=explode(";",$range);
		for ($i=0;$i<count($ranges);$i++)
		{
			$oddo=explode("-",$ranges[$i]);
			
			if ( strpos($oddo[0],"+"))
			{
	
				$root=$oddo[0]+0;
				if ($resource==$root) return true;
				$page+=0;

				if (strstr($tree,":$root:")) return true;
				else continue;
			}
		
	
			$od=$oddo[0]+0;
			if (!isset($oddo[1])) $oddo[1]=$oddo[0];
			$do=$oddo[1]+0;
			if (!$do) $do=$od;
			if ($resource>=$od && $resource<=$do) return true;
		}
		return false;	
	
	}
	

	
	
	public function remove_hard($id=null) {

		$server=Bootstrap::$main->session('server');
		if ($server['id']!=$this->server) return false;		
	
		if ($id === true)
			return $this->conn->execute('DELETE FROM ' . $this->getTable() . ' WHERE trash <> 0 AND server='.$this->server.' AND ver='.$this->ver);

		if (!$id)
			$id=$this->sid;
		
		return parent::remove($id);
	}
	
	public function untrash($id=null) {
		if (!$id) $id=$this->sid;
		
		$classname=get_class($this);
		$model=new $classname($id);
		$model->trash=0;
		
		return $model->save();
	}
	
	public function remove($id=null) {
		$server=Bootstrap::$main->session('server');
		if ($server['id']!=$this->server) return false;		
		
		if (!$id) {
			$model=&$this;
		} else {
			$classname=get_class($this);
			$model=new $classname($id);
		}
		$model->trash=time();
		return $model->save();
	}
	
	public function getTrashed()
	{
		$sql = "SELECT * FROM " . $this->getTable() . " WHERE server = ? AND ver = ? AND trash>0 ORDER BY trash DESC";
		$sql = $this->conn->modifyLimitQuery($sql,1000,0);
		
		$trash = $this->conn->fetchAll($sql, array(
		    $this->server, $this->ver
		));
	    
		foreach ($trash as $k => $v) {
		    $trash[$k]['_table'] = $this->getTable();
		}
	    
		return $trash;
	}

 
	public function save ($default_update_key=true) {
		
		$server=Bootstrap::$main->session('server');
		
		if ($server['id']!=$this->server) return false;
		
		$activity_type=$this->sid ? 'W' : 'C';
		
		
		$security=Bootstrap::$main->getConfig('security');
		$global=Bootstrap::$main->getConfig('global');
		$user=Bootstrap::$main->session('user');
				
		if (!$user['admin'] && $this->server && !$this->savedData['sid'] && isset($security['limit'][$this->getTable()]) && ($limit=$security['limit'][$this->getTable()])>0 ) {
			$count=$this->count();
			
			if ($limit2=$server[$this->getTable().'_limit']) $limit=$limit2;
			
			if ($count>=$limit) {
				$email=current(explode(',',$global['admin']));
				Bootstrap::$main->error(ERROR_ERROR,'Limit for resources has been exceeded. Please contact %s',array($email));
				return null;
			}
		}
		
		$ret=parent::save($default_update_key);
		
		if ($this->trash) $activity_type='D';
		if ($ret) Tools::activity($this->getTable(),$this->sid,$activity_type);
		
		return $ret;
	}
    
    
	public function count($server=0)
	{
		if (!$server) $server=$this->server;
		$sql="SELECT count(*) FROM ".$this->getTable()." WHERE server=".$server;
		return $this->conn->fetchOne($sql);
	}
    
	public function hasAccess()
	{
		return in_array($this->server,array_keys(Bootstrap::$main->session('servers')));
	}
	
	
	protected function _getCountItemsInRange($min,$max)
	{
		$params = array(
		    $this->server, $this->lang, $this->ver, $min, $max
		);		

		$sql = "SELECT count(*) FROM ".$this->getTable()."
				    WHERE server=? AND lang=? AND ver=?
				    AND ".$this->_subkey.">=? AND ".$this->_subkey."<=?";
				    
		return $this->conn->fetchOne($sql, $params);
	}
	
	protected function _getItemsInRange($min,$max)
	{
		$params = array(
		    $this->server, $this->lang, $this->ver, $min, $max
		);
		
		$sql = "SELECT ".$this->_subkey." FROM ".$this->getTable()."
					WHERE server=? AND lang=? AND ver=?
					AND ".$this->_subkey.">=? AND ".$this->_subkey."<=?";
		return $this->conn->fetchColumn($sql, $params);		
	}
	
	
	public function findFirstEmptyNumber($ranges)
	{
	    foreach ($ranges AS $range) {
		$min = $range[0];
		$max = $range[1];
	
		$count = $this->_getCountItemsInRange($min,$max);
	
	
		if ($count > $max - $min) continue;
	
		if ($count < 50) {

		    $ids = $this->_getItemsInRange($min,$max);
		    
		    for ($i = $min; $i <= $max; $i++) {
			if (!in_array($i, $ids)) {
			    return $i;
			}
		    }
	
		} else {
		    $mid = $min + floor(($max - $min) / 2);
		    $page = $this->findFirstEmptyNumber(array(
			array($min, $mid)
		    ));
		    if (!is_null($page)) return $page;
		    $page = $this->findFirstEmptyNumber(array(
			array($mid + 1, $max)
		    ));
		    
		    if (!is_null($page)) return $page;
		}
	
	    }
	
	    return null;
	}	
	
	
}
