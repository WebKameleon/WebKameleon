<?php
class logoutController extends Controller {
    public function get() {
        
        $config=Bootstrap::$main->getConfig('security');
        $redirect=$config['logout_url'];
        Bootstrap::$main->logout($redirect?:Bootstrap::$main->getRoot());
    }
}
