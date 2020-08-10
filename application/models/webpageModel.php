<?php

class webpageModel extends webModel
{
    protected $_key = 'sid';
    protected $_table = 'webpage';
    protected $_subkey='id';

    public $header, $footer;

    protected static $main;
    
    protected static $rightCache;

    public function __construct($data = null, $new = false)
    {
        parent::__construct($data, $new);

        $server = Bootstrap::$main->session('server');
        $this->header = $server['header'];
        $this->footer = $server['footer'];
	
	
    }

    public function setMain()
    {
        self::$main = $this;
    }

    public function getMain()
    {
        return self::$main;
    }

    
    private $nextcycle=array();
    
    
    protected function nextcycle($sid)
    {
		if (in_array($sid,$this->nextcycle)) return true;
		$webpage=new self($sid);
		if (!$webpage->next) return false;
		$this->nextcycle[]=$sid;
	
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE id = ? AND server = ? AND lang = ? AND ver = ? AND trash=0 ORDER BY ver DESC";
        $sql = $this->conn->modifyLimitQuery($sql, 1);

		$data = $this->conn->fetchRow($sql, array($webpage->next, $this->server, $this->lang, $this->ver));
	
		if (!$data) return false;
		if (!is_array($data)) return false;
		
		return $this->nextcycle($data['sid']);
    }
    
    public function getOne($page, $onlythisver = false)
    {
        $v = $onlythisver ? '=' : '<=';
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE id = ? AND server = ? AND lang = ? AND ver $v ? AND trash=0 ORDER BY ver DESC";
        $sql = $this->conn->modifyLimitQuery($sql, 1);

		$this->nextcycle=array();
        $data = $this->conn->fetchRow($sql, array($page, $this->server, $this->lang, $this->ver));

        if ($data)
		{
			$this->load($data);
			if ($this->next && $this->nextcycle($this->sid))
			{
			$this->next=null;
			$data['next']=null;
			$this->save();
			}
		}
	
        return $data;
    }

    /**
     * @param int $prev
     * @return array
     */
    public function getAllByPrev($prev=null)
    {
	if (is_null($prev)) $prev=$this->id;
	
        return $this->conn->fetchAll("SELECT * FROM " . $this->getTable() . " WHERE prev = ? AND server = ? AND lang = ? AND ver = ? AND trash=0 ORDER BY ".Bootstrap::$main->now."-nd_create<10 DESC, COALESCE(title_short,title),title", array(
            $prev, $this->server, $this->lang, $this->ver
        ));
    }

    /**
     * @param $prev
     * @return bool
     */
    public function hasChildren($prev=null)
    {
	if (is_null($prev)) $prev=$this->id;
	
        return !!$this->conn->fetchOne("SELECT 1 FROM " . $this->getTable() . " WHERE prev = ? AND server = ? AND lang = ? AND ver = ? AND trash=0", array(
            $prev, $this->server, $this->lang, $this->ver
        ));
    }

    /**
     * @param $prev
     * @return dataset
     */
    public function getChildren($prev=null)
    {
	if (is_null($prev)) $prev=$this->id;
	
        return $this->conn->fetchAll("SELECT * FROM " . $this->getTable() . " WHERE prev = ? AND server = ? AND lang = ? AND ver = ? AND trash=0 ORDER BY ".Bootstrap::$main->now."-nd_create<10 DESC,COALESCE(title_short,title),title", array(
            $prev, $this->server, $this->lang, $this->ver
        ));
    }    
    
    
    
    /**
     * @param int $id
     * @return array
     */
    public function getAllParents($id)
    {
        $parents = array();
        $page = $this->getOne($id);
        while ($page = $this->getOne($page['prev'])) {
            $parents[] = $page;
        }

        return $parents;
    }

    /**
     * @param int $prev
     * @return int
     */
    public function getPagesCountByPrev($prev=null)
    {
	if (is_null($prev)) $prev=$this->id;
	
        return $this->conn->fetchOne("SELECT COUNT(*) FROM " . $this->getTable() . " WHERE prev = ? AND server = ? AND lang = ? AND ver = ? AND trash=0", array(
            $prev, $this->server, $this->lang, $this->ver
        ));
    }

    
    protected $treecycle;
    
    public function trees($page, $ver = 0, $type = null,$clear_reccurence_array=true)
    {
	if ($clear_reccurence_array) $this->treecycle=array();
    
	if (isset($this->treecycle[$page]))
	{
	    $webpage=new webpageModel();
	    $webpage->getOne($page);
	    $webpage->prev=0;
	    $webpage->save();
	}

        if (!$ver) $ver = $this->ver;
        $ret = array(
            'type' => array(),
            'notype' => array()
        );
        if (!strlen($page) || $page < 0 || isset($this->treecycle[$page])) return $ret;
	
	$this->treecycle[$page]=true;

        $sql = "SELECT prev,type FROM webpage WHERE id=? AND server=? AND lang=? AND ver=? AND trash=0";
        $r = $this->conn->fetchRow($sql, array(
            $page,
            $this->server,
            $this->lang,
            $ver
        ));

        if (!$r) return $ret;

        $addthispage = !is_null($type);
        if (is_null($type)) $type = $r['type'] + 0;

        $ret2 = $this->trees($r['prev'], $ver, $type,false);
        $ret['type'] = array_merge($ret['type'], $ret2['type']);
        $ret['notype'] = array_merge($ret['notype'], $ret2['notype']);

        if ($addthispage) {
            $ret['notype'][] = $page;
            if ($type + 0 == $r['type'] + 0) $ret['type'][] = $page;
        }

        return $ret;
    }
    
    
    public function getUnproven ()
    {
        return $this->conn->fetchAll("SELECT * FROM " . $this->getTable() . " WHERE server = ? AND lang = ? AND ver = ? AND trash=0 AND noproof<>0", array(
	    $this->server, $this->lang, $this->ver
        ));	
    }
    
    public function mayProof($page=null,$username=null)
    {
	$page_was_null=is_null($page);
        if (is_null($page)) $page=$this->id;
	if ($page<0) $page=0;
	
	$server=Bootstrap::$main->session('server');
	
	if (!$username) {
	    if (!isset($server['proof'])) return true;
	    $pages=$server['proof'];
	} else {
	    $rights=new rightModel();
	    $r = $rights->getRight($server['id'],$username);
	    if (!$r['proof']) return true;
	    $pages=$r['proof'];
	}
	
	if ($page>0) {
	    if (!$page_was_null)
	    {
		$tmp=new webpageModel();
		$tmp->getOne($page);
		$tree=$tmp->tree;
	    } else {
		$tree=$this->tree;
	    }		    
		
	} else $tree='';
	
	return $this->checkRightRange($page,$pages,$tree);
    }

    public function checkRight($page = null)
    {
	$page_was_null=is_null($page);
        if (is_null($page)) $page=$this->id;
	if ($page<0) $page=0;
	
	if (isset(self::$rightCache[$page])) return self::$rightCache[$page];
	
	$server=Bootstrap::$main->session('server');

	$ret=true;
	if (isset($server['pages'])) {    
	    $pages=$server['pages'];

	    
	    if ($page>0) {
		if (!$page_was_null)
		{
		    $tmp=new webpageModel();
		    $tmp->getOne($page);
		    $tree=$tmp->tree;
		} else {
		    $tree=$this->tree;
		}		    
		    
	    } else $tree='';
	    
	    $ret=$this->checkRightRange($page,$pages,$tree);
	}
	if ($ret) {
	    $may_proof=$page_was_null?$this->mayProof():$this->mayProof($page);
	    
	    if (!$may_proof) {
		if ($page_was_null) {
		    $ret = $this->noproof >= 0;
		} else {
		    $webpage = new webpageModel();
		    $webpage->getOne($page);
		    $ret = $webpage->noproof >= 0;		    
		}
	    }	    
	}
	self::$rightCache[$page]=$ret;
	return $ret;
    }



    public function sidsForFtp($limit, $all, $server, $lang, $ver)
    {
        $sql = "SELECT id,sid,nd_update,nd_ftp,lang FROM " . $this->getTable() . " WHERE server=? AND ver<=? AND trash=0 AND id IS NOT NULL";

        if (strlen($limit)) {
            $ppos = strpos($limit, '+');
            $limit = preg_replace('/[^0-9]/', '', $limit);
            $sql .= $ppos ? " AND (tree ~ ':$limit:' OR id=$limit)" : " AND id=$limit";
        }
	
	if (is_array($lang))
	{
	    $sql.=" AND lang IN ('".implode("','",$lang)."')";
	}
	else
	{
	    $sql.=" AND lang='$lang'";
	}
	
        $sql .= " ORDER BY id,ver DESC";

        $pages = array();
        $allpages = $this->conn->fetchAll($sql, array($server, $ver));
	
	
	
        if (!$allpages) return $pages;
    
        foreach ($allpages AS $page) {
	    $token=$page['lang'].'-'.$page['id'];
            if (isset($pages[$token])) continue;
	    
            if (!strlen($limit) && !$all && ($page['nd_ftp'] > $page['nd_update'] && $page['nd_ftp'] && $page['nd_update'])) continue;	    
	    $pages[$token] = $page['sid'];
        }

        return $pages;
    }

    
    protected $file_name_cache;
    
    public function file_name($webpage = null)
    {
        if (is_null($webpage)) {
            $wp = $this->data();
        } elseif (is_object($webpage)) {
            $wp = $webpage->data();
        } elseif (is_int($webpage)) {
            $wp = $this->getOne($webpage);
        } elseif (is_array($webpage)) {
            $wp = $webpage;
        } else {
            return;
        }

		if (isset($wp['sid'])) if (isset($this->file_name_cache[$wp['sid']])) return $this->file_name_cache[$wp['sid']];
	
        $path = Bootstrap::$main->session('path');
	
        $file_name = trim($wp['file_name']);
        if (!$file_name) $file_name = $path['pages'] . '/' . $wp['id'] . '.html';
        $file_name = $path['pageprefix'] . $file_name;

		if (isset($wp['sid'])) $this->file_name_cache[$wp['sid']]=$file_name;
        return $file_name;
    }

    public function remove($id, $recursive = false)
    {
        //if (!$id) return;
        $this->getOne($id);
        if (!$this->sid) return;
        if (!$this->checkRight()) return;

        if (!parent::remove()) return false;
	

	
	if ($id==0)
	{
	    $this->conn->execute("UPDATE weblink SET trash=".Bootstrap::$main->now." WHERE server = ? AND lang = ? AND ver = ? ", array(
		$this->server, $this->lang, $this->ver
	    ));	    
	}	
	
	$ifpage0alsohf = $id ? '=' : '<=';
        $this->conn->execute("UPDATE webtd SET trash=-".$this->sid." WHERE page_id $ifpage0alsohf ? AND server = ? AND lang = ? AND ver = ?", array(
            $id, $this->server, $this->lang, $this->ver
        ));
	


	
        $this->conn->execute("UPDATE weblink SET page_target=null WHERE page_target = ? AND server = ? AND lang = ? AND ver = ? AND (lang_target IS NULL OR lang_target='' OR lang_target=?)", array(
            $id, $this->server, $this->lang, $this->ver,$this->lang
        ));
	
        $this->conn->execute("UPDATE weblink SET page_target=null WHERE page_target = ? AND server = ? AND lang <> ? AND ver = ? AND lang_target=?", array(
            $id, $this->server, $this->lang, $this->ver,$this->lang
        ));	


        $this->conn->execute("UPDATE webtd SET next=null WHERE next = ? AND server = ? AND lang = ? AND ver = ? ", array(
            $id, $this->server, $this->lang, $this->ver
        ));	

        $this->conn->execute("UPDATE webtd SET more=null WHERE more = ? AND server = ? AND lang = ? AND ver = ? ", array(
            $id, $this->server, $this->lang, $this->ver
        ));	

	
        $this->conn->execute("UPDATE webpage SET tree = NULL WHERE tree LIKE '%:$id:%' AND server = ? AND lang = ? AND ver = ?", array(
            $this->server, $this->lang, $this->ver
        ));

	
	
	
        if ($recursive) {
            $children = $this->getAllByPrev($id);
            foreach ($children AS $child) {
                $this->remove($child['id'], $recursive);
            }
        } else {
		
            $this->conn->execute("UPDATE webpage SET prev = ? WHERE prev = ? AND server = ? AND lang = ? AND ver = ?", array(
                $this->prev, $id, $this->server, $this->lang, $this->ver
            ));
        }
	
    }

    /**
     * @param int $id
     * @param bool $wholeTree
     * @param bool $hidden
     */
    public function changeProperty($id, $property, $value = null, $wholeTree = false)
    {
        $this->getOne($id);
        if ($value === null) {
            $value = $this->$property ? 0 : 1;
        }
        $this->$property = $value;
        $this->save();

        if ($wholeTree) {
            foreach ($this->getAllByPrev($id) as $page) {
                $this->changeProperty($page['id'], $property, $value, $wholeTree);
            }
        }
    }
    
    protected function title2url($wp) {
	$title=!empty($wp['title_short'])?$wp['title_short']:$wp['title'];
	
	$title=Bootstrap::$main->kameleon->str_to_url($title,-1);
		
	return $title;
    }
    
    
    protected function url_path($page) {
	static $cache;
	
	if (isset($cache[$page])) return $cache[$page];
	
	if ($page==0) return $cache[$page]='';
	
	$wp=$this->getOne($page);
	if ($wp['nositemap']) return $cache[$page]='';
    
	return $cache[$page]=$this->title2url($wp);
    }
    
    
    public function createFilename($wp,$add_index=0) {
	$config=Bootstrap::$main->getConfig();

	if ($wp['id']==0) return $config['default']['directory_index'][0];	
	
	$title=$this->title2url($wp);
	
	
	if (!$title) return;
	
	$path='';
	
	if (empty($wp['tree']))
	{
	    $trees=$this->trees($wp['id'],$wp['ver']);
	    
	    if (isset($trees['notype'])) {
		$tree=implode(':',$trees['notype']);
		if (strlen($tree)) $tree=":$tree:";
		$wp['tree'] = $tree;
	    }
	    
	}
	
	foreach (explode(':',$wp['tree']) AS $parent) {
		if (!strlen($parent)) continue;
		$node=$this->url_path($parent);
		if ($node) {
			$path.=$node.'/';
		}
	}
	
	if (!$add_index) $add_index='';
	
	return $path.$title.$add_index.'/'.$config['default']['directory_index'][0];
    }
    
    public function filenameCount($file_name,$except=0) {
		return $this->conn->fetchOne("SELECT count(*) FROM webpage WHERE server=? AND ver=? AND lang=? AND file_name=? AND id<>?", array($this->server,$this->ver,$this->lang, $file_name,$except));
    }
    
    
    public function langs() {
		$sql="SELECT lang FROM webpage WHERE server=? AND id=0 AND trash=0 GROUP BY lang";
		$langs = $this->conn->fetchAll($sql, array($this->server));
		
		$ret=array();
		if (is_array($langs)) foreach($langs AS $lang) $ret[]=$lang['lang'];
		
		return $ret;
    }
    
    public function untrash($id=null) {
	    if (!$id) $id=$this->sid;
    
	    if (parent::untrash($id)) {
		    $sql="UPDATE webtd SET trash=0 WHERE trash=-".$id;
		    return $this->conn->execute($sql);
	    }
	    
    }    

    public function save($default_update_key=true,$checkrights=true)
    {
		if (!strlen($this->id)) return false;
		
		
		
		if ($this->id)
		{
			if ($this->prev==0) $this->tree=':0:';
			else {
				$trees=$this->trees($this->prev,$this->ver);
				if (isset($trees['notype'])) {
					$this->tree=':'.implode(':',$trees['notype']).':'.$this->prev.':';
				}
			}
		}
		
		if ($checkrights && !$this->checkRight()) return false;
		
		
		$filename='';
	
		$resitemap=0;
		foreach (array('file_name','trash','hidden','nositemap') AS $f) {
			if (in_array($f,array_keys($this->data))  && in_array($f,array_keys($this->savedData)) && trim($this->data[$f]) != trim($this->savedData[$f]) ) {
				
				//mydie($this->data);
				$resitemap=1;
				break;
			}		
		}
		
		if (!$this->sid) {
			$resitemap=1;
		}
		
		if ($resitemap) {
			$server=new serverModel($this->server);
			$server->resitemap=$resitemap;
			$server->save();
		}
	
		
		if ($this->trash >0 || $this->hidden ) {
			$filename=$this->file_name();
		} elseif ( isset($this->data['file_name']) && isset($this->savedData['file_name']) && trim($this->data['file_name']) != trim($this->savedData['file_name'])) {
			
			$filename=$this->file_name();
		}
	
		if ($filename) {
			$webpagetrash=new webpagetrashModel();
			$webpagetrash->server=$this->server;
			$webpagetrash->page_id=$this->id;
			$webpagetrash->ver=$this->ver;
			$webpagetrash->lang=$this->lang;
			$webpagetrash->nd_issue=time();
			$webpagetrash->file_name=$filename;
			$webpagetrash->status='N';
			$webpagetrash->save();
		}
		
		if (!$this->mayProof() && $checkrights) {
			$user=Bootstrap::$main->session('user');
			$this->unproof_autor = $user['username'];
			$this->unproof_date=Bootstrap::$main->now;
			$this->noproof++;
		}    
		
		return parent::save($default_update_key);
    }
    
    public function next_page($id)
    {
	    
	if (!$id) return 0;

	$page=$this->getOne($id,true);
	
	if (!empty($page['next']) && $page['next']>0)
	{
		return $this->next_page($page['next']);
	}
	
	return $id;
    }
    
    public function count_pages($server=null)
    {
	if (!$server) $server=$this->server;
	
	$ret=$this->conn->fetchAll("SELECT lang,count(*) AS count FROM webpage WHERE server=? AND ver=? AND trash=0 GROUP BY lang", array($server,$this->ver));
	
	return $ret;
	
    }
    
    
    public function appengine_login()
    {
	if ($this->appengine_login != 'inherit') return $this->appengine_login;
	if ($this->id == 0) return '';
	
	$webpage=new webpageModel();
	$webpage->getOne($this->prev);
	return $webpage->appengine_login();
    }
    
    
    public function next_to_translate($prev=null)
    {
	$sql = "SELECT olang||','||id FROM " . $this->getTable() . " WHERE server = ? AND lang = ? AND ver = ? AND trash=0 AND id>0 AND lang<>olang";
	if (!is_null($prev)) $sql.=" AND prev=$prev";
	$sql.=" ORDER BY id";
	$sql = $this->conn->modifyLimitQuery($sql, 1);
	
	return $this->conn->fetchOne($sql, array($this->server, $this->lang, $this->ver));
    }


    public function types()
    { 
	
		return $this->conn->fetchAll("SELECT type,count(*) AS count FROM webpage WHERE server=? AND ver=? GROUP by type ORDER BY type", array($this->server,$this->ver));
    }
}
