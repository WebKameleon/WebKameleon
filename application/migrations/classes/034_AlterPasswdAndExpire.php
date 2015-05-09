<?php

class AlterPasswdAndExpire extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'passwd';
    protected $_columnName1 = 'nd_last_expire';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'integer', null, array('notnull' => false, 'default'=>0, 'value'=>0));
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
