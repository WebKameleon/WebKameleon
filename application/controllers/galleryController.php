<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class galleryController extends Controller
{
    protected function init()
    {

        parent::init();

        define('ELFINDER_IMG_PARENT_URL', Bootstrap::$main->getRoot() . 'elFinder');
    }

    public function get()
    {
        $data = Bootstrap::$main->session();
        $data['elfinder'] = array();

        return $data;
    }

    public function browse()
    {
        return $this->get();
    }

    public function connector($username = null)
    {
        require_once 'elFinder/elFinderConnector.class.php';
    
        $connector = new elFinderConnector($this->elFinder($username));
    
        $connector->run();
    }

    /**
     * @param string $hash
     * @param string $username
     * @return array
     */
    public function info($hash = null, $username = null)
    {
        $data = $this->elFinder($username)->exec('info', array(
            'targets' => array($hash ?: $this->id)
        ));

        if ($data['files']) {
            return current($data['files']);
        } else {
            return array();
        }
    }

    public function dimensions($hash = null, $username = null)
    {
        $data = $this->elFinder($username)->exec('dim', array(
            'target' => $hash ?: $this->id
        ));

        return $data;
    }

    /**
     * @param string $hash
     * @param string $username
     * @return string
     */
    public function get_content($hash = null, $username = null)
    {
        $data = $this->elFinder($username)->exec('file', array(
            'target' => $hash ?: $this->id
        ));

        $content = '';
        if (isset($data['pointer'])) {
            while (!feof($data['pointer'])) {
                $content .= fread($data['pointer'], 1024);
            }
        }

        return $content;
    }

    /**
     * @return elFinder
     */
    public function elFinder($username = null)
    {
        static $elfinder;

        if ($elfinder == null) {
            require_once 'elFinder/elFinder.class.php';
            require_once 'elFinder/elFinderVolumeDriver.class.php';
            require_once 'elFinder/elFinderVolumeLocalFileSystem.class.php';
            require_once 'elFinder/elFinderVolumeGoogle.class.php';

            $roots = array();

            $roots[] = $this->configRoot(array(
                'driver' => 'LocalFileSystem',
                'alias'  => $this->trans('Images'),
                'path'   => Bootstrap::$main->session('uimages_path'),
                'URL'    => Bootstrap::$main->session('uimages'),
            ), 1);

            $roots[] = $this->configRoot(array(
                'driver' => 'LocalFileSystem',
                'alias'  => $this->trans('Files'),
                'path'   => Bootstrap::$main->session('ufiles_path'),
                'URL'    => Bootstrap::$main->session('ufiles'),
            ), 2);

            $roots[] = $this->configRoot(array(
                'driver' => 'LocalFileSystem',
                'alias'  => $this->trans('Root files'),
                'path'   => Bootstrap::$main->session('ufiles_path') . '/.root',
                'URL'    => Bootstrap::$main->session('ufiles') . '/.root',
            ), 3);

            $roots[] = $this->configRoot(array(
                'driver' => 'LocalFileSystem',
                'alias'  => $this->trans('Templates'),
                'path'   => Bootstrap::$main->session('template'),
                'URL'    => Bootstrap::$main->getRoot(),
            ), 4);

            $roots[] = $this->configRoot(array(
                'driver' => 'LocalFileSystem',
                'alias'  => $this->trans('Include files'),
                'path'   => Bootstrap::$main->session('uincludes'),
                'URL'    => Bootstrap::$main->session('uincludes_ajax'),
            ), 5);

            $roots[] = $this->configRoot(array(
                'driver' => 'LocalFileSystem',
                'alias'  => $this->trans('HTML files'),
                'path'   => Bootstrap::$main->session('ufiles_path') . '/.html',
                'URL'    => Bootstrap::$main->session('ufiles') . '/.html',
            ), 6);

            
            $roots[] = array(
                'driver' => 'Google',
                'client' => Google::getUserClient($username,false,'drive'),
            );
            
            $elfinder = new elFinder(array(
                'roots' => $roots
            ));
        }
        
        return $elfinder;
    }

    /**
     * @param array $root
     * @param int $type
     * @return array
     */
    protected function configRoot(array $root, $type)
    {
        // SIM hide files with names starting with dot
        $attributes = array(
            array(
                'pattern' => '/\/\./',
                'hidden'  => true
            )
        );

        if (isset($root['attributes'])) {
            $root['attributes'] = array_merge($root['attributes'], $attributes);
        } else {
            $root['attributes'] = $attributes;
        }

        $files = Bootstrap::$main->getConfig('files');

        if (isset($files[$type])) {
            $root['uploadOrder'] = array('allow');
            $root['uploadAllow'] = array();

            if (empty($files[$type]) || strtolower($files[$type]) == 'all') {
                $root['uploadAllow'][] = 'all';
            } else {
                foreach (explode(' ', $files[$type]) as $ext) {
                    $root['uploadAllow'][] = Tools::get_mime_from_ext($ext);
                }
            }
        }

        // SIM dla template
        if ($type == 4) {
            if (Bootstrap::$main->session('template_media') == 0) {
                $root['attributes'][] = array(
                    'pattern' => '/^.*$/',
                    'read'    => true,
                    'write'   => false,
                    'locked'  => true
                );
            }
        }

        return $root;
    }
}