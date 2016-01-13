<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class breadcrumbsWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'breadcrumbs';

    /**
     * @var array
     */
    public $breadcrumbs;

    public function run()
    {
        parent::run();

        $webpage = Bootstrap::$main->tokens->webpage;

        if ((!isset($this->data['showhome']) || !$this->data['showhome']) && $webpage['id'] == 0) return;

        $result = array();

        $page = $webpage['id'];
        if (!isset($this->data['all'])) $this->data['all']=0;
        while ($page) {
            
            $rec = $this->getpage($page, $this->data['all']);
            if (!count($result)) $rec['last'] = true;
            $result[] = $rec;
            $page = $rec['prev'];
        }

        if (isset($this->data['home']) && $this->data['home']) {
            $result[] = array(
                'title' => $this->data['home'],
                'href' => Bootstrap::$main->tokens->page_href(0),
                'home' => true
            );
        }

        if (!isset($result[0]['href']) && count($result)>1) {
            unset($result[0]);
        }
        
        krsort($result);

        $result[count($result) - 1]['first'] = true;

        $this->breadcrumbs = $result;
        
        $this->loadJS('breadcrumbs.js');
        Bootstrap::$main->tokens->loadJQuery = true;
    }

    protected function title(&$wp)
    {
        return $wp['title_short'] ? : $wp['title'];
    }

    protected function getpage($page, $showNeighbors)
    {

        $webpage = new webpageModel();

        $wp = $webpage->getOne($page);

        if ($wp['hidden'] || $wp['trash']) {
            if ($wp['id'] == 0) return;

            return $this->getpage($wp['prev'], $showNeighbors);
        }

        $result['title'] = $this->title($wp);
        if (!$wp['nositemap']) {
            $result['href'] = Bootstrap::$main->tokens->page_href($page);
        }
        $result['prev'] = $wp['prev'];

        if ($showNeighbors) {
            $neighbors = array();
            $pages = $webpage->getAllByPrev($wp['prev']);

            foreach ($pages AS $p) {
                if ($p['hidden'] || $p['nositemap']) continue;

                $neighbors[] = array(
                    'title' => $this->title($p),
                    'href' => Bootstrap::$main->tokens->page_href($p['id']),
                    'self' => $p['id'] == $page
                );
            }

            sort($neighbors);
            $result['neighbors'] = $neighbors;

        }

        return $result;
    }

}