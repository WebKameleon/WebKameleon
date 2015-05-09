<?php

class shareWidget extends Widget
{
    public $name = 'share';

    /**
     * @var array
     */
    public $title;
    public $media = array(
        'facebook' => array( 'url'=>'https://www.facebook.com/sharer/sharer.php?u={url}&t='),
        'twitter' => array( 'url'=>'https://twitter.com/intent/tweet?source={url}&text=:%20{url}'),
        'gplus' => array( 'url'=>'https://plus.google.com/share?url={url}'),
        'tumblr' => array( 'url'=>'http://www.tumblr.com/share?v=3&u={url}&t=&s='),
        'pinterest' => array( 'url'=>'http://pinterest.com/pin/create/button/?url={url}&description='),
        'getpocket' => array( 'url'=>'https://getpocket.com/save?url={url}&title='),
        'reddit' => array( 'url'=>'http://www.reddit.com/submit?url={url}&title='),
        'linkedin' => array( 'url'=>'http://www.linkedin.com/shareArticle?mini=true&url={url}&title=&summary=&source={url}'),
        'pinboard' => array( 'url'=>'https://pinboard.in/popup_login/?url={url}&title=&description='),
        'mail' => array('url'=>'mailto:?subject=&body={url}'),
    );

    public function init()
    {
        parent::init();

        $this->title=Tools::translate('Share ...');

        
        foreach($this->media AS $k=>&$media)
        {
            $media['checked']=isset($this->data['media'][$k]) && $this->data['media'][$k]; 
            $media['name']=Tools::translate($k);

        }

        
    }
    
    public function run()
    {
        parent::run();
        
        $server=Bootstrap::$main->session('server');
        $path=Bootstrap::$main->session('path');

        foreach($this->media AS $k=>&$media)
        {
          
            $url=$server['http_url'];
            if (substr($url,-1)!='/') $url.='/';
            $url.=$path['pageprefix'];
            $url.=preg_replace('/index.(php}htm|html)$/','',$this->webpage['file_name']);
            $media['url'] = str_replace('{url}',urlencode($url),$media['url']);
            $media['type']=$k=='mail'?'mail':'popup';
        }        
        
    }


}