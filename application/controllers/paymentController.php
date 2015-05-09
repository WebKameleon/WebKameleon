<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class paymentController extends Controller
{
    const PAYMENT_STARTED   = 'payment-started';
    const PAYMENT_COMPLETED = 'payment-completed';
    const PAYMENT_FRAUD     = 'payment-fraud';

    protected $config;

    public function init()
    {
        parent::init();

        $this->config = Bootstrap::$main->getConfig('payment');
    }

    /**
     * @return array
     */
    protected function get_info()
    {
        $server = new serverModel($this->id);
        $serverData = $server->data();
        $serverData['tmb'] = Tools::get_template_thumb($serverData);

        $lang = Bootstrap::$main->session('ulang');

        $info = $this->config['default'];

        if (isset($this->config[$lang])) {
            $info = array_merge($info, $this->config[$lang]);
        }
        
        $field='price_'.$lang;
        $amount=$server->$field?:$server->price_en;
        
        if ($amount) $info['amount']=$amount;
        
        $field='social_template_price_'.$lang;
        $info['template_price']=$server->$field?:$server->social_template_price_en;
        
        $info['total']=$info['amount']+$info['template_price'];
        $info['total100x']=$info['total'] * 100;

        $info['amount100x'] = $info['amount'] * 100;
        $info['invoice_link'] = sprintf($info['invoice_link'], urlencode($serverData['nazwa_long'] ? : $serverData['nazwa']));
        $info['invoice_info'] = $this->trans('To receive a VAT invoice, enter the invoice details', $info['invoice_link']);

        $serverData['payment_expire'] = Tools::date($serverData['nd_expire'] + $info['duration']);

        $payment = $this->config;
        $payment['info'] = $info;
        $payment['lang'] = $lang;
        $payment['server'] = $serverData;
        $payment['user']=Bootstrap::$main->session('user');
        $payment['user']['name']=explode(' ',$payment['user']['fullname']);
        $payment['user']['first_name']=$payment['user']['name'][0];
        $payment['user']['last_name']=$payment['user']['name'][1];
        

        
        return $payment;
    }

    public function get()
    {
        $payment = $this->get_info();
  
        return array(
            'tac' => $payment['info']['tac'],
            'payment' => $payment
        );
    }

    /*** BANK TRANSFER ***/

    public function bank_transfer()
    {
        $payment = $this->get_info();

        return array(
            'payment' => $payment
        );
    }

    /*** PAYU ***/

    public function payu()
    {
        $payment = $this->get_info();

        $payu = paymentModel::newPayment(paymentModel::TYPE_PAYU, $payment);
        $payment['custom_id'] = $payu->custom_id;
        $payment['client_ip'] = $_SERVER['REMOTE_ADDR'];
        //sig = md5 ( pos_id + pay_type + session_id + pos_auth_key + amount
        // + desc + desc2 + trsDesc + order_id + first_name + last_name
        // + street + street_hn + street_an + city + post_code + country + email + phone + language + client_ip + ts + key1 )

        
        $payment['ts'] = time();
        $payment['sig'] = md5(''
            . $payment['payu']['pos_id']
            . $payment['custom_id']
            . $payment['payu']['pos_auth_key']
            . $payment['info']['total100x']
            . $payment['server']['nazwa_long']
            . $payment['user']['first_name']
            . $payment['user']['last_name']
            . $payment['user']['email']
            . $payment['client_ip']
            . $payment['ts']
            . $payment['payu']['key1']
        );

        
        Bootstrap::$main->session('custom_id',$payment['custom_id']);
        
        $this->report(self::PAYMENT_STARTED, $payu);

        return array(
            'payment' => $payment
        );
    }

    /**
     * @return array|bool
     */
    public function payu_process()
    {
        
        $this->log('$_REQUEST', $_REQUEST);

        if (empty($_REQUEST)) {
            return false;
        }

        $req = array();
        $req['pos_id'] = $_REQUEST['pos_id'];
        $req['session_id'] = $_REQUEST['session_id'];
        $req['ts'] = time() + microtime(true);
        $req['sig'] = md5($req['pos_id'] . $req['session_id'] . $req['ts'] . $this->config['payu']['key1']);
        $req = http_build_query($req);

        $host = 'www.platnosci.pl';
        $url = 'https://' . $host . '/paygw/UTF/Payment/get/txt';

        $ch = curl_init();
        
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Host: ' . $host,
            'Connection: close'
        ));
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $this->log('req', $req);
        $this->log('res', $res);
        $this->log('curl error', $err);
        

        $tmp = array();
        foreach (explode("\n", str_replace("\r", '', $res)) as $line) {
            if (strpos($line,':'))
            {
                list ($key, $val) = explode(': ', $line);
                $tmp[$key] = $val;
            }
        }
        $res = $tmp;

        if ($res['status'] != 'OK') {
            return false;
        }

        if ($res['trans_status'] == 99) { 
            $payment = new paymentModel;
            $payment->findCustom($res['trans_session_id']);
    

            if (!$payment->id) {
                $this->report(self::PAYMENT_FRAUD, null, $_REQUEST, 'payment not found');
                return false;
            }
    
            if ($payment->amount * 100 > $res['trans_amount']) {
                $this->report(self::PAYMENT_FRAUD, $payment, $_REQUEST, 'payment amount mismatch');
                return false;
            }
    
            if ($payment->response_data) {
                $this->report(self::PAYMENT_FRAUD, $payment, $_REQUEST, 'payment already processed');
                return false;
            }
    
            $payment->transaction_id = $res['trans_id'];
            $payment->setResponseData($res);

    
            $this->payment_done($payment);
            $this->report(self::PAYMENT_COMPLETED, $payment);
        
            return $payment->data();
        }

        
        return false;
    }

    public function success()
    {
        return $this->status();
    }

    public function cancel()
    {
        return $this->status();
    }

    
    
    public function payu_cancel()
    {
        return $this->cancel();
    }


    public function payu_success()
    {
        return $this->success();
    }

    /*** PAYPAL **/

    public function paypal()
    {
        $payment = $this->get_info();

        $paypal = paymentModel::newPayment(paymentModel::TYPE_PAYPAL, $payment);
        $payment['custom_id'] = $paypal->custom_id;
        $payment['return'] = Bootstrap::$main->getKameleonUrl() . 'payment/paypal_success';
        $payment['cancel_return'] = Bootstrap::$main->getKameleonUrl() . 'payment/paypal_cancel';
        $payment['notify_url'] = Bootstrap::$main->getKameleonUrl() . 'public/paypal_notify';

        $this->report(self::PAYMENT_STARTED, $paypal);

        Bootstrap::$main->session('custom_id',$payment['custom_id']);
        
        return array(
            'payment' => $payment
        );
    }

    public function paypal_process()
    {
        $this->log('$_REQUEST', $_REQUEST);

        if (empty($_REQUEST)) {
            return false;
        }

        $payment = new paymentModel;
        $payment->findTransaction($_REQUEST['txn_id']);
       
        if ($payment->id) {
            $this->report(self::PAYMENT_FRAUD, null, $_REQUEST, 'payment already processed');
            return false;
        }

        $payment->findCustom($_REQUEST['custom']);

        if (!$payment->id) {
            $this->report(self::PAYMENT_FRAUD, null, $_REQUEST, 'payment not found');
            return false;
        }

        if ($_REQUEST['payment_status'] != 'Completed') {
            return false;
        }

        $customData = $payment->getCustomData();

        if ($_REQUEST['mc_currency'] != $customData['info']['currency']) {
            $this->report(self::PAYMENT_FRAUD, $payment, $_REQUEST, 'payment currency mismatch');
            return false;
        }

        if ($payment->amount > $_REQUEST['mc_gross']) {
            $this->report(self::PAYMENT_FRAUD, $payment, $_REQUEST, 'payment amount mismatch');
            return false;
        }

        $req = array();
        $req['cmd'] = '_notify-validate';
        foreach ($_REQUEST as $key => $value) {
            $req[$key] = stripslashes($value);
        }
        $req = http_build_query($req);

        if ($customData['paypal']['sandbox']) {
            $host = 'www.sandbox.paypal.com';
        } else {
            $host = 'www.paypal.com';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $host . '/cgi-bin/webscr');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Host: ' . $host,
            'Connection: close'
        ));
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $this->log('req', $req);
        $this->log('res', $res);
        $this->log('curl error', $err);

        if (strcmp($res, 'VERIFIED') != 0) {
            return false;
        }

        $payment->transaction_id = $_REQUEST['txn_id'];
        $payment->setResponseData($_REQUEST);

        $this->payment_done($payment);
        $this->report(self::PAYMENT_COMPLETED, $payment);

        return $payment->data();
    }

    
    protected function status()
    {
        $payment = new paymentModel;
        $payment->findCustom(Bootstrap::$main->session('custom_id'));
     
        $data = $payment->getCustomData();
        
        $data['server']['tmb'] = Tools::get_template_thumb($data['server']);

        return array(
            'payment' => $data
        );
    }

    public function paypal_cancel()
    {
        return $this->cancel();
    }
    

    public function paypal_success()
    {
        return $this->success();
    }

    protected function payment_done(paymentModel $payment)
    {
        $custom = $payment->getCustomData();

        
        $server = new serverModel($custom['server']['id']);
        if ($server->nd_expire > time() ) $server->nd_expire += $custom['info']['duration'];
        else $server->nd_expire = time() + $custom['info']['duration'];
        $server->nd_last_payment = time();
        $server->social_template_price_en=0;
        $server->social_template_price_pl=0;
        $server->save();

        $custom['server'] = $server->data();
        $custom['server']['payment_expire'] = Tools::date($server->nd_expire);
        $payment->setCustomData($custom);
        $payment->save();
    }

    /**
     * @param string $what
     * @param mixed $data
     */
    protected function log($what, $data)
    {
        if (!is_writable(APPLICATION_PATH . '/logs')) return;
        file_put_contents(APPLICATION_PATH . '/logs/payments.log', date('Y-m-d H:i:s') . ' ' . $what . ': ' . print_r($data, 1) . PHP_EOL, FILE_APPEND);
    }

    /**
     * @param string $action
     * @param paymentModel $payment
     * @param string $extra_data
     * @param string $extra_message
     */
    protected function report($action, paymentModel $payment = null, $extra_data = null, $extra_message = null)
    {
        $data = array();

        if ($payment)
        {
            $data['payment'] = $payment->data();
            $custom_data=$payment->getCustomData();
            $data['server']=$custom_data['server'];
            $data['him']=$custom_data['user']['email'];
        }

        if ($extra_data)
            $data['extra_data'] = $extra_data;

        if ($extra_message)
            $data['extra_message'] = $extra_message;

        
        
        Observer::observe($action, $data);            
            
    }
}