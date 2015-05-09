<?php


class anchorsWidget extends Widget
{
    public $name = 'anchors';

    public $titles;
    public $levels;
    
    

    public function init()
    {
        parent::init();
        $webtd=new webtdModel();
        $tds=$webtd->getAll(array($this->page));
        
        foreach($tds AS $td)
        {
            if (!$td['title']) continue;
            $this->titles[$td['title']]=$td['level'];
            $this->levels[$td['level']]=1;
        }
        
    }

    
    public function edit()
    {
        $level=Bootstrap::$main->getConfig('level');
        
        $levels=array();
        
        foreach(array_keys($this->levels) AS $l)
        {
            $checked=in_array($l,array_keys($this->data['level']));
            $levels[]=array('level'=>$l, 'name'=>$level['body'][$l],'checked'=>$checked);
        }
        
        $this->levels=$levels;
        
        //mydie($levels);
        
        return parent::edit();
    }
    
    
    public function run()
    {

        if (isset($this->data['level']) && count($this->data['level']))
        {
            foreach($this->titles AS $t=>$l)
            {
                if (!in_array($l,array_keys($this->data['level']))) unset($this->titles[$t]);
            }
        }
        if (isset($this->data['animation']) && $this->data['animation'])
        {
            Bootstrap::$main->tokens->loadJQuery = true;
            $this->loadJS('anchors.js');
        } 
        return parent::run();
    }
}