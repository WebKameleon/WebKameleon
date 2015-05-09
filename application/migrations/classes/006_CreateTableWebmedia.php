<?php

class CreateTableWebmedia extends Doctrine_Migration_Base
{
    private $_tableName = 'webmedia';

    public function up()
    {
        $this->createTable($this->_tableName, array(
            'id' => array(
                'type' => 'integer',
                'notnull' => true,
                'primary' => true,
                'autoincrement' => true,
            ),
            'target' => array(
                'type' => 'character varying(255)',
                'notnull' => true,
            ),
            'username' => array(
                'type' => 'character varying(32)',
                'notnull' => true,
            ),
            'server' => array(
                'type' => 'integer',
                'notnull' => true,
            ),
        ));
        
        $this->addIndex($this->_tableName,$this->_tableName.'_target_key',array('fields'=>array('target')));
        $this->addIndex($this->_tableName,$this->_tableName.'_server_key',array('fields'=>array('server')));        
        
    }

    public function down()
    {
        $this->dropTable($this->_tableName);
    }
}
