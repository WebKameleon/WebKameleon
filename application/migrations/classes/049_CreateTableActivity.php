<?php

class CreateTableActivity extends Doctrine_Migration_Base
{
    private $_tableName = 'activity';

    
    public function up() {
      $this->createTable($this->_tableName,array(
            'id' => array('type' => 'integer','notnull' => true,'primary' => true,'autoincrement' => true),
             'login_id'=>array('type'=>'integer'),
             'nd_click'=>array('type'=>'integer'),
             'click_type'=>array('type'=>'varchar(1)'),
             'table_name'=>array('type'=>'varchar(32)'),
             'table_id'=>array('type'=>'integer'),
             
        ));
      
        $this->addIndex( $this->_tableName, $this->_tableName.'_login_key', array('fields'=>array('login_id')) );
    }
    
    public function down() {
        
        $this->dropTable($this->_tableName);
    }
    
 
}
