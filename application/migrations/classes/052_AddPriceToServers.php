<?php

class AddPriceToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'price_en';
    protected $_columnName2 = 'price_pl';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'Double precision', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName2, 'Double precision', null, array('notnull' => false));
        

    }

    public function down()
    {
        
        $this->removeColumn($this->_tableName1, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
