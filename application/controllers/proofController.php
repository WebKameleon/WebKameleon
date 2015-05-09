<?php
class proofController extends Controller
{
    
    
    protected function getPage()
    {
        $page=0+$this->id;
        if ($page<0) $page=0;

        $webpage=new webpageModel();
        $webpage->getOne($page);        
        
        return $webpage;
    }
    
    
    public function get()
    {
        $webpage=$this->getPage();
        $page=$webpage->id;
        
        $ret=array('page'=>$page);
        
        $users=new userModel();
    
        $server=Bootstrap::$main->session('server');
    
        if ($webpage->mayProof())
        {
            $ret['canProof']=true;
            $ret['pages']=$webpage->getUnproven();
            $ret['active']=$page;
            
            foreach($ret['pages'] AS &$p)
            {
                $p['user']=$users->find_one_by_username($p['unproof_autor']);
                $p['waiting'] = $p['noproof']<0;
            }

        }
        else
        {
            $ret['canProof']=false;
            $serverModel=new serverModel($server['id']);
            $users = $serverModel->getUsers();
            
            foreach($users AS $i=>$user)
            {
                if (!$webpage->mayProof($page,$user['username'])) unset($users[$i]);
            }
            
            $ret['users'] = $users;

            
        }
        
        
        return $ret;
    }
    
    public function proof()
    {
        $webpage=$this->getPage();        

        $me = Bootstrap::$main->session('user');
        $server = Bootstrap::$main->session('server');

        $him=new userModel($webpage->unproof_autor);

        Observer::observe('proof_proof', array(
            'me' => $me['email'],
            'him' => $him->email,
            'server' => $server,
            'webpage' => $webpage->data()
        )); 

        
        $webpage->noproof = 0;
        $webpage->proof_autor=$me['username'];
        $webpage->save();
        
        $this->redirect('proof'.(isset($_GET['hash'])?'#'.$_GET['hash']:''));
        
    }
    
    public function reject()
    {
        $webpage=$this->getPage();        

        $me = Bootstrap::$main->session('user');
        $server = Bootstrap::$main->session('server');

        $him=new userModel($webpage->unproof_autor);
        
        
        Observer::observe('proof_reject', array(
            'me' => $me['email'],
            'him' => $him->email,
            'txt' => $_POST['txt'],
            'server' => $server,
            'webpage' => $webpage->data()
        )); 

        $przed=strlen($webpage->unproof_comment)?"\n\n":'';

        
        if ($_POST['txt']) $webpage->unproof_comment.=$przed.$me['fullname'].":\n".$_POST['txt'];
        $webpage->noproof = abs($webpage->noproof);
        
        $webpage->save();
        
        $this->redirect('proof/get/'.$webpage->id);
        
    }
    
    public function request()
    {
        $webpage=$this->getPage();

        $me = Bootstrap::$main->session('user');
        $server = Bootstrap::$main->session('server');
        
        if (count($_POST['user'])) {
            $users=implode(',',$_POST['user']);

                        
            Observer::observe('proof_request', array(
                'me' => $me['email'],
                'they' => $users,
                'txt' => $_POST['txt'],
                'server' => $server,
                'webpage' => $webpage->data()
            ));            
            
            
            $przed=strlen($webpage->unproof_comment)?"\n\n":'';

            
            if ($_POST['txt']) $webpage->unproof_comment.=$przed.$me['fullname'].":\n".$_POST['txt'];
            $webpage->noproof = -1 * abs($webpage->noproof);
            $webpage->unproof_autor=$me['username'];
            
            $webpage->save(true,false);
            
            $this->redirect('index/get/'.$webpage->id);
        }
        else {
            $this->redirect('proof/get/'.$webpage->id);
        }
    }

}
