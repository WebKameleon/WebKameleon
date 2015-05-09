<?php

class publicController extends Controller
{
    public function get($setSpecialView=true)
    {
        
        $payment=Bootstrap::$main->getConfig('payment');
        
        $tac=isset($payment[Bootstrap::$main->session('ulang')]['tac'])?$payment[Bootstrap::$main->session('ulang')]['tac']:$payment['default']['tac'];
        
        $ret = array(
            'return_url' => Bootstrap::$main->getRoot() . 'wizard/create',
            'show_footer' => 1,
            'tac'=>$tac
        );
        
        if (isset($_GET) && count($_GET) ) $ret['return_url'] = false;

        
        $public=Bootstrap::$main->getConfig('public');
        if ($setSpecialView) $this->setViewTemplate(APPLICATION_PATH . '/views/scripts/'.$public['view']);
        
        return $ret;
    }

    public function info()
    {

        return array();
    
    }
    
    public function scopes()
    {
        Observer::observe('scopes-deny');
        return $this->get(false);
    }
    
    
    public function error()
    {
        Bootstrap::$main->error(ERROR_ERROR,'User error %s',array($this->id));
        $this->redirect('public');
    }
    
    
    public function payu_notify()
    {
        $payment = new paymentController();
        $payment->init();
        
        $p = $payment->payu_process();
        header('Content-Type: text/plain; charset=utf-8');
        die('OK');
    }
    
    
    public function paypal_notify()
    {
        $payment = new paymentController();
        $payment->init();

       
        $p = $payment->paypal_process();
        header('Content-Type: text/plain; charset=utf-8');
        die('OK');
    }    
    
    
    
    public function tr()
    {
        if (!isset($_GET['txt'])) return;
        
        $txt=$_GET['txt'];
        $ret = array('oryginal'=>$txt,'translated'=>Tools::translate($txt));
    
        die(json_encode($ret));
    }
}
