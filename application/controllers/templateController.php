<?php
class templateController extends mediaController {
    
    protected function getLocalPath() {
        $template = Bootstrap::$main->session('template');   
        if (!$template) return null;
                
        $path=explode('/',$this->fullpath);
        unset($path[0]);
        
        return $template.'/'.implode('/',$path);
    }
    
}
