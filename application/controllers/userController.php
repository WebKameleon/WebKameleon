<?php


class userController extends Controller
{
    public function profile()
    {
        $languages = Bootstrap::$main->translate->getLanguages();

        if (isset($_POST['profile'])) {
            $postData  = $_POST['profile'];
            $user = new userModel(Bootstrap::$main->session('user'), false);
            if (isset($postData['lang']) && array_key_exists($postData['lang'], $languages)) {
                $user->ulang = $postData['lang'];
            }
            $user->after_login_redirect = $postData['after_login_redirect'];
            Bootstrap::$main->session('user', $user->save());
            return $this->redirect('index/get');
        }

        $session = Bootstrap::$main->session();
        $session['languages'] = $languages;

        return $session;
    }
    
    
    public function remove()
    {
        $ajax=new ajaxController();
        $u=Bootstrap::$main->session('user');
        
        $server=new serverModel();
    
        
        $servers=$server->getForUser($u['username']);
        
        foreach($servers AS &$s) $s['owner']=1;
        
        Bootstrap::$main->session('trash',$servers);
        
        foreach($servers AS &$s)
        {
            $ajax->wizard_remove($s['id']);
        }
        
        
        $user=new userModel($u['username']);
        $user->remove($u['username']);
        
        $this->redirect('logout');
    }
    
    
    
    
}