<?php

class AlterPasswdAndNotips extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'passwd';
    protected $_columnName1 = 'notips';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'integer', 2, array('notnull' => false));
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
