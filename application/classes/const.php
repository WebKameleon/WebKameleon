<?php
	
if (!defined('MEDIA_DIRNAME')) define('MEDIA_DIRNAME', 'media');
if (!defined('MEDIA_PATH')) define('MEDIA_PATH', realpath(dirname(__FILE__) . '/../../'.MEDIA_DIRNAME));
if (!defined('FILES_PATH')) define('FILES_PATH', realpath(dirname(__FILE__) . '/../../files'));

if (!defined('WIDGETS_PATH')) define('WIDGETS_PATH', APPLICATION_PATH.'/widgets');

if (!defined('PAGE_MODE_PURE'))     define ('PAGE_MODE_PURE', 0);
if (!defined('PAGE_MODE_PREVIEW'))  define ('PAGE_MODE_PREVIEW', 1);
if (!defined('PAGE_MODE_EDIT'))     define ('PAGE_MODE_EDIT', 2);
if (!defined('PAGE_MODE_EDITHF'))   define ('PAGE_MODE_EDITHF', 3);

if (!defined('VIEW_PATH')) define('VIEW_PATH', APPLICATION_PATH.'/views');

if (!defined('UIMAGES_TOKEN')) define('UIMAGES_TOKEN', 'ea8f11151a1d58cef6210fa5fc20b7db');
if (!defined('UFILES_TOKEN')) define('UFILES_TOKEN', 'x2c23796969a66cb491d74e39ac136ca');
if (!defined('INSIDELINE_TOKEN')) define('INSIDELINE_TOKEN', 'ba05863b65eefe6fa534c354bb49df6d');
if (!defined('RMEDIA_TOKEN')) define('RMEDIA_TOKEN', 'af7c92d59ca66b0d51d744e504c58219');
if (!defined('IMG_TOKEN')) define('IMG_TOKEN', 'af7c92d59ca66b0dkss4a4e504c58219');



if (!defined('ERROR_ERROR'))     define ('ERROR_ERROR', 1);
if (!defined('ERROR_WARNING'))     define ('ERROR_WARNING', 2);
if (!defined('ERROR_NOTICE'))     define ('ERROR_NOTICE', 3);

if (!defined('LOGIN_IDLE_TIME_MINS'))     define ('LOGIN_IDLE_TIME_MINS', 15);