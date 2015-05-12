<?php

require_once __DIR__.'/include/Merlin.php';

class merlinWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'merlin';
    protected $merlin;
    

    public function edit_html()
    {
	return '../edit.html';
    }
    
    
    public function init()
    {

	if (isset($this->data['login']) && isset($this->data['pass']) && $this->data['pass'] && $this->data['login'])
	{
	    $this->merlin = new MERLIN ($this->data['login'],$this->data['pass']);
	    $this->data['operators']=$this->merlin->getFilters([],'ofr_tourOp');
	    
	    if (isset($this->data['operator']) && is_array($this->data['operator']))
	    {
		$this->merlin = new MERLIN ($this->data['login'],$this->data['pass'],implode(',',$this->data['operator']));
		
	    
		$merlin_auth=array('login'=>$this->data['login'],'pass'=>$this->data['pass'],'operator'=>$this->data['operator']);
		Bootstrap::$main->session('widget_merlin_auth',$merlin_auth);
	    }
	    
	    //mydie($this->merlin->debug);
	}
	else
	{
	    $merlin_auth=Bootstrap::$main->session('widget_merlin_auth');
	    if ($merlin_auth)
	    {
		foreach ($merlin_auth AS $k=>$v)
		{
		    $this->data[$k]=$v;
		}
		$this->save();
	    }
	}
	
	
        parent::init();
    }

    
    public function edit()
    {

        $this->data['dbg']=$this->merlin->debug;
    }
    
    public function default_html($html_path)
    {
	$config=Bootstrap::$main->getConfig();
	
	$webtd=new webtdModel($this->webtd['sid']);
	$webtd->html=$config['webtd']['widget']['transfer']['merlin'].'/'.basename($this->name).'.php';
	$html=parent::default_html($html_path);
	$webtd->plain=file_get_contents($html);
	$webtd->costxt='login='.$this->data['login'].'&pass='.$this->data['pass'].'&operator='.implode(',',$this->data['operator']);
	if (isset($this->data['type'])) $webtd->costxt.='&type='.$this->data['type'];
	if (isset($this->data['xsearch'])) foreach ($this->data['xsearch'] AS $x) $webtd->costxt.='&data[xsearch]['.$x.']=1';
	$webtd->costxt.='&widget_images='.urlencode($this->widget_images);
	if ($this->webtd['cos']) $webtd->costxt.='&mode='.$this->webtd['cos'];
	
	$webtd->ob=3;
	$webtd->save();
	

	
	return $this->mode ? $html : WIDGETS_PATH.'/merlin/ftp.html';
    }    

    public function default_images_path()
    {
        return WIDGETS_PATH.'/merlin/images';
    }
    
    public function default_images_dest()
    {
        return 'widgets/merlin';
    }
    
    public function run()
    {
	$this->loadCSS('../merlin.css');
	Bootstrap::$main->tokens->loadJQuery = true;
	$this->data['dbg']=$this->merlin->debug;
	$this->widget_images.='/..';
    }

}
