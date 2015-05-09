<?php

class ReassignWidgetTypes2 extends Doctrine_Migration_Base
{
    protected $tab = array(
	30260 => 30005,
	30270 => 30075,
	30150 => 30125
    );

    public function up()
    {
	foreach ($this->tab AS $old=>$new)
	    Doctrine_Manager::connection()->exec("UPDATE webtd SET type=$new WHERE type=$old");
    }
    
   

    public function down()
    {
	foreach ($this->tab AS $old=>$new)
	    Doctrine_Manager::connection()->exec("UPDATE webtd SET type=$old WHERE type=$new");
    }
}
