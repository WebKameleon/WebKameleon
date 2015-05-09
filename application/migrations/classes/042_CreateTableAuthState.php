<?php

class CreateTableAuthState extends Doctrine_Migration_Base
{
    private $_tableName = 'authstate';

    
    public function up() {
      $this->createTable($this->_tableName,array(
             'state'=>array('type'=>'varchar(32)'),
             'nd_create'=>array('type'=>'integer'),
             'nd_complete'=>array('type'=>'integer default 0'),
             'ip'=>array('type'=>'varchar(32)'),
             'nd_user_joined'=>array('type'=>'integer')
        ));
      
        $this->addIndex( $this->_tableName, $this->_tableName.'_state_key', array('fields'=>array('state')) );
    }
    
    public function down() {
        
        $this->dropTable($this->_tableName);
    }
    
 
}
