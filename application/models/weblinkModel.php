<?php

class weblinkModel extends webModel
{
    protected $_key = 'sid';
    protected $_subkey='menu_id';

    /**
     * @param int $menu_id
     * @return array
     */
    public function getAll($menu_id=0,$mode=-1,$type=-1,$lang='',$ver=0,$server=0) {
    
	if ($mode==-1) $mode=Bootstrap::$main->session('editmode');
    
	$hidden = ($mode<=PAGE_MODE_PREVIEW) ? ' AND (hidden=0 OR hidden IS NULL)':'';
	
	$menu_id+=0;
	$type+=0;
	
	if (!$lang) $lang=$this->lang;
	if (!$ver) $ver=$this->ver;
	if (!$server) $server=$this->server;
	
	
	$sql="SELECT * FROM " . $this->getTable() . " WHERE server = ? AND lang = ? AND ver = ? AND trash=0 $hidden";
	if ($menu_id) $sql.=" AND menu_id = $menu_id";
	if ($type!=-1) $sql.=" AND type = $type";
	
        return $this->conn->fetchAll($sql." ORDER BY menu_id,pri", array($server, $lang, $ver));
    }

    /**
     * @return array
     */
    public function getMenuList()
    {
        return $this->conn->fetchAll("SELECT DISTINCT(menu_id), name FROM " . $this->getTable() . " WHERE server = ? AND lang = ? AND ver = ? AND trash=0 ORDER BY menu_id", array(
            $this->server, $this->lang, $this->ver
        ));
    }

    /**
     * @param int $menu_id
     * @param string $name
     * @return int
     */
    public function menu_change_name($menu_id, $name)
    {
        return $this->conn->exec("UPDATE " . $this->getTable() . " SET name = ? WHERE menu_id = ? AND server = ? AND lang = ? AND ver = ?", array(
            trim($name), $menu_id, $this->server, $this->lang, $this->ver
        ));
    }

    /**
     * @param int $menu_id
     * @param string $name
     * @return array
     */
    public function add_link($menu_id, $name, $alt, $page_target=null)
    {
		$type=null;
        $links = $this->getAll($menu_id);
        if (!empty($links)) {
            $lastLink = end($links);
            $name = $lastLink['name'];
			$type = $lastLink['type'];
            $pri = $lastLink['pri'] + 1;
			$d_xml=$lastLink['d_xml'];
        } else {
            $pri = 1;
        }
	
	
        $link = new weblinkModel;
        $link->menu_id = $menu_id;
        $link->pri = $pri;
        $link->name = $name;
        $link->alt = $alt;
		$link->d_xml = $d_xml;
		$link->page_target = $page_target;
	
		if ($type) $link->type = $type;
	
	
        return $link->save();
    }

    /**
     * @param int $menu_id
     * @return int
     */
    public function remove_links($menu_id)
    {
	if (!$this->checkRight($menu_id)) return false;
	
        return $this->conn->exec("UPDATE " . $this->getTable() . " SET trash=".time()." WHERE menu_id = ? AND server = ? AND lang = ? AND ver = ?", array(
            $menu_id, $this->server, $this->lang, $this->ver
        ));
    }

    /**
     * @return int
     */
    
    
    protected function _getCountItemsInRange($min,$max)
    {
	    $params = array(
		$this->server, $this->lang, $this->ver, $min, $max
	    );		

	    $sql = "SELECT count(distinct(".$this->_subkey.")) FROM ".$this->getTable()."
				WHERE server=? AND lang=? AND ver=?
				AND ".$this->_subkey.">=? AND ".$this->_subkey."<=?";
				
	    
	    $c1=$this->conn->fetchOne($sql, $params);
	    
	    $params = array(
		$this->server, $this->lang, $this->ver, $min, $max,
		$this->server, $this->lang, $this->ver, $min, $max
	    );		
	    
	    
	    
	    $sql="SELECT count(distinct(menu_id)) FROM webtd WHERE server = ? AND lang = ? AND ver = ? AND menu_id>=? AND menu_id<=?
		    AND menu_id NOT IN (SELECT menu_id FROM weblink WHERE server = ? AND lang = ? AND ver = ? AND menu_id>=? AND menu_id<=?)";
	    
	    
	    
	    $c2=$this->conn->fetchOne($sql, $params);
	    
	    
	    
	    return $c1+$c2;
    }
    
    
    protected function _getItemsInRange($min,$max)
    {
	    $params = array(
		$this->server, $this->lang, $this->ver, $min, $max,
		$this->server, $this->lang, $this->ver, $min, $max
	    );
	    
	    $sql = "SELECT ".$this->_subkey." FROM ".$this->getTable()."
				    WHERE server=? AND lang=? AND ver=?
				    AND ".$this->_subkey.">=? AND ".$this->_subkey."<=?
				    
		    UNION
		    SELECT menu_id  FROM webtd WHERE server = ? AND lang = ? AND ver = ? AND menu_id>=? AND menu_id<=?
		    ";
	    
	    
	    return $this->conn->fetchColumn($sql, $params);		
    }    
    
    
    
    public function get_new_menu_id()
    {
	$server = Bootstrap::$main->session('server');
	$servers = Bootstrap::$main->session('servers');
	
	$server['menus'] = $servers[$server['id']]['menus'];

	
        if ($server['menus']) {
            $ranges=Tools::ranges($server['menus']); 
        } else {
            $ranges = array(array(1, 1000), array(1001, 2000), array(2001, 5000), array(5001, 10000), array(10001, 50000), array(50001, 1000000));
        }
	
	return $this->findFirstEmptyNumber($ranges);
	
	/*
	old stuff
	
	$max1=$this->conn->fetchOne("SELECT MAX(menu_id) FROM " . $this->getTable() . " WHERE server = ? AND lang = ? AND ver = ?", array(
            $this->server, $this->lang, $this->ver
        )) + 1;
	
	$max2=$this->conn->fetchOne("SELECT MAX(menu_id) FROM webtd WHERE server = ? AND lang = ? AND ver = ? AND menu_id>0", array(
            $this->server, $this->lang, $this->ver
        )) + 1;	
	
        return max($max1,$max2);
        */
    }

    /**
     * @param int $src_id
     * @param int $new_id
     * @param string $name
     * @return int
     */
    public function copy_menu($src_id, $new_id = null, $name = null, $recurrence = false, $src_lang='', $src_ver=0, $src_server=0)
    {
        if ($links = $this->getAll($src_id,-1,-1,$src_lang,$src_ver,$src_server)) {

            if (is_null($new_id) )
                $new_id = $this->get_new_menu_id();

            if (!$name)
                $name = $links[0]['name'];

            foreach ($links as $link) {
                unset($link[$this->getKey()]);
		unset($link['lang']);
		unset($link['ver']);
		unset($link['server']);
		
                $copy = new self($link, true);
                $copy->menu_id = $new_id;
		$copy->name = $name;
		
		$copy->save();
		
		if ($recurrence && $link['submenu_id']) {
		    
		    $copy->submenu_id = $this->copy_menu($link['submenu_id'],null,null,true,$src_lang,$src_ver,$src_server);
		    $copy->save();	
		}
		
                
                
            }

            return $new_id;
        }
        return -1;
    }
    
    
    function save()
    {
	$page_target=explode(':',$this->page_target);
	if (count($page_target)>1) {
	    $this->page_target=$page_target[1];
	    $this->lang_target=$page_target[0];
	}
	
	if (!$this->checkRight()) return false;
	
	return parent::save();
    }
    
    
    
    public function checkRight($menu_id=null) {
	if (!$menu_id) $menu_id=$this->menu_id;
	
	
	$server=Bootstrap::$main->session('server');
	
	
	if (!isset($server['menus'])) return true;
	
	$menus=$server['menus'];
	
	
	
	return $this->checkRightRange($menu_id,$menus);
    }    
    
}
