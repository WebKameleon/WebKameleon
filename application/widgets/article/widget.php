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
			
			if (strlen($this->webtd['plain']) && $this->webtd['page_id']==$this->webpage['id']) {
				$webpage=new webpageModel($this->webpage['sid']);
				if (!$webpage->og_image) $webpage->og_image=$this->webtd['bgimg'];
				if (!$webpage->og_desc) {
					$desc=$this->webtd['plain'];
					$pos=strpos(strtolower($desc),'</p>');
					if (!$pos) $pos=strpos(strtolower($desc),'<br');
					if (!$pos) $pos=strpos(strtolower($desc),'</div');
					if ($pos) $desc=substr($desc,0,$pos);
					$webpage->og_desc=Tools::nohtml($desc);
				}
				$webpage->save();
				
			}
	    
        }
		
		$this->normalImgUrl=parent::getImagesUrl();
		
		if (isset($this->webtd['attachment']) && $this->webtd['attachment'] ) {
			$this->attachment_class=strtolower(end(explode('.',$this->webtd['attachment'])));
		}
		
		if(!$this->webtd['nd_update'] && $this->webtd['page_id']>0 && $this->webtd['page_id']==$this->webpage['id'] && $this->webtd['title']!=$this->webpage['title']) {
			
			$webtd=new webtdModel($this->webtd['sid']);
			$tds=$webtd->getAll([$this->webtd['page_id']]);
			if (count($tds)==1) {
				$webtd->title=$this->webpage['title'];
				$webtd->save();
			}
		
		}
		
		$cat=new webcatModel();
		$this->cats = $cat->cats($this->webtd['sid']);
		if (count($this->cats)==0)
			$this->cats=0;
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
