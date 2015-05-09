<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class facebookWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'facebook';

    /**
     * @var
     */
    public $pluginUrl;
    public $hprc=false;

    public function run()
    {
        if (!$this->data['href'])
        {
            $server=Bootstrap::$main->session('server');
            $path=Bootstrap::$main->session('path');
            if (substr($server['http_url'],-1)!='/') $server['http_url'].='/';
            $href=$server['http_url'].$path['pageprefix'].$this->webpage['file_name'];
            $href=preg_replace('/index\.[a-zA-Z0-9]+$/','',$href);
            $this->data['href']=$href;
        }
       
        $params = array();
        $plugin = 'like';

        switch ($this->data['type'])
        {
            case 'button':
                $params = array(
                    'href', 'width', 'layout', 'action', 'colorscheme', 'send'
                );
                break;

            case 'box':
                $params = array(
                    'href', 'width', 'height', 'colorscheme', 'show_faces', 'show_border', 'header', 'stream'
                );
                $plugin = 'like_box';
                break;

            case 'comments':
                $params = array(
                    'href', 'width', 'height', 'colorscheme', 'num_posts', 'order_by'
                );
                $plugin = 'comments';
                break;
        }

        

        
        if ($this->data['width']=='100%' || !$this->data['width']) 
        {
            $this->hprc=true;
            $this->data['width']='200';
            
        }
        
        
        
        $params = array_intersect_key($this->data, array_flip($params));
        
        
        $this->pluginUrl = 'http://www.facebook.com/plugins/' . $plugin . '.php?' . http_build_query($params);
        
    }
}