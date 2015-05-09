<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class RenameWidgetNewsToArticle extends Doctrine_Migration_Base
{
    public function up()
    {
        Doctrine_Manager::connection()->exec('UPDATE webtd SET widget = ? WHERE widget = ?', array('article', 'news'));
    }

    public function down()
    {

    }
}