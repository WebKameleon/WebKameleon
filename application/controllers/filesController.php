<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class filesController extends Controller
{
    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var string
     */
    protected $rootURL;

    /**
     * @var resource
     */
    protected $finfo;

    /**
     * @var array
     */
    protected $options;

    protected function setup()
    {
        $this->options = array();

        $this->options['type'] = $this->_getParam('type', 1);
        $this->options['single_mode'] = $this->_getParam('single_mode') ? 1 : 0;

        switch ($this->options['type']) {
            case 1:
            default:
                $this->rootPath = Bootstrap::$main->session('uimages_path');
                $this->rootURL  = Bootstrap::$main->session('uimages');
                break;

            case 2:
                $this->rootPath = Bootstrap::$main->session('ufiles_path');
                $this->rootURL  = Bootstrap::$main->session('ufiles');
                break;

            case 3:
                $this->rootPath = Bootstrap::$main->session('ufiles_path') . '/.root';
                $this->rootURL  = Bootstrap::$main->session('ufiles') . '/.root';
                break;

            case 4:
                $this->rootPath = Bootstrap::$main->session('template');
                $this->rootURL  = Bootstrap::$main->getRoot();
                break;

            case 5:
                $this->rootPath = Bootstrap::$main->session('uincludes');
                $this->rootURL  = Bootstrap::$main->session('uincludes_ajax');
                break;

            case 6:
                $this->rootPath = Bootstrap::$main->session('ufiles_path') . '/.html';
                $this->rootURL  = Bootstrap::$main->session('ufiles') . '/.html';
                break;
        }

        $this->finfo = finfo_open(FILEINFO_MIME_TYPE);
    }

    protected function get_dir()
    {
        return base64_decode($this->_getParam('dir'), '');
    }

    protected function get_root()
    {
        return $this->rootPath . $this->get_dir();
    }

    protected function get_url()
    {
        return $this->rootURL . $this->get_dir();
    }

    public function get()
    {
        $data = Bootstrap::$main->session();

        $this->setup();

        $data['files'] = $this->options;

        if ($this->_hasParam('extra_class'))
            $data['files']['extra_class'] = $this->_getParam('extra_class');

        return $data;
    }

    public function browse()
    {
        $this->setup();
        
        $last_uploaded_files=Bootstrap::$main->session('last_uploaded_files');
        if (!is_array($last_uploaded_files)) $last_uploaded_files=array();
        Bootstrap::$main->session('last_uploaded_files',array());

        $files       = array();
        $directories = array();

        $root = $this->get_root();
        $glob = glob($root . '/*');

        if ($root != $this->rootPath) {
            $directories[] = $this->get_file_info(dirname($root), "..");
        }

        foreach ($glob as $filename) {
            $info = $this->get_file_info($filename);
            

            if ($info['name'][0] == '.')
                continue;

            if ($info['mime'] == 'directory')
                $directories[] = $info;
            else
            {
                $info['checked']=in_array($info['name'],$last_uploaded_files);
                $files[] = $info;
            }
        }

        $files = array_merge($directories, $files);

        $breadcrumbs = array();
        while ($root != $this->rootPath) {
            $breadcrumbs[] = $this->get_file_info($root);
            $root = dirname($root);
        }
        $breadcrumbs[] = $this->get_file_info($root, Tools::translate('Home directory'));

        Header('Content-type: application/json; charset=utf8');
        die(json_encode(array(
            'files' => $files,
            'breadcrumbs' => array_reverse($breadcrumbs)
        )));
    }

    public function handle()
    {
        $this->setup();

        $files = Bootstrap::$main->getConfig('files');

        $options = array(
            'script_url' => Bootstrap::$main->getRoot() . 'files/handle/',
            'upload_dir' => $this->get_root() . '/',
            'upload_url' => $this->get_url() . '/'
        );

        if (!$this->_getParam('dont_resize') && isset($files['image_versions']) && !empty($files['image_versions'])) {
            $options['image_versions'] = $files['image_versions'];
        }

        if (isset($files[$this->options['type']]) && !empty($files[$this->options['type']])) {
            $options['accept_file_types'] = '/\.(' . str_replace(' ', '|', $files[$this->options['type']]) . ')$/i';
        }

        $last_uploaded_files=array();
        if (isset($_FILES['files']['name'])) foreach($_FILES['files']['name'] AS &$name)
        {
            $name=Bootstrap::$main->kameleon->str_to_url($name);
            $last_uploaded_files[]=$name;    
        }
        Bootstrap::$main->session('last_uploaded_files',$last_uploaded_files);
        
        require_once 'UploadHandler.php';
        $handler = new UploadHandler($options, 0);
        $res = $handler->post(0);

        $errors = array();

        foreach ($res['files'] as $file) {
            if (isset($file->error))
                $errors[] = Tools::translate($file->error);
        }

        die(json_encode(array(
            'status' => 1,
            'errors' => array_unique($errors)
        )));
    }

    public function from_url($url = null)
    {
        $this->setup();

        if ($url == null) $url = $this->_getParam('url');

        $filename = Bootstrap::$main->kameleon->str_to_url(basename($url));

        $img = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($img, file_get_contents($url));

        $dst = $this->get_root() . DIRECTORY_SEPARATOR . $filename;

        $files = Bootstrap::$main->getConfig('files');
        if (isset($files['image_versions']) && ($ver = current($files['image_versions'])) !== false) {
            list ($src_w, $src_h) = getimagesize($img);
            if ($src_w>$ver['max_width'] || $src_h>$ver['max_height'])
                Bootstrap::$main->kameleon->min_image($img, $dst, $ver['max_width'], $ver['max_height'], true, false);
            else
                copy($img, $dst);
        } else {
            copy($img, $dst);
        }

        
        $last_uploaded_files=array($filename);
        Bootstrap::$main->session('last_uploaded_files',$last_uploaded_files);        
        
        
        @unlink($img);

        die(json_encode(array(
            'status' => 1,
        )));
    }

    /**
     * @param string $filename
     * @return array
     */
    protected function get_file_info($filename, $name = null)
    {
        $filepath = str_replace($this->rootPath, '', $filename);

        return array(
            'name'    => $name ? : basename($filename),
            'path'    => base64_encode($filepath),
            'url'     => $this->rootURL . $filepath,
            'baseUrl' => $this->rootURL,
            'mime'    => @finfo_file($this->finfo, $filename),
            'size'    => @filesize($filename),
            't'       => @filemtime($filename)
        );
    }
}