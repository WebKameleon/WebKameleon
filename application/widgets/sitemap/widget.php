<?php

class sitemapWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'sitemap';

    /**
     * @var array
     */
    public $sitemap;

    /**
     * @var string
     */
    public $ul;

    public function run()
    {
        $this->sitemap = $this->getSitemap(0);
        $this->ul = $this->ul($this->sitemap);

        Bootstrap::$main->tokens->loadJQuery = true;
        $this->loadJS('sitemap.js');

        parent::run();
    }

    /**
     * @param array $sitemap
     * @return string
     */
    protected function ul(array $sitemap)
    {
        $ret = '<ul>';
        foreach ($sitemap as $page) {
            $plus = $plus = isset($page['sitemap']) && count($page['sitemap']);
            if ($page['hidden'] && !$plus)
                continue;

            $ret .= '<li title="' . $page['title'] . '">';
            $ret .= '<span class="sitemap_box' . ($plus ? ' sitemap_box_plus' : '') . '"></span>';

            if (!$page['hidden'])
                $ret .= '<a href="' . $page['href'] . '">';

            $ret .= $page['title_short'] ? : $page['title'];

            if (!$page['hidden'])
                $ret .= '</a>';

            if ($plus)
                $ret .= $this->ul($page['sitemap']);

            $ret .= '</li>';
        }
        $ret .= '</ul>';

        return $ret;
    }

    /**
     * @param int $page
     * @return array
     */
    protected function getSitemap($page)
    {
        $webpage = new webpageModel();
        $sitemap = $webpage->getChildren($page);

        if (is_array($sitemap)) {
            foreach ($sitemap AS $i => $p) {

                if ($p['nositemap']) {
                    unset($sitemap[$i]);
                    continue;
                }

                $sitemap[$i]['sitemap'] = $this->getSitemap($p['id']);
                $sitemap[$i]['href'] = Bootstrap::$main->tokens->href('', '', $p['id']);
            }
        } else {
            $sitemap = array();
        }

        return $sitemap;
    }

}
