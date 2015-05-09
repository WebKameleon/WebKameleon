<?php

class AddAppEngineRewriteToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'appengine_rewrite';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'Text', null, array('notnull' => false ));
        
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
