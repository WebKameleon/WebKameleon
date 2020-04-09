<?php

class webtdModel extends webModel {
	protected $_key = 'sid';

	/**
	 * @var array
	 */
	    public $obtd;
	
	
	public function getAll($pages,$ver=0,$lang=null,$server=0,$mode=0) {
		if (!$ver) $ver=$this->ver;
		if (!$lang) $lang=$this->lang;
		if (!$server) $server=$this->server;
		
		if (!is_array($pages) || !count($pages)) return;
		
		$debug_id=Debugger::debug(null,'get-all-td ('.implode(',',$pages).')');
		
		foreach($pages AS $i=>$page) if (!strlen($page)) unset($pages[$i]);
		
		$arch = $mode <= PAGE_MODE_PREVIEW ? ' AND (nd_valid_to IS NULL OR nd_valid_to>'.Bootstrap::$main->now.') AND (hidden IS NULL OR hidden=0)':'';
		
		$sql="SELECT * FROM webtd WHERE server=? AND lang=? AND ver=?
			AND page_id IN (".implode(',',$pages).") AND trash=0 $arch
			ORDER BY page_id,level,pri,sid";
	
		$data = $this->conn->fetchAll($sql,array($server,$lang,$ver));
		
		
		foreach($data AS &$td) {
			if (!trim($td['uniqueid'])) {
				$m=new webtdModel($td['sid']);
				$td['uniqueid']=$m->uniqueid=$m->uniqueid();
				$m->save();
			}
		}
	
		Debugger::debug($debug_id);
		return $data;
	}
	
	
	public function getCat($cats,$ver=0,$lang=null,$server=0,$mode,$limit=0,$offset=0,$sort=0,$sort_dir=1,$bgimg_only=false,$since=0,$till=0)
	{
		if (!$ver) $ver=$this->ver;
		if (!$lang) $lang=$this->lang;
		if (!$server) $server=$this->server;
		
		switch ($sort)
		{
			case 1:
				$sortby='nd_custom_date_end';
				break;
			case 2:
				$sortby='title';
				break;
			
			default:
				$sortby='nd_custom_date';
				break;
		}
		
		$sortby.=$sort_dir?' ASC':' DESC';
		
		$arch = $mode <= PAGE_MODE_PREVIEW ? ' AND (nd_valid_to IS NULL OR nd_valid_to>'.Bootstrap::$main->now.') AND (hidden IS NULL OR hidden=0)':'';
		$and='';
		
		if ($bgimg_only) $and.=" AND bgimg<>''";
		if ($since) $and.=" AND nd_custom_date>=$since";
		if ($till) $and.=" AND nd_custom_date<=$till";
		$nd_ftp = $mode ? '':' AND nd_ftp>0';
		
		$sql="SELECT * FROM webtd WHERE server=? AND lang=? AND ver=?
			AND sid IN (SELECT tdsid FROM webcat WHERE server=? AND category IN ('".implode("','",$cats)."'))
			AND page_id IN (SELECT id FROM webpage WHERE id=webtd.page_id AND server=? AND lang=? AND ver=? $nd_ftp)
			AND trash=0 $arch $and
			ORDER BY $sortby";
		
		$sql = $this->conn->modifyLimitQuery($sql,$limit,$offset);

		
		
		$data = $this->conn->fetchAll($sql,array($server,$lang,$ver,$server,$server,$lang,$ver));
		
		if (is_array($data)) foreach ($data AS &$td)
		{
			$sql="SELECT category FROM webcat WHERE tdsid=".$td['sid'];
			$td['cats']=$this->conn->fetchColumn($sql);		
		}
		
		return $data;
	}
	
	
	
	
	
	public function move($sid=0,$step=0) {
		if (!$step) return;
		$orig_sid=$sid;
		if (!$sid) $sid=$this->sid;
		else $this->get($sid);
		
		if (!$sid) return;
		
		$server=$this->data['server'];
		$page_id=$this->data['page_id'];
		$ver=$this->data['ver'];
		$lang=$this->data['lang'];
		$level=$this->data['level']+0;
		$pri=$this->data['pri']+0;
		
		$std_where = "server=$server AND page_id=$page_id AND ver=$ver AND lang='$lang' AND level=$level AND trash=0";
		
		
		if ($step>0) {
			$sql="SELECT min(pri) FROM webtd WHERE $std_where AND pri>$pri";
		} else {
			$sql="SELECT max(pri) FROM webtd WHERE $std_where AND pri<$pri";
		}
		
		
		$switchpri=$this->conn->fetchOne($sql);
		if (strlen($switchpri)) {
			$sql="UPDATE webtd SET pri=$pri WHERE $std_where AND pri=$switchpri";
			$this->conn->execute($sql);
			$this->pri=$switchpri;
			$this->save();
		} else {
			$sql="SELECT max(pri) FROM webtd WHERE $std_where AND sid<>$sid";
			$max=$this->conn->fetchOne($sql);			
			$sql="SELECT min(pri) FROM webtd WHERE $std_where AND sid<>$sid";
			$min=$this->conn->fetchOne($sql);			
			
			if ($step>0) {
				$sql="UPDATE webtd SET pri=pri+1 WHERE $std_where AND sid<>$sid";		
				$this->conn->execute($sql);
				$this->pri=$min;
				$this->save();
			} else {

				$this->pri=$max+1;
				$this->save();				
			}
		}
		
		$this->move($orig_sid,(abs($step)-1)*($step/abs($step)));
	}
	
	
	public function uniqueid($sid=null) {
		if (!$sid) $sid=$this->sid;
		if (!$sid) return;
		
		$t=time();
		$j=0;
		while (true)
		{
			$uid=sprintf("%08X",$t-$j+$sid);
			$sql="SELECT count(*) AS c1 FROM webtd WHERE uniqueid=?";
			$c=$this->conn->fetchOne($sql,array($uid));
			
			if ($c)
			{
				$j++;
				continue;
			}
			return $uid;
		}
		
		
	}
	
	public function replace_uniqueids($server,$uniqueids,$ver=0,$firstsid=0) {
		
		foreach($uniqueids AS $old=>$new) {
			if (!$old || !$new) continue; 
			$sql="SELECT * FROM webtd WHERE server=?";
			if ($ver) $sql.=" AND ver=$ver";
			if ($firstsid) $sql.=" AND sid>=$firstsid";
			$sql.=" AND plain LIKE '%${old}%'";
			
			
			foreach($this->conn->fetchAll($sql,array($server)) AS $td) {
				$webtd=new webtdModel($td);
				$webtd->plain = str_replace($old,$new,$webtd->plain);
				$webtd->save();
			}
		}
		
	}
	
	public function next_pri($page_id=null,$level=null) {
		if (is_null($page_id)) $page_id=$this->page_id;
		if (is_null($level)) $level=$this->level;
		
		
		$sql="SELECT max(pri) FROM webtd WHERE server=? AND page_id=? AND ver=? AND lang=? AND level=? ";
		
		
		return 1+$this->conn->fetchOne($sql,array($this->server,$page_id,$this->ver,$this->lang,$level));
	}

    public function add($page_id, $type)
    {
        $session = Bootstrap::$main->session();
        $config = Bootstrap::$main->getConfig();

        if ($page_id == $session['server']['header'])
            $level = $config['default']['level']['header'];
        elseif ($page_id == $session['server']['footer'])
            $level = $config['default']['level']['footer'];
        else
            $level = $config['default']['level']['body'];
	
		if ($page_id>0)
		{
			$webpage=new webpageModel();
			$wp=$webpage->getOne($page_id,true);
		
			if (isset($config['webpage']['type'][$wp['type']]['level']) && $config['webpage']['type'][$wp['type']]['level'])
			{
				$level=$config['webpage']['type'][$wp['type']]['level'];
			}
	
		}
	
        $model = new webtdModel();
        $model->page_id = $page_id;
        $model->type = $type;
        $model->level = $level;
        $model->pri = $this->next_pri($page_id, $level);

        if (isset($config['webtd']['type'][$type])) {
            $options =& $config['webtd']['type'][$type];

            if (isset($options['menu']) && $options['menu']) {
                $weblink = new weblinkModel();
                $model->menu_id = $weblink->get_new_menu_id();
                if (!isset($options['filename'])) $model->type = 0;
            }

            if (isset($options['widget']) && $options['widget']) {
                $model->widget = $options['widget'];
            }

            if (isset($options['add_title']) && $options['add_title']) {
                $model->title = Tools::translate('New').' '.Tools::translate($options['name']);
            }
			
			if (isset($options['html']) && $options['html']) {
                $model->html = $options['html'];
            }
			
			if (isset($options['staticinclude']) && $options['staticinclude']) {
                $model->staticinclude = $options['staticinclude'];
            }
			
			if (isset($options['obtd']) && $options['obtd']) {
                $model->obtd = $options['obtd'];
            }
        }

        $obj = $model->save();

        return $obj;

    }

    public function data()
    {
        $data = parent::data();

        $obtd = array();
        $obtd[1] = ($this->ob & 1) == 1;
        $obtd[2] = ($this->ob & 2) == 2;

        $data['obtd'] = $obtd;

        return $data;
    }
    
	public function checkRight($page_id=null) {
		
		if (is_null($page_id)) $page_id=$this->page_id;
		$webpage=new webpageModel();
		return $webpage->checkRight($page_id);
	}
	
	public function mayProof($page_id=null) {
		if (is_null($page_id)) $page_id=$this->page_id;
		$webpage=new webpageModel();
		return $webpage->mayProof($page_id);
	}    
	
	
	
    public function save()
    {
        if (is_array($this->obtd)) {
            $this->ob = 0;
            $this->ob = $this->ob | $this->obtd[1];
            $this->ob = $this->ob | $this->obtd[2];
        }
	
	if (!$this->checkRight()) return false;
	if (!strlen($this->page_id)) return false;
	
	if (!$this->uniqueid) $this->uniqueid=$this->uniqueid();
	
        $ret = parent::save();

        $page = $this->page_id >= 0 ? $this->page_id : 0;
        $webpage = new webpageModel();
        $webpage->getOne($page);
        $webpage->nd_update = $this->nd_update;
        
	
	if (!$this->mayProof())
	{
		if ($webpage->unproof_sids==':') $webpage->unproof_sids='';
		$sids=strlen($webpage->unproof_sids) ? explode(',',$webpage->unproof_sids) : array();
		if (!in_array($this->sid,$sids)) $sids[]=$this->sid;
		$webpage->unproof_sids=implode(',',$sids);
	}
	
	$webpage->save();

        return $ret;
    }



    /**
     * @return bool
     */
    public function has_widget()
    {
        return $this->widget && Widget::exists($this->widget);
    }

    /**
     * @return Widget
     */
    public function get_widget($page=0)
    {
	$data=$this->data();
	$widget_data=unserialize(base64_decode($data['widget_data']));
	
	if (is_array($widget_data) && is_array($this->data['widget_data']))
	{
		$this->data['widget_data'] = array_merge($widget_data,$this->data['widget_data']);
	}
	
	$widget=Widget::factoryWebtd($this->data,$page);
	return $widget;
    }

    /**
     * @param $data
     */
    public function update_widget($data=null)
    {
        if (!is_null($data)) $this->widget_data = $data;

        $widget = $this->get_widget();
        $widget->update();


        $this->widget_data = base64_encode(serialize($widget->data));
        $this->save();
    }
    
    
    public function levels($body=true)
    {
	if ($body) return $this->conn->fetchAll("SELECT level,count(*) AS count FROM webtd WHERE server=? AND ver=? AND page_id>=0 GROUP by level ORDER BY level", array($this->server,$this->ver));
	

	return $this->conn->fetchAll("SELECT level,page_id,count(*) AS count FROM webtd WHERE server=? AND ver=? AND page_id<0 GROUP by level,page_id ORDER BY page_id DESC,level", array($this->server,$this->ver));
    }
    
}
