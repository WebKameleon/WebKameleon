<?php

class CreateTableWebCat extends Doctrine_Migration_Base
{
    private $_tableName = 'webcat';

    
    public function up() {
        $this->createTable($this->_tableName,array(
             'id' => array('type' => 'integer','notnull' => true,'primary' => true,'autoincrement' => true),
             'server'=>array('type'=>'integer'),
             'tdsid'=>array('type'=>'integer'),
             'category'=>array('type'=>'varchar(32)'),
        ));
      
        $this->addIndex( $this->_tableName, $this->_tableName.'_all_key', array('fields'=>array('server','category')) );
        $this->addIndex( $this->_tableName, $this->_tableName.'_sid_key', array('fields'=>array('tdsid')) );
    }
    
    public function down() {
        
        $this->dropTable($this->_tableName);
    }
    
 
}
