<?php

class ReassignWidgetTypes extends Doctrine_Migration_Base
{

    public function up()
    {
	Doctrine_Manager::connection()->exec("UPDATE webtd SET type=30000+10*(type-29999) WHERE type>29999");
	Doctrine_Manager::connection()->exec("UPDATE webtd SET type=30000 WHERE type=29999");
    }
    
   

    public function down()
    {
	Doctrine_Manager::connection()->exec("UPDATE webtd SET type=29999 WHERE type=30000");
  	Doctrine_Manager::connection()->exec("UPDATE webtd SET type=29999+((type-30000)/10) WHERE type>30000");
	
    }
}
