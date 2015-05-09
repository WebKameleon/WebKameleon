<?php

class CreateTableObserver extends Doctrine_Migration_Base
{
    private $_tableName = 'observer';
    
    function up()
    {
        $int2=Doctrine_Manager::connection()->getDbh()->getAttribute(PDO::ATTR_DRIVER_NAME)=='pgsql'?'int2':'integer(2)';
        
        $this->createTable($this->_tableName, array(
            'id' => array(
                'type' => 'integer',
                'notnull' => true,
                'primary' => true,
                'autoincrement' => true,
            ),
            'pri' => array(
                'type' => 'integer',
                'notnull' => true,
            ),
            'event' => array(
                'type' => 'character varying(64)',
                'notnull' => true,
            ),
            'active' => array(
                'type' => 'integer',
                'notnull' => true,
                'default' => 1,
                'value' => 1,
            ),
            'result' => array(
                'type' => $int2,
                'notnull' =>false 
            ),
            'days' => array(
                'type' => $int2,
                'notnull' =>false 
            ),
            'mail_from' => array(
                'type' => 'character varying(255)',
                'notnull' =>false 
            ),
            'mail_to' => array(
                'type' => 'character varying(255)',
                'notnull' =>false
            ),
            'mail_subject' => array(
                'type' => 'character varying(255)',
                'notnull' =>false
            ),
            'mail_reply' => array(
                'type' => 'character varying(255)',
                'notnull' =>false
            ),
            'mail_cc' => array(
                'type' => 'text',
                'notnull' =>false
            ),

            'mail_msg' => array(
                'type' => 'text',
                'notnull' =>false
            ),
            'mail_html' => array(
                'type' => $int2,
                'notnull' => false,
            ),
            'lang' => array(
                'type' => 'text',
                'notnull' =>true
            ),

        ));

    }

    public function down()
    {
        $this->dropTable($this->_tableName);
    }
}
