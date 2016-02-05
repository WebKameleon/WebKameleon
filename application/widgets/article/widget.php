<?php

class articleWidget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'article';
	protected $fancybox;

    /**
     * @return string
     */
    public function getImagesUrl()
    {
        return $this->getGfxUrl() . '/' . $this->thumbDir;
    }


    public function run()
    {
		parent::run();
		
        if (isset($this->webtd['bgimg']) && $this->webtd['bgimg'] ) {
            
			$this->checkImage($this->webtd['bgimg']);
            $this->checkThumb($this->webtd['bgimg']);
			
			if ($this->fancybox) Bootstrap::$main->tokens->loadJQuery = true; 
	    
        }
		
		$this->normalImgUrl=parent::getImagesUrl();
		
		if (isset($this->webtd['attachment']) && $this->webtd['attachment'] ) {
			$this->attachment_class=strtolower(end(explode('.',$this->webtd['attachment'])));
		}
        
    }

    public function init()
    {
		parent::init();
		
		$this->crop=true;

		$this->fancybox=Bootstrap::$main->getConfig('widgets.article.fancybox');
		
        if ($this->fancybox && isset($this->webtd['bgimg']) && $this->webtd['bgimg'] ) {
            require_once __DIR__ . '/../common/widget.php';
            commonWidget::loadFancybox();
        }
        
    }
}
