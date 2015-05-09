<?php

class AlterPasswdAndRights extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'passwd';
    protected $_columnName1 = 'servers';

    protected $_tableName2  = 'rights';
    protected $_columnName2 = 'owner';

    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'int', null, array(
            'notnull' => false, 'default' => 1, 'value' => 1
        ));
        $this->addColumn($this->_tableName2, $this->_columnName2, 'int', null, array(
            'notnull' => false,
        ));

    }

    public function down()
    {
	    $this->removeColumn($this->_tableName2, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
