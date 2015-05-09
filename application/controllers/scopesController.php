<?php

class scopesController extends Controller
{

    public function get()
    {
        $user=new userModel();
        $user=$user->getCurrent();
        $oauth=Bootstrap::$main->getConfig('oauth2');
        
        
        $scopes=json_decode($user->access_token,true);
        
        if (is_array($scopes)) foreach(array_keys($scopes) AS $scope)
        {
            $scopes[$scope]=array('key'=>1);
            $scopes[$scope]['valid']=$this->checkscope($scope);
        }
        
        //mydie($scopes);
        
        
        return array('user'=>$user,'scopes'=>$scopes,'oauth'=>$oauth);
    }
    
    
    public function remove()
    {
        $scope=$this->id;
        
        if ($scope)
        {
            $user=new userModel();
            $user->getCurrent();
            $scopes=json_decode($user->access_token,true);
            
            if (isset($scopes[$scope]))
            {
                unset($scopes[$scope]);
                $user->access_token=json_encode($scopes);
                $user->save();
                $u=Bootstrap::$main->session('user');
                $u['access_token']=$user->access_token;
                Bootstrap::$main->session('user',$u);
            }
        }
        
        $this->redirect('scopes');
    }
    
    
    public function __call($name,$args)
    {
        return $this->accessandvalid($name);
    }
    
    
    
    
    protected function checkscope($scope)
    {
        $user=new userModel();
        $user=$user->getCurrent();
        $scopes=json_decode($user->access_token,true);        
        
        $client=Google::getUserClient(null,false,$scope);
        
        $token=json_decode($scopes[$scope],true);
        
        $scopes[$scope]=array('key'=>1);
        try
        {
            $client->refreshToken($token['refresh_token']);
            return 1;
        }
        catch (Exception $e)
        {
            return 0;
        }        
    }
    
    protected function accessandvalid($scope)
    {
        $user=new userModel();
        $user=$user->getCurrent();
        
        return array('hasAccess'=>$user->hasAccess($scope), 'valid'=>$this->checkscope($scope));
    }
    
}