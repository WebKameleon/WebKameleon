<?php
class uimagesController extends mediaController {
    
    protected function getLocalPath() {
        $uimages = MEDIA_PATH;   

        $path=explode('/',$this->fullpath);
        
        
        
        $server=Bootstrap::$main->session('server');
        if (!isset($path[1]) || !isset($server['id']) || $path[1]!=$server['id']) return null;
        
        unset($path[0]);
        unset($path[1]);
        
        
        return dirname(Bootstrap::$main->session('uimages_path')).'/'.implode('/',$path);
    }
    
}
