<?php

class webfavModel extends Model {
	protected $_key = 'wf_sid';
	
	public function getBookmarks($server,$username,$lang) {
		$sql="SELECT * FROM webfav
			LEFT JOIN webpage ON webfav.wf_server=webpage.server AND webfav.wf_lang=webpage.lang AND webfav.wf_page_id=webpage.id
			WHERE wf_server=? AND wf_user=? AND wf_lang=?
			ORDER BY wf_sid";
		
		return $this->conn->fetchAll($sql,array($server,$username,$lang));
	}
	
	public function addRemove($page,$server,$username,$lang) {
		$sql="SELECT wf_sid FROM webfav WHERE wf_page_id=? AND wf_server=? AND wf_user=? AND wf_lang=?"; 
		$id=$this->conn->fetchOne($sql,array($page,$server,$username,$lang));
	
		if ($id) $this->remove($id);
		else {
			$this->load(array('wf_page_id'=>$page,'wf_server'=>$server,'wf_user'=>$username,'wf_lang'=>$lang),true);
			$this->save();
		}
	
	}
}
