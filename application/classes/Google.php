<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

require_once 'google-api-php-client/src/Google/autoload.php';
require_once 'google-api-php-client/src/Google/Client.php';
//require_once 'google-api-php-client/src/Google_Client.php';


/**
 * Class Google
 *
 * @method static Google_DriveService getDriveService
 * @method static Google_CalendarService getCalendarService
 * @method static Google_YouTubeService getYouTubeService
 * @method static Google_AnalyticsService getAnalyticsService
 *
 */
class Google
{
    /**
     * @return Google_Client
     */
    public static function getClient()
    {
        $options = Bootstrap::$main->getConfig('oauth2');

        //$scopes = self::getScopes();

        $client = new Google_Client;
        $client->setApplicationName($options['application_name']);
        $client->setClientId($options['client_id']);
        $client->setClientSecret($options['client_secret']);
        if (isset($_SERVER['HTTP_HOST'])) $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . Bootstrap::$main->getRoot() . 'auth/get_token');
        
        //$client->setScopes($scopes);

        return $client;
    }

    /**
     * @return array
     */
    public static function getScopes()
    {
        return array();
        
        $options = Bootstrap::$main->getConfig('oauth2');
        $scopes = explode(" ", $options['scopes']);
        
        if ($options['extra_scopes']) {
            $scopes = array_merge($scopes, explode(" ", $options['extra_scopes']));
        }
        sort($scopes);
        return array_unique($scopes);
    }

    /**
     * @param int|array $userData
     * @return Google_Client
     */
    public static function getUserClient($user = null, $force=false, $forscope='')
    {
        
        
        if (isset($_GET['error']) && $_GET['error']=='access_denied')
        {
            Bootstrap::$main->redirect('index/get/'.Bootstrap::$main->session('referpage'));
        }
 
        if ($forscope) Bootstrap::$main->session('forscope',$forscope);
        else $forscope=Bootstrap::$main->session('forscope');
        
        $client = self::getClient();
        
        if (!$forscope) return $client;
        
        $currentUser=Bootstrap::$main->session('user');
        $options = Bootstrap::$main->getConfig('oauth2');
        $auth=Bootstrap::$main->session('auth');
        
        if ($user == null) $user = $currentUser;

        $user = new userModel($user);
        $current = (strtolower($auth['email']) == strtolower($user->email) );

        if ($user->access_token) {
            $tokens = json_decode($user->access_token, true);
        } else {
            $tokens = array();
        }
        
        

        $scopes=array();
        $current_scopes=array();
        $change = false;
        
        
        if ($user->access_token)
        {
            $current_scopes=json_decode($user->access_token,true);
            $scopes=array_keys($current_scopes);
        }
         
        
        foreach($scopes AS $i=>$scope)
        {
            if (strstr($scope,' '))
            {
                foreach (explode(' ',$scope) AS $sc)
                {
                    $scname=array_search($sc,$options['scopes']);
                    if (!$scname) $scname=array_search($sc.'.readonly',$options['scopes']);
                    
                    if ($scname)
                    {
                        $scopes[]=$scname;
                        $change=true;
                        $current_scopes[$scname] = $current_scopes[$scope];
                    }
                }
                unset($scopes[$i]);
                unset($current_scopes[$scope]);
            }
        }
        
        if ($forscope && !in_array($forscope,$scopes))
        {
            $scopes[]=$forscope;
            $change=true;
            $current_scopes[$forscope]=null;
        }
        
        
        
        if ($change)
        {
            $user->access_token=json_encode($current_scopes);
            $user->save();
            $change=false;
        }
        
         
        
        $scopes_to_ask=array();
        
        if ($forscope && isset($options['scopes'][$forscope]) && strstr($options['scopes'][$forscope],' ')) $scopes_to_ask[]=$options['scopes'][$forscope];
        else foreach($scopes AS $scope) if ($scope && isset($options['scopes'][$scope])) $scopes_to_ask[]=$options['scopes'][$scope];
    
    

        
        $client->setScopes($scopes_to_ask);
        

        
        if ($force) {
            if (isset($_GET['code'])) $client->authenticate($_GET['code']);
            else {
                $client->setAccessType('offline');
                $auth_url = $client->createAuthUrl();
                header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
                die();
            }
            $current_scopes[$forscope] = $client->getAccessToken();
            
            if ($forscope && isset($options['scopes'][$forscope]) && strstr($options['scopes'][$forscope],' '))
            {

                foreach (explode(' ',$options['scopes'][$forscope]) AS $s)
                {
                    $k=array_search($s,$options['scopes']);
                    if ($k) $current_scopes[$k]= $current_scopes[$forscope];
                }
                
            }
            else
            {
                foreach($scopes AS $scope)
                {
                    if ($scope && !strstr($options['scopes'][$scope],' ')) $current_scopes[$scope]=$current_scopes[$forscope];
                }
                
            }
            
            $change = true;
        }
        
     
        if (isset($current_scopes[$forscope]) && $current_scopes[$forscope])
        {
            $client->setAccessToken($current_scopes[$forscope]);
        }
        else
        {
            if (!$force) return $client;
        }
        
        
        if ($force && !isset($_GET['code'])) die();
        
        
        
        if ($client->isAccessTokenExpired()) {
            
            $token = json_decode($current_scopes[$forscope], true);
        
            if (isset($token['refresh_token']))
            {
                try {
                    $client->refreshToken($token['refresh_token']);
                } catch (Exception $e) {
                    Tools::log('tokens_refresh_error',array($user->email,$e));
                    
                    if ($current)
                    {
                        $current_scopes[$forscope]=null;
                        $user->access_token = json_encode($current_scopes);
                        $user->save();
                        Bootstrap::$main->redirect('auth/get_token/'.$forscope);
                    }
                }
                $current_scopes[$forscope] = $client->getAccessToken();
                $change = true;
            }
            else
            {
                
                if ($current)
                {
                    
                    Tools::log('tokens_refresh_error',array($forscope,$user->data(),$current_scopes));
                    
                    $current_scopes[$forscope]=null;
                    $user->access_token = json_encode($current_scopes);
                    $user->save();
                    
                    Bootstrap::$main->session('user', $user->data());
                    Bootstrap::$main->redirect('auth/get_token/'.$forscope);
                }
                
            }

        }

        
        if ($change) {
            $user->access_token=json_encode($current_scopes);
            $user->save();
            
            if ($current) {
                Bootstrap::$main->session('user', $user->data());
            }
            Bootstrap::$main->error();
        }

        return $client;
    }

    /**
     * @param string|array|Google_Service_Drive_DriveFile $file
     * @param int|array $userData
     * @return string|bool
     */
    public static function getContent($file, $userData = null)
    {
        $client = self::getUserClient($userData,false,'drive');

        if (is_array($file) && isset($file['downloadUrl'])) {
            $downloadUrl = $file['downloadUrl'];
        } else if ($file instanceof Google_Service_Drive_DriveFile) {
            $downloadUrl = $file->getDownloadUrl();
        } else if (($URL = filter_var($file, FILTER_VALIDATE_URL)) !== false) {
            $downloadUrl = $URL;
        } else {
            $file = self::getDriveService($client)->files->get($file);
            $downloadUrl = $file['downloadUrl'];
        }

        if ($downloadUrl) {
            return $client->getAuth()->authenticatedRequest(
                new Google_Http_Request($downloadUrl)
            )->getResponseBody();
        }

        return false;
    }

    public function __callStatic($name, $args)
    {
        
        if (preg_match('/^get([a-zA-Z0-9]+)Service$/', $name, $match)) {
            $class = 'Google_Service_' . $match[1];
            //$class = 'Google_'.$match[1].'Service';
            
            
            $userData = @$args[0];
            //require_once 'google-api-php-client/src/contrib/' . $class . '.php';
            return new $class($userData instanceof Google_Client ? $userData : self::getUserClient($userData,false,strtolower($match[1])));
        }

        die('die in file ' . __FILE__ . ' at line ' . __LINE__);
    }
    
    


    protected static function request($url,$method='GET',$data=null,$scope_required='',$return_kind='',$user=null, $headers=array()) {
        
        $request = new Google_Http_Request($url,$method,$headers,$data);
        
        $client = self::getUserClient($user,false,$scope_required);
        
        if ($client->isAccessTokenExpired())
        {
            Bootstrap::$main->redirect('scopes/'.$scope_required);
        }
        $response = $client->getAuth()->authenticatedRequest($request);
        
        $ret=$response->getResponseBody();
        
       
        if ($return_kind=='xml') return simplexml_load_string($ret);
        if ($return_kind=='json') return json_decode($ret,true);
        
        return $ret;
    }
    
    
}
