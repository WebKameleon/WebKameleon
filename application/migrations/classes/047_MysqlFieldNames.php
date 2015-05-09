<?php

class MysqlFieldNames extends Doctrine_Migration_Base
{


    public function up()
    {
	
	$this->changeColumn('passwd','email','varchar',255);
	
	if (strtolower(Doctrine_Manager::connection()->getDriverName())=='pgsql')
	{
	    Doctrine_Manager::connection()->exec("ALTER TABLE webtd RENAME repeat TO contents_repeat");

	}
    }

    public function down()
    {
	$this->changeColumn('passwd','email','text');
	
	if (strtolower(Doctrine_Manager::connection()->getDriverName())=='pgsql')
	{
	    Doctrine_Manager::connection()->exec("ALTER TABLE webtd RENAME contents_repeat TO repeat");

	}	
    }
}
