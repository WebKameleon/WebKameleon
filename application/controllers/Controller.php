<?php

class Controller
{
    protected $viewTemplate;
    protected $viewLayout;
    protected $redirect;
    protected $fullpath;
    protected $id;

    public function __construct($fullpath = null, $id = null)
    {
        $this->fullpath = $fullpath;
        $this->id = $id;
        
        $this->viewLayout=APPLICATION_PATH . '/views/layout.html';
        $this->init();
    }
    
    public function __call($name,$args) {
            header('HTTP/1.0 404 Not Found');
            die;        
    }

    protected function init()
    {

    }

    public function setViewTemplate($view)
    {
        $this->viewTemplate = $view;
    }

    public function getViewLayout()
    {
        return $this->viewLayout;    
    }
    
    public function getViewTemplate()
    {
        return $this->viewTemplate;
    }

    public function redirect($r = null,$anchor=false)
    {        
        if (!$r) return $this->redirect;

        if ($this->_hasParam('return_url') && $this->_getParam('return_url')) {
            $this->redirect = base64_decode($this->_getParam('return_url'));
        } elseif (substr($r, 0, 7) == 'http://' || substr($r, 0, 8) == 'https://') {
            $this->redirect = $r;
        } elseif ($r[0] == '/') {
            $this->redirect = $r;
        } else {
            $this->redirect = Bootstrap::$main->getRoot() . $r;
        }
        
        if ($anchor) $this->redirect.='#'.$anchor;
    
        return $this->redirect;
    }

    public function redirectBack($anchor='')
    {
        $this->redirect(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:$_SERVER['REQUEST_URI'],$anchor);
    }

    public function addKameleonTags($html, $mode, $level = 'index', $vars = null)
    {
        
        while (strstr($html, $randtoken = md5(time() . rand(0, 1000)))) ;
        $randtoken = 'x' . $randtoken . 'x';

        foreach (array(
            'pre'=>'',
            'afterheaderbegin'=>array('(<head>|<head [^>]*>)', '\\1'.$randtoken),
            'beforeheaderend'=>array('(<\/head>)', $randtoken.'\\1'),
            'beforepagebodybegin'=>array('(\{with:body\})', $randtoken.'\\1'),
            'afterpagebodyend'=>array('(\{endwith:body\})', '\\1'.$randtoken),
            'beforepageheaderbegin'=>array('(\{with:header\})', $randtoken.'\\1'),
            'afterpageheaderend'=>array('(\{endwith:header\})', '\\1'.$randtoken),
            'beforepagefooterbegin'=>array('(\{with:footer\})', $randtoken.'\\1'),
            'afterpagefooterend'=>array('(\{endwith:footer\})', '\\1'.$randtoken),             
            'afterbodybegin'=>array('(<body[^>]*>)', '\\1'.$randtoken),
            'beforebodyend'=>array('(<\/body>)', $randtoken.'\\1'),
            'post'=>''
        ) AS $check => $reg) {
            $path = VIEW_PATH . '/replace/' . $level . '.' . $check . '-' . $mode . '.html';

            if (file_exists($path)) {
                if ($check == 'pre') {
                    $html = $randtoken . $html;
                } elseif ($check == 'post') {
                    $html .= $randtoken;
                } else {
                    $html = preg_replace('/' . $reg[0] . '/i', $reg[1], $html);
                }
                
                //W3C workaround
                if ($check=='afterheaderbegin')
                {
                    $nohead=strpos(strtolower($html),'</head>');
                    $afterheaderbegin=strpos($html,$randtoken);
                    $charset=strpos(strtolower($html),'charset');
                    
                    if ($charset && $charset<$nohead)
                    {
                        while ($charset<$nohead && $html[$charset]!='>') $charset++;
                        
                        if ($charset<$nohead)
                        {
                            $html=str_replace($randtoken,'',$html);
                            $charset-=strlen($randtoken)-1;
                            
                            $html=substr($html,0,$charset).$randtoken.substr($html,$charset+1);
                        }
                            
                    }
                    
                    
                }
                
                if (is_array($vars)) $html=str_replace($randtoken,GN_Smekta::smektuj(file_get_contents($path),$vars), $html);
                else $html = str_replace($randtoken, file_get_contents($path), $html);
            }
        }
        
        return $html;
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function _hasParam($name)
    {
        return $this->_getParam($name) !== null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function _getParam($name, $defaultValue = null)
    {
        $value = @$_REQUEST[$name];
        if (($value === null || $value === '') && ($defaultValue !== null)) {
            $value = $defaultValue;
        }
        return $value;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function trans($text)
    {
//        return Tools::translate($text);

        return call_user_func_array(array(
            Bootstrap::$main->translate, 'trans'
        ), func_get_args());
    }
    
    public function finish(&$data, $output)
    {

    }
    
    public function postRender(&$data)
    {
        return $data;
    }
    
    protected function _log()
    {
        $name=str_replace('Controller','',get_class($this));
        if (!$name) return;
        $name.='_controller';
        
        Tools::log($name,func_get_args());
    }
    
    protected function getConfigChangeStyles($for)
    {
        $config=Bootstrap::$main->getConfig();
        $style=$config['style'];
        
        foreach ($style AS $k=>$s)
        {
            if (isset($s['for']) && $s['for']!='*')
            {
                if (!in_array($for,explode(',',$s['for']))) unset($style[$k]);
            }
        }
        $config['style']=$style;
        return $config;
    }
    
}