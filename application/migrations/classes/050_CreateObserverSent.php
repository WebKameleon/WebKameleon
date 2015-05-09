<?php

class CreateTableObsererSent extends Doctrine_Migration_Base
{
    private $_tableName = 'observersent';

    
    public function up() {
      $this->createTable($this->_tableName,array(
            'id' => array('type' => 'integer','notnull' => true,'primary' => true,'autoincrement' => true),
             'email'=>array('type'=>'varchar(255)'),
             'nd_sent'=>array('type'=>'integer'),
             'event'=>array('type'=>'varchar(64)'),
        ));
      
        $this->addIndex( $this->_tableName, $this->_tableName.'_key', array('fields'=>array('email,event')) );
    }
    
    public function down() {
        
        $this->dropTable($this->_tableName);
    }
    
 
}
