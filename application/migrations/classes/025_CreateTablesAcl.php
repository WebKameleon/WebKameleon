<?php

class CreateTablesAcl extends Doctrine_Migration_Base
{
    private $_tableName1 = 'acl_users';
    private $_tableName2 = 'acl_pages';
    

    public function up()
    {
        $this->createTable($this->_tableName1, array(
            'id' => array(
                'type' => 'integer',
                'notnull' => true,
                'primary' => true,
                'autoincrement' => true,
            ),
            'username' => array(
                'type' => 'character varying(64)',
                'notnull' => true,
            ),
            'password' => array(
                'type' => 'character varying(64)',
                'notnull' => true,
            ),
            'server' => array(
                'type' => 'integer',
                'notnull' => true,
            ),
        ));

        $this->createTable($this->_tableName2, array(
            'id' => array(
                'type' => 'integer',
                'notnull' => true,
                'primary' => true,
                'autoincrement' => true,
            ),
            'username' => array(
                'type' => 'character varying(64)',
                'notnull' => true,
            ),
            'server' => array(
                'type' => 'integer',
                'notnull' => true,
            ),
            'page' => array(
                'type' => 'integer',
                'notnull' => true,
            ),
            'ok' => array(
                'type' => 'integer',
                'notnull' => true,
            ),
        ));
 

        
        $this->addIndex($this->_tableName1,$this->_tableName1.'_server_key',array('fields'=>array('server')));
        $this->addIndex($this->_tableName2,$this->_tableName2.'_all_key',array('fields'=>array('server','page','username')));        
        
    }

    public function down()
    {
        $this->dropTable($this->_tableName2);
        $this->dropTable($this->_tableName1);
        
    }
}
