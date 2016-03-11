<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class formWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'form';

    /**
     * @return string
     */
    
    private $client,$service;


    public function update()
    {

    }
    
    public function edit()
    {
		$this->check_scope('drive',$_GET['page']);
	
		parent::edit();
	
	
		if (!isset($this->data['form']) || !$this->webtd['nd_update'])
		{
			$form=Bootstrap::$main->getConfig('webtd');
			$form=$form['form'];
	
			$lang=$this->webtd['lang'];
			
			$formId=isset($form[$lang])?$form[$lang]:$form['en'];
			
			
			$server=Bootstrap::$main->session('server');
			$title=$server['nazwa_long'].' / '.$this->webpage['title'];
			
			$copiedFile = new Google_Service_Drive_DriveFile();
			$copiedFile->setTitle($title);
			
			try {
			$this->data['form'] = $this->service->files->copy($formId, $copiedFile);
			} catch (Exception $e) {
			$this->data['error'] = $e->getMessage();
			}
			
		}
	
	
		if (isset($this->data['form']) && !isset($this->data['error']))
		{
			
			try {
				$id='';
				if (is_array($this->data['form'])) $id=$this->data['form']['id'];
				if (is_object($this->data['form'])) $id=$this->data['form']->id;
			
			$file=$this->service->files->get($id);            
			
			$url=$file['alternateLink'];
				$_url=explode('?',$url);
			$this->data['url']=preg_replace('~/edit$~','/viewform',$_url[0]);
			$this->data['form']=$file;
			
			} catch (Exception $e) {
			$this->data['error'] = $e->getMessage();
			}  
	   
		}
		
		$this->save();
	
    }    

    public function run()
    {

        Bootstrap::$main->tokens->loadJQuery = true;
		$this->loadJS('form.js');

        parent::run();

	if (isset($this->data['form']))
	{
	    try {
		$id='';
		if (is_array($this->data['form'])) $id=$this->data['form']['id'];
		if (is_object($this->data['form'])) $id=$this->data['form']->id;

		$file=$this->service->files->get($id);            
		
		
		$url=$file['alternateLink'];
	        $_url=explode('?',$url);
		$url=preg_replace('~/edit$~','/viewform',$_url[0]);
		
		$lastMod=strtotime($file['modifiedDate']);
		
		if (!isset($this->data['lastFetch']) || $this->data['lastFetch'] < $lastMod)
		{
		    $this->data['lastFetch']=Bootstrap::$main->now;
		    $this->data['html']=file_get_contents($url);
		
		    $this->save();
		
		}
		//mydie($file,$lastMod);
	    
	    } catch (Exception $e) {
		$this->data['error'] = $e->getMessage();
	    }
	    
	    
	    if (isset($this->data['html']) && $this->data['html'])
	    {
		$forms=array();
		$html=$this->data['html'];
		while(true)
		{
		    $pos=strpos(strtolower($html),'<form');
		    if (!$pos) break;
		    $html=substr($html,$pos);
		    $pos=strpos(strtolower($html),'</form>');
		    $forms[]=substr($html,0,$pos+7);
		    $html=substr($html,$pos+8);
		    
		}
		$this->data['form_html']=$forms[0];
	    }
   	    
	}

    }

    public function init()
    {
        $this->client=Google::getUserClient(null,false,'drive');
        $this->service = Google::getDriveService($this->client);
            
        
        parent::init();
    }
}
