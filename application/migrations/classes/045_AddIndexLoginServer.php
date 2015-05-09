<?php

class AddIndexLoginServer extends Doctrine_Migration_Base
{
    private $_tableName1 = 'login';
    private $_tableName2 = 'login_arch';

    
    public function up() {
      
        $this->addIndex( $this->_tableName1, $this->_tableName1.'_server_idx', array('fields'=>array('server')) );
        $this->addIndex( $this->_tableName2, $this->_tableName2.'_server_idx', array('fields'=>array('server')) );
    }
    
    public function down() {
        
        $this->removeIndex($this->_tableName2,$this->_tableName2.'_server');
        $this->removeIndex($this->_tableName1,$this->_tableName1.'_server');
    }
    
 
}