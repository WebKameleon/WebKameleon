<?php

class articlelistWidget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'articlelist';

    
    public function edit()
    {
        parent::edit();
        
        
        $webcat=new webcatModel();
        
        $webcat->purge($this->webtd['server']);
        
        $this->data['cats']=$webcat->getCats($this->webtd['server']);
        
        foreach($this->data['cats'] AS $i=>$v)
        {
            if (is_array($this->data['category']) && in_array($v['category'],$this->data['category']) ) $this->data['cats'][$i]['checked']=true;
        }
        
    }
    
    public function update()
    {
        if ($this->data['since']) $this->data['since']=0+Bootstrap::$main->kameleon->strtotime($this->data['since']);
        if ($this->data['till']) $this->data['till']=0+Bootstrap::$main->kameleon->strtotime($this->data['till']);
        
        $cnf=Bootstrap::$main->getConfig('widgets.articlelist');
        foreach(array('thumb_width','thumb_height','width','height') AS $f) $this->data[$f]=$cnf[$f];
        
        return parent::update();
    }
    
    public function run()
    {

        parent::run();
        
        //mydie($this->data);

        if (!isset($this->data['category']) || !is_array($this->data['category']) || !count($this->data['category']) ) return;
        
        $cats=$this->data['category'];
        
        $webtd=new webtdModel($this->webtd['sid']);
        
        $limit = $this->data['na']+0;
        $offset = $this->data['from'] ? $this->data['from']-1 : 0; 

        if (!isset($this->data['sort'])) $this->data['sort']=0;
        if (!isset($this->data['sort_dir'])) $this->data['sort_dir']=0;
        if (!isset($this->data['images'])) $this->data['images']=0;
        
        if (!isset($this->data['since'])) $this->data['since']='';
        if (!isset($this->data['till'])) $this->data['till']='';
        
        $tds=$webtd->getCat($cats,0,null,0,$this->mode,$limit,$offset,$this->data['sort'],$this->data['sort_dir'],$this->data['images'],$this->data['since'],$this->data['till']);
        
        $this->crop=true;
        
        $index=new indexController();
        
        $max_update=0;
        $counter_i=0;
        
        foreach ($tds AS $i=>$td)
        {
            $td['plain_orig']=$td['plain'];
            $max_update=max($max_update,$td['nd_update']);
            if (trim($td['trailer'])) $td['plain']=$td['trailer'];
            
            
            $td['plain']=Tools::nohtml($td['plain'],array('a','br'));
            
            if ($this->data['chr'] && $this->strlen($td['plain']) > $this->data['chr'])
            {
                
                $add=$this->data['chr'];
                while($this->strlen($td['plain']) > $this->data['chr'])
                {
                    $td['plain']=mb_substr($td['plain'],0,$this->data['chr']+$add--,'utf8');
                }
                while (substr($td['plain'],-1)!=' ' && substr($td['plain'],-1)!="\n" && strlen($td['plain']))
                    $td['plain']=mb_substr($td['plain'],0,mb_strlen($td['plain'],'utf8')-1,'utf8'); 
                
                $td['plain']=preg_replace('/<[^>]*$/','',$td['plain']);
                
                $tds[$i]['plain']=$td['plain'];
            }
            
            $tds[$i]=$index->_process_td($td,$this->mode,$this->webpage['id'],$this->webpage['tree']);
            
            $tds[$i]['more_link']=Bootstrap::$main->kameleon->href('','',$td['page_id'],$this->webpage['id'],$this->mode);
            
            if ($td['bgimg'])
            {
                $this->scale=true;
                Bootstrap::$main->tokens->loadJQuery = true;
                
                $d=$this->data;
                $this->checkThumb($td['bgimg']);
                $this->checkImage($td['bgimg']);
                $this->data=$d;
            }
            
            if ($td['attachment'])
            {
                $tds[$i]['attachment_class'] = strtolower(end(explode('.',$td['attachment'])));
            }
            
            if (isset($this->data['text']) && $this->data['text'] && !strlen(trim($td['plain']))) unset($tds[$i]);
        
            $tds[$i]['first_child']=0;
            $tds[$i]['last_child']=0;
            
            if ($counter_i++ == 0) $tds[$i]['first_child'] = 1;
            if ($counter_i == count($tds)) $tds[$i]['last_child'] = 1;
        }
        
        if ($this->webtd['size'] && count($tds)>$this->webtd['size'])
        {
            $this->nav=array();
            $this->nav_limit=$this->webtd['size'];
            
            for($i=0;$i<ceil(count($tds)/$this->webtd['size']);$i++) $this->nav[]=$i+1;
            Bootstrap::$main->tokens->loadJQuery = true;
            $this->loadJS('articlelist.js');
        }
        
        $this->articles=$tds;
        
        if ($max_update>$this->webtd['nd_update'])
        {
            $webtd=new webtdModel($this->webtd['sid']);
            $webtd->nd_update=$max_update;
            $webtd->save();
        }
    }
    
 
    
    protected function strlen($str)
    {
        return mb_strlen(Tools::nohtml($str),'utf8');
    }
}
