<?php

class bgslideWidget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'bgslide';


    protected static $first=array();   

    public function checkImage($filename)
    {
        $w=$this->data['width'];
        $h=$this->data['height'];
        
        //echo "$w x $h <br>";
        
        return Tools::check_image($filename, $this->getUimagesPath(), $this->getImagesPath(), $w, $h, 0777, true);
    }

    /**
     * @param string $filename
     * @return string
     */
    public function checkThumb($filename)
    {
        $h=$this->data['thumb_height'];
        $w=0;
        return Tools::check_image($filename, $this->getUimagesPath(), $this->getThumbsPath(), $w, $h, 0777, true);
    }

    
    protected function checkLinks(array $links)
    {
        return parent::checkLinks($links);
        
        //mydie('www');
    }
    
    
    public function run()
    {

        Bootstrap::$main->tokens->loadJQuery = true;

        parent::run();
        
        $this->data['first']=isset(self::$first[$this->webpage['sid']])?0:1;
        self::$first[$this->webpage['sid']]=1;
    
        $this->webpage['kmw_bgslide_body'] = true;
    }

}
