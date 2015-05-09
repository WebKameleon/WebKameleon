<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class commonWidget
{
    protected static function getLibUrl()
    {
        return  Bootstrap::$main->session('template_images') . '/' . Widget::$widget_dir . '/common/';
    }

    public static function loadFancybox()
    {
        Bootstrap::$main->tokens->loadLibs(self::getLibUrl() . 'fancybox2/jquery.fancybox.css', 'css');
        Bootstrap::$main->tokens->loadLibs(self::getLibUrl() . 'fancybox2/jquery.fancybox.pack.js', 'js');
        Bootstrap::$main->tokens->loadJQuery = true;
    }
}