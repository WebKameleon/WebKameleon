<?php
class uincludesController extends mediaController {
    
    protected function getLocalPath() {
        
        $path=explode('/',$this->fullpath);
        
        $server=Bootstrap::$main->session('server');


        if (!isset($path[1]) || !isset($server['id']) || $path[1]!=trim($server['nazwa'])) return null;
        
        unset($path[0]);
        unset($path[1]);
        return dirname(Bootstrap::$main->session('uincludes')).'/'.implode('/',$path);
    }
    
}
