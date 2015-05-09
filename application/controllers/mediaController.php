<?php

class mediaController extends Controller
{

    public function __call($name, $args)
    {
        
        $path = urldecode($this->getLocalPath());
        if (file_exists($path)) {
            if (!is_dir($path)) {
                $p = explode('.', basename($path));
                $ext = strtolower(end($p));
                $expires = 3600;
                switch ($ext) {
                    case 'js':
                        $expires = 300;
                        $mime = 'application/javascript';
                        break;

                    case 'css':
                        $expires = 10;
                        $mime = 'text/css';
                        break;

                    case 'php':
                    case 'phtml':
                        $this->include_file($path);
                        break;

                    default:
                        $mime = mime_content_type($path);
                        break;
                }

                header("Cache-Control: max-age=$expires");
                header("Pragma: ");
                header("Expires: " . date('D, d M Y H:i:s \G\M\T', time() + $expires));

                header('Content-type: ' . $mime);
                readfile($path);

            }
        }
        die();
    }

    protected function init()
    {
        session_write_close();
    }    
    
    
    protected function include_file($file)
    {
        $config=Bootstrap::$main->getConfig();
        if (!$config['security']['allow_td_module_execute']) die("Could not execute '$file' for security reasons!");
        
        $KAMELEON_MODE=true;
        
        chdir(dirname($file));
        include($file);
        die();
    }

    protected function getLocalPath()
    {
        return null;
    }

    public function get()
    {
        $_GET['cmd'] = 'file';
        $_GET['target'] = $this->id;

        $webmedia = new webmediaModel();

        $gallery = new galleryController();
        $gallery->connector($webmedia->getOwner($this->id));
    }
}
