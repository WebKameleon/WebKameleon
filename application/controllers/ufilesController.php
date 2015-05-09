<?php
class ufilesController extends mediaController {
    
    protected function getLocalPath() { 

        $path=explode('/',$this->fullpath);
        
        $server=Bootstrap::$main->session('server');
        
        if (!isset($path[1]) || !isset($server['id'])) return null;
        
        $s=explode('-',$path[1]);
        if ($s[0]!=$server['id']) return null;
        
        unset($path[0]);
        unset($path[1]);
        
        return Bootstrap::$main->session('ufiles_path').'/'.implode('/',$path);
    }
    
}
