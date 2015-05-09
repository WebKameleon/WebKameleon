<?php

class Translate
{
    /**
     * @var array
     */
    private $words = null;

    /**
     * @var string
     */
    private $lang,$olang;
    

    /**
     * @var array
     */
    private $languages;

    public function __construct($lang)
    {
	$this->lang=$lang;
	$this->olang=null;
	
    }

    protected function init()
    {
	$lang=$this->lang;
	
        $path = APPLICATION_PATH . '/lang/' . $lang . '.ser';
        if (file_exists($path)) {
	    $this->olang = $lang;
            $this->words = unserialize(file_get_contents($path));
        } else {
            $this->lang = 'en';
	    $this->olang = $lang;
	    $path = APPLICATION_PATH . '/lang/en.ser';
	    $this->words = unserialize(file_get_contents($path));
        }
	
    }
    
    
    public function __get($what)
    {
        return $this->trans($what);
    }

    public function trans($txt)
    {
	if (is_null($this->olang)) $this->init();
    
        $args = func_get_args();

	
        if (isset($this->words[$txt])) {
            $args[0] = $this->words[$txt];
        };
	
	if (!isset($this->words[$txt]) || $this->olang!=$this->lang)
	{
            $security = Bootstrap::$main->getConfig('security');
            $dir = APPLICATION_PATH . '/../files';
            if (isset($security['write_untranslated_words']) && $security['write_untranslated_words'] && is_writable($dir)) {
                $words = array();
                if (file_exists($dir . '/' . $this->olang . '.php')) include($dir . '/' . $this->olang . '.php'); else file_put_contents($dir . '/' . $this->olang . '.php', "<?php\n");
                if (!isset($words[$txt])) file_put_contents($dir . '/' . $this->olang . '.php', '$words[\'' . addslashes($txt) . '\']=\'\';' . "\n", FILE_APPEND);
            }
        }

        $ret = call_user_func_array('sprintf', $args);

        return $ret;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        if ($this->languages == null) {
            $this->languages = array();
            foreach (glob(APPLICATION_PATH . '/lang/*.ser') as $file) {
                list ($lang) = explode('.', basename($file));
                $this->languages[$lang] = $this->trans($lang);
            }
        }

        return $this->languages;
    }
}
