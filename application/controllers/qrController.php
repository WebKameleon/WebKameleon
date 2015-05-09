<?php

class qrController extends Controller
{

    public function get()
    {
	include_once LIBRARY_PATH.'/phpqrcode/qrlib.php';
	
	
	QRcode::png($_GET['url'],false,QR_ECLEVEL_L,isset($_GET['size'])?$_GET['size']:3);
	die();
	
    }
}


    
