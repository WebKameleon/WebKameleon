<?php

class Observer
{
    /**
     * @param string $event
     * @param array $vars
     * @param string $lang
     * @param null $result
     * @param int $days
     */
    public static function observe($event, array $vars = array(), $lang = null, $result = null, $days = null)
    {
        $observer = new observerModel;
        $user     = new userModel;
        
        
        
        if (isset($vars['him'])) $vars['him']=trim($vars['him']);
        if (isset($vars['me'])) $vars['me']=trim($vars['me']);
        
        if (!isset($vars['admin_mail'])) {
            $global=Bootstrap::$main->getConfig('global');
            $vars['admin_mail'] = $global['admin'];
        }
        
        
        if (!$lang && isset($vars['him'])) {
            $user->getByEmail($vars['him']);
            $lang = $user->ulang;
        }

        if (!$lang  && isset($vars['me'])) {
            $user->getByEmail($vars['me']);
            $lang = $user->ulang;
        }
        
        if (!$lang) $lang=Bootstrap::$main->session('lang');

        if (!isset($vars['server'])) {
            $vars['server'] = Bootstrap::$main->session('server');
        }
        
        if (!isset($vars['root'])) {
            $vars['root'] = Bootstrap::$main->getRoot();
        }
        
        
        $vars['_server'] = $_SERVER;

        $observers = $observer->getObservers($event, $lang, $result, $days);
        

        foreach ($observers AS $obs) {
            self::obs($obs, $vars);
        }
    }

    /**
     * @param array $params
     * @param array $vars
     */
    protected static function obs($params, array $vars = array())
    {
        foreach ($params AS $k => $param)
            $params[$k] = GN_Smekta::smektuj($param, $vars);

        if ($params['mail_to'] && $params['mail_subject'] && $params['mail_msg'])
            self::mail($params);
    }

    /**
     * @param array $params
     * @return bool
     */
    protected static function mail (array $params)
    {

        if (!is_array($params['mail_from']) && strstr($params['mail_from'],',')) $params['mail_from']=explode(',',$params['mail_from']);
        if (is_array($params['mail_from']) )
        {
            $r=rand(0,count($params['mail_from'])-1);
            $params['mail_from']=$params['mail_from'][$r];
        }
               
        $user = new userModel;
        $user = $user->getByEmail($params['mail_from']);

        if (empty($user))
            return false;

        $token = @json_decode(Google::getUserClient($user,false,'mail')->getAccessToken(), true);

        if (!isset($token['access_token'])) return false;

        
        $params['mail_msg'] = str_replace('{myname}',$user['fullname'],$params['mail_msg']);
        
        
        $gmail = new GN_SmtpGmail;
        $gmail->email = $user['email'];
        $gmail->fullname = $user['fullname'];
        $gmail->token = $token['access_token'];

        $mailer = new GN_Mailer;
        $mailer->CharSet = 'UTF-8';
        $mailer->setGmail($gmail);

        $mailer->AddAddress($params['mail_to']);

        if ($params['mail_reply']) $mailer->AddReplyTo($params['mail_reply']);

        
        $to=explode(',',$params['mail_to']);
        
        if ($params['mail_cc'])
            foreach (explode(PHP_EOL, $params['mail_cc']) as $cc){
                $mailer->AddCC($cc);
                $to=array_merge($to,$cc);
            }

        $mailer->Subject = $params['mail_subject'];

        if ($params['mail_html'])
            $mailer->MsgHTML($params['mail_msg']);
        else
            $mailer->Body = $params['mail_msg'];

        $result = $mailer->Send();
        
        
    
        
        if ($result>0) foreach($to AS $t)
        {
            $sent=new observersentModel();
            $sent->nd_sent = Bootstrap::$main->now;
            $sent->event=$params['event'];
            $sent->email=$t;
            
            $sent->save();
        }
        

        return $result;
    }
}
