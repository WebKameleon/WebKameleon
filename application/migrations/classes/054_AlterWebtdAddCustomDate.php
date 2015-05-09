<?php

class AlterWebtdAddCustomDate extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'webtd';
    protected $_columnName1 = 'nd_custom_date';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'text', null, array(
            'notnull' => false
        ));

    }


    public function postUp()
    {
	Doctrine_Manager::connection()->execute('UPDATE '.$this->_tableName1.' SET '. $this->_columnName1.'=nd_valid_from');
	
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
