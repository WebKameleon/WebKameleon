<?php

class Debugger
{
    private static $instance;
    private $debug=null;
    
    private $structure,$stack=array();
    
    protected function push($value)
    {
        $this->stack[count($this->stack)]=$value;
        return $value;
    }
    
    protected function top()
    {
        return $this->stack[count($this->stack)-1];
        
    }
    
    protected function pop()
    {
        $ret=$this->top();
        unset($this->stack[count($this->stack)-1]);
        
    }
    
    public static function debug($debug_id=null,$txt=null)
    {
        if (is_null(self::$instance)) self::$instance=new self;
        
        if (is_null(self::$instance->debug)) {
            $config=Bootstrap::$main->getConfig();

            self::$instance->debug=isset($config['security']['debug']) ? $config['security']['debug'] : $config['security.debug'];
        }
        
        if (!self::$instance->debug) return;
        
        return self::$instance->_debug($debug_id,$txt);
    }
    
    private function _debug($debug_id,$txt)
    {
        if (is_null($debug_id)) {
            while(true) {
                $debug_id=md5(time().rand(100000,999999));
                if (!isset($this->structure[$debug_id])) break;
            }
            $this->structure[$debug_id]['start']=microtime(true);
            $this->structure[$debug_id]['txt']=$txt;
            $this->structure[$debug_id]['prev']=null;
            $this->structure[$debug_id]['steps']=array();
            return $this->push($debug_id);
    
        } else {
            $delta=round(microtime(true)-$this->structure[$debug_id]['start'],4);
            if ($txt) {
                $this->structure[$debug_id]['steps'][$txt]=array('t'=>$delta);
            } else {
                
                
                $this->pop();
                $this->structure[$debug_id]['delta']=$delta;
                
                $txt=$this->structure[$debug_id]['txt'].': '.$delta;
                $steps=$this->structure[$debug_id]['steps'];
                
                $this->structure[$debug_id]['stack']=$this->stack;
                
                if (count($this->stack)) {
                    $top=$this->top();
                    
                    $this->structure[$top]['steps'][$txt]=count($steps)?$steps:null;
                    $this->structure[$debug_id]['prev']=$top;
                }

                
                return array($txt => $steps);
            }
        }
        
        
    }
    
    
    
    
}