<?php

class Gmail {
    
    public static function send($to,$data,$subject='',$from='',$html=false,$replyto='')
    {
        if (!strstr($to,'@'))
        {            
            parse_str($data);
            Observer::observe($to,get_defined_vars());   
        }
        else
        {
            if (!$from)
            {
                $global=Bootstrap::$main->getConfig('global');
                $from = $global['admin'];
            }
            
            
            
            if ($from)
            {
                if (strstr($from,','))
                {
                    $f=explode(',',$from);
                    $from=$f[rand(0,count($f)-1)];
                }
                
                $user = new userModel;
                $user = $user->getByEmail($from);
                
                //die(print_r($user,1));
                $token = @json_decode(Google::getUserClient($user,false,'mail')->getAccessToken(), true);
        
                $gmail = new GN_SmtpGmail;
                $gmail->email = $user['email'];
                $gmail->fullname = $user['fullname'];
                $gmail->token = $token['access_token'];
        
                $mailer = new GN_Mailer;
                $mailer->CharSet = 'UTF-8';
                $mailer->setGmail($gmail);
        
                $to=str_replace(';',',',$to);
                $to=str_replace(' ',',',$to);
                
                foreach (explode(',',$to) AS $mail) if (trim($mail) && strstr($mail,'@')) $mailer->AddAddress($mail);
        
                if ($replyto) $mailer->AddReplyTo($replyto);
        
        
                $mailer->Subject = $subject;
        
                if ($html)
                    $mailer->MsgHTML($data);
                else
                    $mailer->Body = $data;
        
                $result = $mailer->Send();
                
                return $result;
            }
            
            return false;
        }
        
    }
    
}