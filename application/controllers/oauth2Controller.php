<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

require_once 'google-api-php-client/src/Google_Client.php';

class oauth2Controller extends Controller
{
    public function get_token()
    {
        $user = Bootstrap::$main->session('user');
        if ($user == null) {
            $this->redirect('auth/get');
            return;
        }

        Google::getUserClient(null,false);
        $this->redirect(Bootstrap::$main->session('redirect') ?: 'index/get');
    }

    public function calendars()
    {
        require_once 'google-api-php-client/src/contrib/Google_CalendarService.php';

        $client = Google::getUserClient(null,false,'calendar');
        $calendar = new Google_CalendarService($client);
        $calendarList = $calendar->calendarList->listCalendarList();
        die('<pre>' . print_r($calendarList, 1) . PHP_EOL);
    }

    public function customsearch()
    {
        require_once 'google-api-php-client/src/contrib/Google_CustomsearchService.php';

        $client = Google::getUserClient(null,false,'cs');
        $customsearch = new Google_CustomsearchService($client);
        $list = $customsearch->cse->listCse('gamma');
        die('<pre>' . print_r($list, 1) . PHP_EOL);
    }

    public function analytics()
    {
        $service = Google::getAnalyticsService();

        $accounts = $service->management_accounts->listManagementAccounts();
//        die('<pre>' . print_r($accounts, 1) . PHP_EOL);
        $properties = $service->management_webproperties->listManagementWebproperties($accounts['items'][0]['id']);
        die('<pre>' . print_r($properties, 1) . PHP_EOL);
    }

    public function mail()
    {
        $user  = Bootstrap::$main->session('user');
        $token = json_decode(Google::getUserClient($user,false,'mail')->getAccessToken(), true);

        $gmail = new GN_SmtpGmail;
        $gmail->email = $user['email'];
        $gmail->fullname = $user['fullname'];
        $gmail->token = $token['access_token'];

        $mailer = new GN_Mailer;
        $mailer->CharSet = 'UTF-8';
        $mailer->setGmail($gmail);

        $mailer->AddAddress('radoslaw.szczepaniak@gmail.com');
        $mailer->Subject = 'Witam';
        $mailer->MsgHTML('<h1>HELLO WORLD!</h1>');

        echo $mailer->Send() ? 'SUCCESS' : 'FAIL';
        die;
    }

    public function test()
    {
        $request = new Google_HttpRequest('https://spreadsheets.google.com/feeds/spreadsheets/private/full');

        $client = Google::getUserClient(null,false,'spreadsheets');
        $response = $client->getIo()->authenticatedRequest($request);

        $xml = $response->getResponseBody();

        die('<pre>' . print_r($response->getResponseBody(), 1) . PHP_EOL);
    }
}