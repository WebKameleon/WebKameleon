<?php

class AlterWebtdAddTrailer extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'webtd';
    protected $_columnName1 = 'trailer';


    public function up()
    {
	$this->addColumn($this->_tableName1, 'nd_custom_date2','Integer',null,array('notnull' => false));
	$this->addColumn($this->_tableName1, 'nd_custom_date_end2','Integer',null,array('notnull' => false));

	
	
	

	
        $this->addColumn($this->_tableName1, $this->_columnName1, 'text', null, array('notnull' => false));

    }
    
    public function postUp()
    {
	Doctrine_Manager::connection()->exec("UPDATE webtd SET nd_custom_date2=CAST(coalesce(nd_custom_date, '0') AS integer)");
	Doctrine_Manager::connection()->exec("UPDATE webtd SET nd_custom_date_end2=CAST(coalesce(nd_custom_date_end, '0') AS integer)");
	
	Doctrine_Manager::connection()->exec("ALTER TABLE webtd DROP COLUMN nd_custom_date");
	Doctrine_Manager::connection()->exec("ALTER TABLE webtd DROP COLUMN nd_custom_date_end");


	Doctrine_Manager::connection()->exec("ALTER TABLE webtd ADD COLUMN nd_custom_date Integer");
	Doctrine_Manager::connection()->exec("ALTER TABLE webtd ADD COLUMN nd_custom_date_end Integer");

	Doctrine_Manager::connection()->exec("UPDATE webtd SET nd_custom_date=nd_custom_date2");
	Doctrine_Manager::connection()->exec("UPDATE webtd SET nd_custom_date_end=nd_custom_date_end2");

	
	Doctrine_Manager::connection()->exec("ALTER TABLE webtd DROP COLUMN nd_custom_date2");
	Doctrine_Manager::connection()->exec("ALTER TABLE webtd DROP COLUMN nd_custom_date_end2");

    }
    
    

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
