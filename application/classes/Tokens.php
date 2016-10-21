<?php
class Tokens
{
    public $webpage, $webtd;
    public $mode;
    public $page;
    protected $_ob_buffer_level = 0;
    protected $_ob_buffer = '', $_ob_vars = null;
    protected $root;

    protected $naglowek_h1;

    /**
     * @var bool
     */
    public $loadJQuery = false;
    public $loadMoreLibs;
    protected $_jQueryKamLoaded=false;

    public function __get($name)
    {
        switch ($name) {
            case 'html':
            case 'include':
            case 'php':
                return $this->_include_file();

            default:
                if (method_exists($this, $name)) return $this->$name();

                return 'Unknown field ' . $name;
        }
    }

    public function loadjquerykam()
    {
        $this->loadJQuery=true;
        return '';
    }
    
    
    
    public function init($r)
    {

        $this->root = $r;
        $this->reset();
    }

    public function reset()
    {
        $this->naglowek_h1 = array();
        $this->loadJQuery = false;
        $this->loadMoreLibs = array('js'=>false,'css'=>false);
        $this->_jQueryKamLoaded=false;
    }

    
    public function loadLibs($lib,$type)
    {
        $lib=preg_replace('~/[^/\.]+/\.\./~','/',$lib);
        
        $this->loadMoreLibs[$type][$lib] = $lib;
    }
    
    public function globals()
    {
        return array();
    }

    protected function include_path()
    {
        return Bootstrap::$main->session('include_path');
    }

    protected function pages_path()
    {
        return Bootstrap::$main->session('pages_path');
    }

    protected function _include_file($html = '')
    {
        $config = Bootstrap::$main->getConfig();

        $static_include = $config['security']['allow_td_module_execute'] && ($this->mode > PAGE_MODE_PURE || $this->webtd['staticinclude']);

        if (!$html) $html = trim($this->webtd['html']);

        if ($static_include && $this->webtd['ob'] & 1 && $this->mode < PAGE_MODE_EDIT) {
            $this->_ob_buffer_level++;
        }
        $ret = false;

        if (strlen($html)) {

            $debug_id = Debugger::debug(null, 'include(' . $html . ')');

            $session = Bootstrap::$main->session();

            $param = "more=" . urlencode($this->webtd['more_link']);
            $param .= "&more_link=" . urlencode($this->webtd['more_link']);
            $param .= "&page=" . $this->webpage['id'];
            $param .= "&lang=" . $this->webpage['lang'];
            $param .= "&cos=" . $this->webtd['cos'];
            $param .= "&next=" . urlencode($this->webtd['next_link']);
            $param .= "&next_link=" . urlencode($this->webtd['next_link']);
            $param .= "&next_sign=" . urlencode(strstr($this->webtd['next_link'], '?') ? '&' : '?');
            $param .= "&size=" . urlencode($this->webtd['size']);
            $param .= "&class=" . urlencode($this->webtd['class']);
            $param .= "&costxt=" . urlencode($this->webtd['costxt']);
            $param .= "&width=" . urlencode($this->webtd['width']);
            $param .= "&title=" . urlencode($this->webtd['title']);
            $param .= "&self_link=" . urlencode($this->webtd['self_link']);
            $param .= "&self=" . urlencode($this->webtd['self_link']);
            $param .= "&sid=" . urlencode($this->webtd['sid']);
            $param .= "&home_link=" . urlencode($this->page_href(0));
            
            if ($this->mode>0 || $static_include) {
                $param .= "&IMAGES=" . urlencode($session['template_images']);
                $param .= "&UIMAGES=" . urlencode($session['uimages']);
                $param .= "&UFILES=" . urlencode($session['ufiles']);
            }
            
            $param .= "&template_images=" . urlencode($session['template_images']);
            $param .= "&uimages=" . urlencode($session['uimages']);
            if ($this->webtd['xml']) $param .= "&xml=" . urlencode($this->webtd['xml']);
            if (!$this->mode) $param .= "&INCLUDE_PATH=" . urlencode($session['include_path']);

            
            $file2include = $html;
            if ($this->mode == PAGE_MODE_EDIT && $this->webtd['page_id'] >= 0 || $this->mode == PAGE_MODE_EDITHF && $this->webtd['page_id'] < 0) {
                $file2include = str_replace(basename($html), '.' . basename($html), $html);
            }

            if ($static_include) {
                $uincludes = $session['uincludes'];
                $path = $uincludes . '/' . $file2include;

                $param .= "&page=" . $this->webpage['id'];
                $param .= "&ver=" . $session['ver'];
                $param .= "&lang=" . $session['lang'];
                $param .= "&pagetype=" . urlencode($this->webpage['type']);
                $param .= "&pagekey=" . urlencode($this->webpage['pagekey']);
                $param .= "&tree=" . urlencode($this->webpage['tree']);
                if ($this->webpage['next']) $param .= "&nextpage=" . urlencode($this->page_href($this->webpage['next']));
                if ($this->webpage['prev'] >= 0) $param .= "&prevpage=" . urlencode($this->page_href($this->webpage['prev']));

                
                ob_start();
                if (file_exists($path)) {
                    /* Backward compatibility */
                    $KAMELEON_MODE = $this->mode ? true : false;
                    $WEBTD = new stdClass();
                    foreach ($this->webtd AS $k => $v) $WEBTD->$k = $v;
                    global $WEBPAGE;
                    $WEBPAGE = new stdClass();
                    foreach ($this->webpage AS $k => $v) $WEBPAGE->$k = $v;
                    $page = $this->page;
                    $_backward_comp_cwd = getcwd();
                    chdir($uincludes);
                    parse_str($param);

                    $adodb = $kameleon_adodb = Bootstrap::$main->getConn();
                    $INCLUDE_PATH = $uincludes;

                    $_backward_comp_error_level = error_reporting();
                    error_reporting(3);
                    /* /Backward compatibility */

                    $config = Bootstrap::$main->getConfig();
                   
                    if (isset($config['webpage']['pre']) && strlen($config['webpage']['pre']) && file_exists($uincludes . '/' . $config['webpage']['pre'])) {
                        include $uincludes . '/' . $config['webpage']['pre'];
                    }
                    if (isset($config['webpage']['action']) && strlen($config['webpage']['action']) && file_exists($uincludes . '/' . $config['webpage']['action'])) {
                        include $uincludes . '/' . $config['webpage']['action'];
                    }
                    
                    include $path;
                    

                    if (isset($config['webpage']['post']) && strlen($config['webpage']['post']) && file_exists($uincludes . '/' . $config['webpage']['post'])) {
                        include $uincludes . '/' . $config['webpage']['post'];
                    }


                    error_reporting($_backward_comp_error_level);
                    chdir($_backward_comp_cwd);

                }
                $ret = @ob_get_contents();
                @ob_end_clean();

            } else {

                $ret='<?php ';
                foreach(explode('&',$param) AS $pair)
                {
                    $pair=explode('=',$pair);
                    $ret.='$'.urldecode($pair[0]).'='."'".addslashes(urldecode($pair[1]))."';";
                }
                $ret.=' if (file_exists($INCLUDE_PATH.\'/' . $html . '\')) include($INCLUDE_PATH.\'/' . $html . '\');?>';
                
                

            }

            Debugger::debug($debug_id);

        }

        if ($static_include && $this->webtd['ob'] & 2 && $this->mode < PAGE_MODE_EDIT) {
            $this->_ob_buffer_level--;
            $this->_ob_vars = get_defined_vars();
        }

        return $ret;

    }

    public function ob($html)
    {
        if ($this->_ob_buffer_level > 0) {
            $this->_ob_buffer .= $html;

            return '';
        }

        if ($this->_ob_vars) {
            $this->_ob_buffer .= $html;
            $ret = GN_Smekta::smektuj($this->_ob_buffer, $this->_ob_vars);
            $this->_ob_buffer = '';
            $this->_ob_vars = null;

            return $ret;
        }

        return $html;
    }

    public function href($href, $variables, $page_target)
    {
        return Bootstrap::$main->kameleon->href($href, $variables, $page_target, $this->page, $this->mode);
    }

    public function page_href($page=0)
    {
        if (isset($this)) return $this->href('', '', $page);

        return Bootstrap::$main->kameleon->href('', '', $page, '', 0);
    }

    protected function widget_news_bgimg_mini()
    {
        $bgimg = trim($this->webtd['bgimg']);
        if (!$bgimg) return;
        $uimages_path = Bootstrap::$main->session('uimages_path');
        if (!file_exists($uimages_path . '/' . $bgimg)) return;
        $mini = str_replace(basename($bgimg), 'mini_' . basename($bgimg), $bgimg);
        if (file_exists($uimages_path . '/' . $mini)) return $mini;

        $config = Bootstrap::$main->getConfig();
        if (Bootstrap::$main->kameleon->min_image($uimages_path . '/' . $bgimg, $uimages_path . '/' . $mini, $config['widgets']['news']['mini']['width'], $config['widgets']['news']['mini']['height'])) {
            return $mini;
        }

        return '';
    }

    public function logo($class = 'sitelogo',$logo_w=0,$logo_h=0)
    {
        $server = new serverModel();
        $logo = $server->option('logo');

        if (!$logo) return;

        if (!file_exists(Bootstrap::$main->session('uimages_path') . "/$logo")) return;

        list($w, $h) = getimagesize(Bootstrap::$main->session('uimages_path') . "/$logo");
        $pinfo = pathinfo(Bootstrap::$main->session('uimages_path') . "/$logo");
        $ext = strtolower($pinfo['extension']);        
        

        $config = Bootstrap::$main->getConfig();
        if (!$logo_w) $logo_w=$config['default']['logo']['width'];
        if (!$logo_h) $logo_h=$config['default']['logo']['height'];
        
        if ($w > $logo_w || $h > $logo_h) {
            $src = Bootstrap::$main->session('uimages_path') . "/$logo";
            $logo2 = $pinfo['filename'].'_' . time() . '.'.$ext;
            $dst = Bootstrap::$main->session('uimages_path') . "/$logo2";
            
            if ( Bootstrap::$main->kameleon->min_image($src, $dst, $logo_w, $logo_h, true) )
            {
                $server->option('logo', $logo2);
                $logo=$logo2;
            }
        }

        return '<a class="' . $class . '" style="background-image: url(\'' . Bootstrap::$main->session('uimages') . "/" . $logo . '\');" href="' . $this->href('', '', 0) . '"></a>';
    }
    
    
    public function logoUrl()
    {
        $server = new serverModel();
        $logo = $server->option('logo');
        
        if ($logo) return Bootstrap::$main->session('uimages').'/'.$logo;
    }

    public function str2img_style($txt, $filename, $fontsize, $fontface, $posx, $posy, $color, $wrap, $width, $height, $time2compare)
    {
        $file = Bootstrap::$main->kameleon->str2img($txt, $filename, $fontsize, $fontface, $posx, $posy, $color, $wrap, $width, $height, $time2compare);

        $session = Bootstrap::$main->session();
        list($w, $h) = getimagesize($session['uimages_path'] . "/" . $filename);

        $url = $session['uimages'] . "/$filename";
        if ($session['editmode']) $url .= "?t=" . time();
        $ret = "height: ${h}px; width: ${w}px; background-image: url('$url')";

        return $ret;
    }

    public function modulo($value, $mod, $plus = 0)
    {
        return (($value - $plus) % $mod) + $plus;
    }

    public function breadcrumb()
    {
        $webpage_tree = explode(":", $this->webpage['tree']);
        $webpage_tree[] = $this->webpage['id'];
        $webpage = new webpageModel();
        $path = '';
        for ($i_tree = 0; $i_tree < (count($webpage_tree)) && is_array($webpage_tree); $i_tree++) {
            if (!strlen($webpage_tree[$i_tree])) continue;
            $parent_page = $webpage_tree[$i_tree];

            $page = $webpage->getOne($parent_page);
            if ($page['nositemap']) continue;

            $title = strlen($page['title_short']) ? $page['title_short'] : $page['title'];

            if (!$parent_page) $title = Tools::translate('Home page');

            $href = $this->href('', '', $parent_page);
            if (!$page['hidden'] && strlen($title) > 0) $title = "<a href=\"$href\">$title</a>";
            $path .= $title;
            if ($i_tree < (count($webpage_tree) - 1) && strlen($title) > 0) $path .= ">> ";
        }

        return $path;
    }

    public function trans($txt)
    {
//        return Tools::translate($txt);

        return call_user_func_array(array(Bootstrap::$main->translate, 'trans'), func_get_args());
    }

    
    
    public function error()
    {
        
        $session = Bootstrap::$main->session();
        $type = isset($session['error_type']) ? $session['error_type'] : 0;

        if (!$type) return '';

        $params = isset($session['error_params']) ? $session['error_params'] : array();

        if (!is_array($params)) $params = array($params);

        $error = isset($session['error']) ? $session['error'] : '';

        $link = isset($session['error_link']) ? $session['error_link'] : '';

        array_unshift($params, $error);

        $a = '';
        $noa = '';
        if ($link) {
            if ($link[0] != '/' && substr($link, 0, 4) != 'http') {
                $link = Bootstrap::$main->getRoot() . $link;
            }
            $a = '<a href="' . $link . '">';
            $noa = '</a>';
        }

        Bootstrap::$main->error();
        
        $ret=call_user_func_array(array($this, 'trans'), $params);
        
        if (!$ret) return '';

        return '<div class="km_error_' . $type . '"><span class="glyphicon glyphicon-warning-sign"></span>' . $a . $ret . $noa . '</div>';

    }

    public function mydie($data)
    {
        mydie($data);
    }

    
    public function h1($start=1,$other=6)
    {
        if ($this->webtd['page_id'] != $this->webpage['id']) $start=$other;
        
        for ($i=$start;$i<10;$i++)
        {
            if (!isset($this->naglowek_h1[$i])) $this->naglowek_h1[$i]=0;
            if ($this->naglowek_h1[$i] < $i*($i+1) )
            {
                $this->naglowek_h1[$i]++;
                return 'h'.$i;
            }
            
        }
        
        return 'h9';
        
        if ($this->naglowek_h1==0) $this->naglowek_h1=$start;
        
        $index=1+floor($this->naglowek_h1/2);
        
        //Liczba trójkątna 
        $n=1;
        while( $index > ($n*($n+1))/2 ) $n++;
        $this->naglowek_h1++;
        return 'h'.$n;
    }
    
    protected function box_title()
    {
        $itemtype='';
        if ($this->webtd['itemtype']) $itemtype=' itemprop="name"';
        return '<'. $this->h1() . ' class="title"'.$itemtype.'>' . $this->webtd['title'] . '</' . $this->h1() . '>';
    }

    /**
     * @param string $template
     * @return string
     */
    public function template_name($template)
    {
        if (substr($template,0,6)=='media/')
        {
            $server=new serverModel();
            $server->find_one_by_nazwa(substr($template,6));
            
            if ($server->nazwa_long) return $server->nazwa_long;
        }
        
        return basename($template);
    }
    

    /**
     * @param string $template
     * @return string
     */
    public function template_url($template)
    {
        if (substr($template,0,6)=='media/')
        {
            $server=new serverModel();
            $server->find_one_by_nazwa(substr($template,6));
            
            return $server->http_url;
        }
        if (file_exists(APPLICATION_PATH.'/templates/'.$template.'/.url')) return file_get_contents(APPLICATION_PATH.'/templates/'.$template.'/.url');
    }


    
    /**
     * @param string $template
     * @return string
     */
    public function template_author($template,$default='Webkameleon')
    {
        if (substr($template,0,6)=='media/')
        {
            $server=new serverModel();
            $server->find_one_by_nazwa(substr($template,6));
            
            
            if ($server->nazwa)
            {
                return $server->creator()->fullname;
            }
        }
        
        return $default;
    }    

    /**
     * @return bool
     */
    public function is_admin()
    {
        $user = Bootstrap::$main->session('user');

        return $user && $user['admin'] == 1;
    }

    /**
     * @param array $server
     * @return string
     */
    public function get_expire_info(array $server)
    {
        $html = array();

        if ($server['nd_expire']) {
            $days = floor(($server['nd_expire'] - time()) / 86400);

            $html[] = '<span class="left">';
            if ($days >= 0 && $days <= 30) {
                $html[] = Tools::translate('Days remaining') . ': ' . $days;
            } else {
                $html[] = Tools::translate('Expires at') . ' ' . Tools::date($server['nd_expire']);
            }
            $html[] = '</span>';
        }

        return implode('', $html);
    }

    /**
     * @param array $server
     * @return string
     */
    public function get_wizard_info(array $server)
    {
        if (!$server['nd_expire']) return '';
        $html = array();
        $html[] = self::get_expire_info($server);
        $html[] = '<span class="right">';
        $html[] = sprintf('<a href="%s/%s">%s</a>', Tools::url('payment/get'), $server['id'], Tools::translate($server['nd_last_payment'] ? 'Extend' : 'Pay'));
        $html[] = '</span>';

        return implode('', $html);
    }

    public function get_langs_ul_html($current = null)
    {
        if ($current == null)
            $current = Bootstrap::$main->session('lang');

        $html  = '';
        $html .= '<ul>';
        foreach (Bootstrap::$main->session('langs_used') as $lang) {
            $_GET['lang'] = $lang;
            $html .= '<li' . ($lang == $current ? ' class="active"' : '') . '><a href="?' . http_build_query($_GET) . '">' . Tools::translate($lang) . '</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    public function modulo_ten($value)
    {
        return $value % 10;
    }

    public function modulo_twenty($value)
    {
        return $value % 20;
    }

    public function modulo_thirty($value)
    {
        return $value % 30;
    }


    public function code_change($html,$style=null)
    {
        if (!$style) return $html;

        return Html::$style($html,$this->webpage['id']); 
    }
    
    public function jQueryKamLoaded ()
    {
        $ret=$this->_jQueryKamLoaded;
        $this->jQueryKamLoaded=true;
        return $ret;
    }
    
    protected $inherited_pageclass_cache;
    
    public function inherited_pageclass()
    {
        $wp=$this->webpage;
        if (isset($this->inherited_pageclass_cache[$wp['id']])) return $this->inherited_pageclass_cache[$wp['id']];
        
        if ($wp['class'])
        {
            $this->inherited_pageclass_cache[$wp['id']]=$wp['class'];
            return $wp['class'];
        }
        $webpage=new webpageModel();

        while (true)
        {
            if (!$wp['id']) return;
            $wp=$webpage->getOne($wp['prev']);
            if ($wp['class'])
            {
                $this->inherited_pageclass_cache[$wp['id']]=$wp['class'];
                return $wp['class'];
            }
        }
        
    }

    
    
}
