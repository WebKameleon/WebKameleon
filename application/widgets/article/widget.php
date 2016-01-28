<?php

class articleWidget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'article';

    /**
     * @return string
     */
    public function getImagesUrl()
    {
        return $this->getGfxUrl() . '/' . $this->thumbDir;
    }

    public function update()
    {
        if (isset($this->webtd['bgimg'])) {

            $this->checkThumb($this->webtd['bgimg']);
            $this->checkImage($this->webtd['bgimg']);
            
        }

        $this->data['__saved__'] = 1;
    }

    public function run()
    {
        if (isset($this->webtd['bgimg']) && $this->webtd['bgimg'] ) {
            Bootstrap::$main->tokens->loadJQuery = true;

            $this->checkThumb($this->webtd['bgimg']);
            $this->checkImage($this->webtd['bgimg']);	    
	    
        }
	if (isset($this->webtd['attachment']) && $this->webtd['attachment'] ) {
	    $this->attachment_class=strtolower(end(explode('.',$this->webtd['attachment'])));
	}
        parent::run();
    }

    public function init()
    {
		$this->crop=true;
        
        if (isset($this->webtd['bgimg']) && $this->webtd['bgimg'] ) {
            require_once __DIR__ . '/../common/widget.php';
            commonWidget::loadFancybox();
        }
        
        parent::init();
    }
}
