<?php

class AddAppMediaPassToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'media_pass';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'varchar(64)', null, array('notnull' => false ));
        
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
