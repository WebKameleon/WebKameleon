<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class observerController extends Controller
{
    /**
     * @var observerModel
     */
    protected $observerModel;
    
    
    protected function init()
    {
        parent::init();
        
        $user = Bootstrap::$main->session('user');

        if (!isset($user['admin']) || !$user['admin']) mydie(Tools::translate('Insufficient rights'), Tools::translate('Error'));
        
        $this->observerModel = new observerModel;
    }
    


    public function get()
    {
        $data = Bootstrap::$main->session();

        if ($data['event'] = $this->id)
            $data['events'] = $this->observerModel->getEvents($this->id);
        else
            $data['list'] = $this->observerModel->getList();

        return $data;
    }

    public function add()
    {
        $data = Bootstrap::$main->session();

        if (($data['event'] = $this->id) == null) {
            $this->redirect('observer/get');
            return;
        }

        if (isset($_POST['observer'])) {
            $this->observerModel->load($_POST['observer'], true);
            $this->observerModel->save();
            return $this->redirect('observer/get/' . $data['event']);
        }

        $this->setViewTemplate('observer.edit');

        $data['observer'] = array(
            'pri'    => 1,
            'days'   => 0,
            'active' => 1
        );

        $data['languages'] = Bootstrap::$main->translate->getLanguages();

        return $data;
    }

    public function edit()
    {
        $data = Bootstrap::$main->session();

        if ($this->id == null) {
            $this->redirect('observer/get');
            return;
        }

        $this->observerModel->get($this->id);
        $data['event'] = $this->observerModel->event;

        if (isset($_POST['observer'])) {
            $postData = $_POST['observer'];
            foreach ($this->observerModel->data() as $k => $v) {
                if ($k != $this->observerModel->getKey() && isset($postData[$k])) {
                    $this->observerModel->$k = $postData[$k];
                }
            }
            $this->observerModel->save();
            $this->redirect('observer/get/' . $data['event']);
            return;
        }

        $data['observer'] = $this->observerModel->data();
        $data['languages'] = Bootstrap::$main->translate->getLanguages();

        return $data;
    }

    public function remove()
    {
        if ($this->id) {
            $this->observerModel->remove($this->id);
        }
        $this->redirectBack();
    }
    
    
    public function test()
    {
        $event=$this->id;
        $user=new userModel();
        $user->getCurrent();
        $data=$user->data();
        
        $data['him']=$data['me']=$data['email'];
        
        $server=Bootstrap::$main->session('server');
        
        $data=array_merge($server,$data);
        
        Observer::observe($event,$data,$data['ulang']);
        
        $this->redirect('observer/get/'.$event);
        
    }
    
}