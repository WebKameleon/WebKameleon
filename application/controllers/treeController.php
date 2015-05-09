<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class treeController extends Controller
{
    /**
     * @var array
     */
    protected $_metadataKeys;

    /**
     * @var webpageModel
     */
    protected $_modelWebpage;

    protected function init()
    {
        parent::init();

        $this->_metadataKeys = array_flip(array('id', 'sid', 'prev', 'title', 'title_short', 'hidden', 'nositemap'));
        $this->_modelWebpage = new webpageModel;

        if ($this->_hasParam('lang')) {
            $this->_modelWebpage->lang = $this->_getParam('lang');
        }
    }

    public function get()
    {
        $session = Bootstrap::$main->session();
        $node    = $this->_getParam('node');

        if ($node > 0) {
            $parents = $this->_modelWebpage->getAllParents($node);
        } else {
            $parents = $this->_modelWebpage->getAllByPrev(-1);
        }

        $parentNodes = array();
        foreach ($parents as $page) {
            $attr = $this->_getAttr($page);
            $parentNodes[] = $attr['id'];
        }

        $jstree = array();
        $jstree['explorer_mode'] = 0;
        $jstree['lang'] = $this->_getParam('lang', $this->_modelWebpage->lang);
        $jstree['multi_langs'] = $this->_getParam('multi_langs', 0);
        $jstree['initially_load'] = implode('#', $parentNodes);
        $jstree['initially_open'] = end($parentNodes);
        if ($node > 0) {
            $jstree['initially_select'] = 'node_' . $node;
        } else {
            $jstree['initially_select'] = end($parentNodes);
        }
        $session['jstree'] = $jstree;

        return $session;
    }

    public function explorer()
    {
        $session = $this->get();
        $session['jstree']['explorer_mode'] = 1;
        return $session;
    }

    public function temp()
    {
        echo json_encode(array('status' => 1)); die;
    }

    public function connector()
    {
        $prev = $this->_getParam('prev', -1);

        $nodes = array();
        foreach ($this->_modelWebpage->getAllByPrev($prev) as $page) {
            $node = array(
                'data'     => $this->_getTitle($page),
                'attr'     => $this->_getAttr($page),
                'metadata' => $this->_getMetadata($page)
            );
            if ($this->_modelWebpage->hasChildren($page['id'])) {
                $node['state'] = 'closed';
            }
            $nodes[$page['id']] = $node;
        }
        //ksort($nodes);
        echo json_encode(array_values($nodes));
        die;
    }

    /**
     * @param array $page
     * @return string
     */
    protected function _getTitle(array $page)
    {
        return ($page['title_short'] ?: $page['title']) . ' (' . $page['id'] . ')';
    }

    /**
     * @param array $page
     * @return array
     */
    protected function _getAttr(array $page)
    {
        return array(
            'id' => 'node_' . $page['id']
        );
    }

    /**
     * @param array $page
     * @return array
     */
    protected function _getMetadata(array $page)
    {
        return array_intersect_key($page, $this->_metadataKeys);
    }

    public function connector_full()
    {
        $session = Bootstrap::$main->session();
        /**
         * @var Doctrine_Connection $db
         */
        $db = Bootstrap::$main->getConn();
        $pages = $db->fetchAll("SELECT id, sid, prev, title, title_short, nositemap FROM webpage WHERE server = ? AND lang = ? AND ver <= ? ORDER BY prev ASC", array(
            $session['server']['id'], $session['lang'], $session['ver']
        ));
        $treeNodes = array();
        foreach ($pages as $page) {
            $node = array(
                'data' => $page['title'] . ' (' . $page['id'] . ')',
                'attr' => array(
                    'id' => 'node_' . $page['id'],
                ),
                'metadata' => $page,
                'children' => array()
            );
            if ($page['prev'] == -1) {
                $node['state'] = 'open';
            }
            $treeNodes[] = $node;
        }
        $treeData = $this->_findNodes(-1, $treeNodes);
        echo json_encode($treeData);
        die;
    }

    /**
     * @param int $id
     * @param array $treeNodes
     * @return array
     */
    protected function _findNodes($prev, &$treeNodes)
    {
        $nodes = array();
        foreach ($treeNodes as $k => $node) {
            if ($node['metadata']['prev'] == $prev) {
                $node['children'] = $this->_findNodes($node['metadata']['id'], $treeNodes);
                $nodes[] = $node;
                unset($treeNodes[$k]);
            }
        }
        return $nodes;
    }
}