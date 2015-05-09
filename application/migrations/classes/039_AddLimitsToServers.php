<?php

class AddLimitsToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'webpage_limit';
    protected $_columnName2 = 'webtd_limit';
    protected $_columnName3 = 'weblink_limit';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'Integer', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName2, 'Integer', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName3, 'Integer', null, array('notnull' => false));
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName3);
        $this->removeColumn($this->_tableName1, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
