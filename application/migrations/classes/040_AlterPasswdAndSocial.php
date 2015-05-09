<?php

class AlterPasswdAndSocial extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'passwd';
    protected $_columnName1 = 'photo';
    protected $_columnName2 = 'link';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'text', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName2, 'text', null, array('notnull' => false));
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
