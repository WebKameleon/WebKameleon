<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class gtranslateWidget extends Widget
{
    public $name = 'gtranslate';

    /**
     * @var array
     */
    public $all_languages;

    public function init()
    {
        parent::init();

        $langs_used = Bootstrap::$main->session('langs_used');

        $items1 = array();
        $items2 = array();

        foreach (Bootstrap::$main->getConfig('langs') as $lang) {
            $tmp = array();
            $tmp['code'] = $lang;
            $tmp['name'] = $name = Tools::translate($lang);
            $tmp['checked'] = isset($this->data['languages']) && array_key_exists($lang, $this->data['languages']);

            if (in_array($lang, $langs_used)) {
                $items1[$name] = $tmp;
            } else {
                $items2[$name] = $tmp;
            }
        }

        ksort($items1);
        ksort($items2);

        $this->all_languages = array_merge($items1, $items2);
    }

    public function run()
    {
        parent::run();

        $js_options = array();
        $js_options['pageLanguage'] = $lang = Bootstrap::$main->session('lang');

        $languages = $this->data['languages'];
        unset($languages[$lang]);

        $js_options['includedLanguages'] = implode(',', array_keys($languages));
        $js_options['multilanguagePage'] = (bool) $this->data['multi_languages'];
        $js_options['autoDisplay'] = (bool) $this->data['auto_display'];

        switch ($this->data['display_mode'])
        {
            case 'inline':
                $js_options['layout'] = $this->data['display_mode_inline'];
                break;

            case 'tabbed':
                $js_options['layout'] = $this->data['display_mode_tabbed'];
                break;

            default:
                $js_options['layout'] = 2;
                break;
        }

        $this->js_options = json_encode($js_options);
    }
}