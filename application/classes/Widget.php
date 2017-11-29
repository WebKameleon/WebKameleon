<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

abstract class Widget
{
    /**
     * @var array
     */
    public $defaults = array();

    /**
     * @var array
     */
    public $data = array();

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $parent;

    /**
     * @var array
     */
    public $webtd;
    
    /**
     * @var array
     */
    public $webpage;    

    /**
     * @var integer
     */
    
    public $page;    

    /**
     * @var array
     */    
    
    public $js_options = array();

    /**
     * @var array
     */
    public $js_options_keys = array();

    /**
     * @var int
     */
    protected $dir_mode = 0755;

    /**
     * @var string
     */
    public static $widget_dir = 'widgets';

    /**
     * @var string
     */
    public $gfx_dir = 'gfx';

    /**
     * @var bool
     */
    public $show_title = true;

    /**
     * @var bool
     */
    public $edit_module = true;

    /**
     * @var bool
     */
    public $isLoaded = false;

    /**
     * @var array
     */
    public static $loaded_widgets = array();

    /**
     * @var array
     */
    protected $libs_to_load = array();

    /**
     * @var string
     */
    public $widget_images;

    /**
     * @var
     */
    public $widget_libs;
    
    
    public $mode;
    

    /**
     * @param string $widgetName
     * @param array|string $widgetData
     * @return Widget
     */
    public static function factory($widgetName, $widgetData = null, array $widgetWebtd = null, $page=0)
    {
        $widgetClass = str_replace('/','_',$widgetName . 'Widget');
        require_once WIDGETS_PATH . DIRECTORY_SEPARATOR . $widgetName . '/widget.php';
        /**
         * @var Widget $widget
         */
        $widget = new $widgetClass;

        if ($widgetData) {
            if (is_string($widgetData)) {
                $widgetData = unserialize(base64_decode($widgetData));
            }

            if (is_array($widgetData)) {
                $widget->data = $widgetData;
            }
        }
        $widget->page=$page;
        $widget->webtd = $widgetWebtd;
        $widget->init();

        
        
        return $widget;
    }

    /**
     * @param array $webtd
     * @return Widget
     */
    public static function factoryWebtd(array $webtd,$page=0)
    {
        $widget = self::factory($webtd['widget'], $webtd['widget_data'] ? : $webtd['web20'] ? : null, $webtd, $page);

        return $widget;
    }

    /**
     * @param string $widgetName
     * @return bool
     */
    public static function exists($widgetName)
    {
        return file_exists(WIDGETS_PATH . DIRECTORY_SEPARATOR . $widgetName);
    }

    public function init()
    {
        $this->defaults = $this->getFromConfig();
        $this->_applyDefaultData();
        $this->_checkIsLoaded();

        $this->widget_images = Bootstrap::$main->session('template_images') . '/' . self::$widget_dir . '/' . $this->name;

      
        
    }

    public function run()
    {
        $this->loadCSS($this->name . '.css');
        if ($this->webtd['menu_id'] && isset($this->data['menu_id']) && $this->data['menu_id']!=$this->webtd['menu_id']) {
            $this->data['menu_id']=$this->webtd['menu_id'];
            $this->save();
            
        }
    }

    public function edit()
    {
        $webpage=new webpageModel();
        $this->webpage = $webpage->getOne($_GET['page']);
    }

    public function update()
    {
        $this->data['__saved__'] = 1;
        
    }

    public function delete()
    {

    }

    protected function _checkIsLoaded()
    {
        $name = $this->parent ? : $this->name;

        if (isset(Widget::$loaded_widgets[$name])) {
            $this->isLoaded = true;
        } else {
            Widget::$loaded_widgets[$name] = true;
        }
    }

    protected function _applyDefaultData()
    {
        
        if (isset($this->defaults['height']) && strstr($this->defaults['height'],'x') && isset($_GET['w']))
        {
            $w=is_numeric($this->defaults['width'])?$this->defaults['width']:$_GET['w'];
            $h=explode('x',$this->defaults['height']);
            if ($h[0] && $h[1])
            {
                $this->defaults['height'] = round(($h[1]*$w)/$h[0]);
            }
        }
        
    
        
        if ($this->defaults) {
            foreach ($this->defaults as $k => $v) {
                if (!is_array($this->data) || !array_key_exists($k, $this->data)) {
                    $this->data[$k] = $v;
                }
            }
        }

        if (!array_key_exists('__saved__', $this->data)) {
            $this->data['__saved__'] = 0;
        }

        $this->js_options = json_encode(
            $this->getJsData(), JSON_NUMERIC_CHECK
        );
    }
    
    public function edit_html()
    {
        return 'edit.html';
    }

    /**
     * @return null|string
     */
    public function getEditView()
    {
        $path = WIDGETS_PATH . DIRECTORY_SEPARATOR . $this->name . '/'.$this->edit_html();
        //mydie($path);
        return file_exists($path) ? $path : null;
    }

    /**
     * @return string
     */
    public function getUimagesPath()
    {
        return Bootstrap::$main->session('uimages_path');
    }

    /**
     * @return string
     */
    public function getUimagesUrl()
    {
        return Bootstrap::$main->session('uimages');
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getUimageUrl($filename)
    {
        return $this->getUimagesUrl() . '/' . $filename;
    }

    /**
     * @return string
     */
    public function getWidgetPath()
    {
        $path = $this->getUimagesPath() . DIRECTORY_SEPARATOR . self::$widget_dir . DIRECTORY_SEPARATOR . $this->name;
        Tools::check_if_exists($path, $this->dir_mode);
        return $path;
    }

    public function getWidgetUrl()
    {
        return $this->getUimagesUrl() . '/' . self::$widget_dir . '/' . $this->name;
    }

    /**
     * @return string
     */
    public function getGfxPath()
    {
        $path = $this->getWidgetPath() . DIRECTORY_SEPARATOR . $this->gfx_dir;
        Tools::check_if_exists($path, $this->dir_mode);
        return $path;
    }

    /**
     * @return string
     */
    public function getGfxUrl()
    {
        return $this->getWidgetUrl() . '/' . $this->gfx_dir;
    }

    /**
     * @return array
     */
    public function getJsData()
    {
        return array_intersect_key($this->data, array_flip($this->js_options_keys));
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getFromConfig($option = null)
    {
        $options = Bootstrap::$main->getConfig('widgets');
        
        
        if ($option)
            return @$options[$this->name][$option];

        return @$options[$this->name];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $arr = (array) $this;
        return $arr;
    }

    /**
     * @param string $js
     */
    public function loadJS($js)
    {
        Bootstrap::$main->tokens->loadLibs($this->getLibUrl($js), 'js');
    }

    /**
     * @param string $css
     */
    public function loadCSS($css)
    {

        Bootstrap::$main->tokens->loadLibs($this->getLibUrl($css), 'css');
    }

    /**
     * @return string
     */
    public function loadLibs()
    {
        //do usunięcia
    }

    /**
     * @param $lib
     * @return string
     */
    public function getLibUrl($lib)
    {
        if (filter_var($lib, FILTER_VALIDATE_URL) !== false || substr($lib,0,2)=='//') return $lib;
        return $this->widget_images . '/' . $lib;
    }
    
    
    protected function check_scope($scope,$page=0)
    {
        $user=Bootstrap::$main->session('user');
        $tokens=json_decode($user['access_token'],true);
        
        if (isset($tokens[$scope]) && $tokens[$scope]) return true;
        Bootstrap::$main->redirect('scopes/'.$scope.'?setreferpage='.$page);
        
    }
    
    
    protected function save()
    {
        if (!$this->webtd['sid']) return;
            
        $webtd = new webtdModel($this->webtd['sid']);
        $webtd->widget_data = base64_encode(serialize($this->data));
        $webtd->save();        
    }
    
    
    public function default_html($html_path)
    {
        $name=str_replace('/','_',$this->name);
        $file=$html_path.'/widget_' . $name . '.html';
        if (file_exists($file)) return $file;
        
        return WIDGETS_PATH.'/'.$this->name.'/default.html';
    }
    
    public function default_images_path()
    {
        return WIDGETS_PATH.'/'.$this->name.'/images';
    }
    
    public function default_images_dest()
    {
        return 'widgets/'.$this->name;
    }
}

abstract class imageWidget extends Widget
{
    /**
     * @var string
     */
    public $imageDir = 'normal';

    /**
     * @var string
     */
    public $thumbDir = 'icon';

    /**
     * @var string
     */
    public $imagesUrl;

    /**
     * @var string
     */
    public $thumbsUrl;

    /**
     * @var bool
     */
    public $scale = false;

    /**
     * @var bool
     */
    public $crop = false;

    /**
     * @var weblinkModel
     */
    protected $_weblink;

    public function init()
    {
        parent::init();

        $this->imagesUrl = $this->getImagesUrl();
        $this->thumbsUrl = $this->getThumbsUrl();

        $this->_weblink = new weblinkModel;

        if (!isset($this->webtd['menu']) && isset($this->data['menu_id']) ) {
            $this->webtd['menu'] = $this->_weblink->getAll($this->data['menu_id']);
        }
    }

    public function update($clean = true)
    {
        $images = json_decode($this->data['images'], true);

        
        
        if ($this->data['menu_id'] == -1) {
            $this->data['menu_id'] = $this->_weblink->get_new_menu_id();
            if ($this->webtd) {
                $webtd = new webtdModel;
                $webtd->load($this->webtd);
                $webtd->menu_id = $this->data['menu_id'];
                $webtd->widget_data = base64_encode(serialize($this->data));
                $webtd->save();
            }
        }
        
        $title='';
        if (strlen($this->webtd['page_id']))
        {
            if ($this->webtd['page_id'])
            {
                $webpage=new webpageModel();
                $wp=$webpage->getOne($this->webtd['page_id']);
                $title='/'.($wp['title_short']?:$wp['title']);
            }
            else
            {
                $title='/home';
            }
        }
        $name=$this->name.$title;
        while(strlen($name)>32) $name=mb_substr($name,0,mb_strlen($name,'utf8')-1,'utf8');

        $data1=$this->data;
        
        
        $pri = 1;
        $links = array();


        foreach ($images as $image) {

            $new = !isset($image['sid']);
            if ($new) {
                $link = $this->_weblink->add_link($this->data['menu_id'], $this->name, $image['title']);
            } else {
                $link = $this->_weblink->get($image['sid']);
            }

            
            $this->_weblink->load($link);
            
            $this->_weblink->name = $name;
            
            $this->_weblink->pri = $pri++;
            if ($new) {
                $this->_weblink->img = urldecode($image['url']);
            }
            $this->_weblink->alt = $image['title'] ? : null;
            if (isset($image['titlea'])) $this->_weblink->titlea = $image['titlea'] ? : null;
            if (isset($image['titleb'])) $this->_weblink->titleb = $image['titleb'] ? : null;
            if (isset($image['titlec'])) $this->_weblink->titlec = $image['titlec'] ? : null;
            $this->_weblink->page_target = $image['page'] !== null ? $image['page'] : null;
            $this->_weblink->href = $image['href'] ? : null;
            
            
            $this->_weblink->save();
            
            

            $links[$link['sid']] = $this->_weblink->data();
        }

        if ($clean && $this->webtd['menu_id']) {
            foreach ($this->webtd['menu'] as $link) {
                if (!array_key_exists($link['sid'], $links)) {
                    $this->deleteImage($link['img']);
                    $this->_weblink->remove($link['sid']);
                }
            }
        }

        

        $this->checkLinks($links);
        
        if ($data1!=$this->data) {
            $this->save();            
        }
        
        

        parent::update();
    }

    /**
     * @param array $links
     */
    protected function checkLinks(array $links=array())
    {
        if (!count($links)) {
            $weblink=new weblinkModel();
            if ($this->webtd['menu_id']) $links=$weblink->getAll($this->webtd['menu_id']);
        }
        
        foreach ($links as $link) {
            
            if (!isset($link['type']) || $link['type']!=$this->webtd['type']) {
                $weblink=new weblinkModel($link['sid']);
                $weblink->type = $this->webtd['type'];
                $weblink->save();
            }
            
            $this->checkImage($link['img']);
            $this->checkThumb($link['img']);
        
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    public function checkImage($filename)
    {
        if (!$filename) return false;
        return Tools::check_image($filename, $this->getUimagesPath(), $this->getImagesPath(), $this->data['width'], $this->data['height'], 0777, $this->scale, $this->crop);
    }

    /**
     * @param string $filename
     * @return string
     */
    public function checkThumb($filename)
    {
        return Tools::check_image($filename, $this->getUimagesPath(), $this->getThumbsPath(), $this->data['thumb_width'], $this->data['thumb_height'], 0777, $this->scale, $this->crop);
    }

    /**
     * @return string
     */
    public function getThumbsPath()
    {
        $path = $this->getGfxPath() . DIRECTORY_SEPARATOR . $this->thumbDir;
        Tools::check_if_exists($path, $this->dir_mode);
        return $path;
    }

    /**
     * @return string
     */
    public function getThumbsUrl()
    {
        return $this->getGfxUrl() . '/' . $this->thumbDir;
    }

    /**
     * @return string
     */
    public function getImagesPath()
    {
        $path = $this->getGfxPath() . DIRECTORY_SEPARATOR . $this->imageDir;
        Tools::check_if_exists($path, $this->dir_mode);
        return $path;
    }

    /**
     * @return string
     */
    public function getImagesUrl()
    {
        return $this->getGfxUrl() . '/' . $this->imageDir;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getImageUrl($filename)
    {
        return $this->getImagesUrl() . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getThumbUrl($filename)
    {
        return $this->getThumbsUrl() . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * @param string $filename
     */
    public function deleteImage($filename)
    {
        @unlink($this->getImagesPath() . DIRECTORY_SEPARATOR . $filename);
        @unlink($this->getThumbsPath() . DIRECTORY_SEPARATOR . $filename);
    }
}