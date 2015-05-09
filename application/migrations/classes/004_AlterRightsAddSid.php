<?php

class AlterRightsAddSid extends Doctrine_Migration_Base
{

    protected $_tableName  = 'rights';
    protected $_columnName = 'sid';

    public function up()
    {
        $this->addColumn($this->_tableName, $this->_columnName, 'int', null, array(
            'notnull' => false, 'autoincrement' => true,
        ));

    }

    public function down()
    {

        $this->removeColumn($this->_tableName, $this->_columnName);
    }
}
