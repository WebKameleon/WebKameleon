<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class langsWidget extends Widget
{
    public $name = 'langs';

    /**
     * @var array
     */
    public $all_languages,$page_target;

    public function init()
    {
        parent::init();

        $langs_used = Bootstrap::$main->session('langs_used');
        
        $this->all_languages=array();

        
        foreach ($langs_used as $lang) {
            $tmp = array();
            $tmp['code'] = $lang;
            $tmp['name'] = $name = Tools::translate($lang);
            $tmp['checked'] = !isset($this->data['languages']) || array_key_exists($lang, $this->data['languages']);

            $this->all_languages[]=$tmp;
        }


    }

    public function run()
    {
        parent::run();

        
        $langs = isset($this->data['languages']) ? array_keys($this->data['languages']) : Bootstrap::$main->session('langs_used');

        $this->all_languages=array();
        
        foreach ($langs AS $lang) $this->all_languages[$lang]=Tools::translate($lang);
        
        $this->page_target = isset($this->data['home']) && $this->data['home'] ? '0' :  $this->webpage['id'];
        
        
    }
}