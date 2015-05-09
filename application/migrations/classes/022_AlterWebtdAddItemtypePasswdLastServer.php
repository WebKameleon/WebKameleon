<?php

class AlterWebtdAddItemtypePasswdLastServer extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'webtd';
    protected $_columnName1 = 'itemtype';

    protected $_tableName2  = 'passwd';
    protected $_columnName2 = 'lastserver';
    

    public function up()
    {
        $this->changeColumn('passwd','email','varchar',255);
        
        $this->addColumn($this->_tableName1, $this->_columnName1, 'text', null, array('notnull' => false));
        $this->addColumn($this->_tableName2, $this->_columnName2, 'integer', null, array('notnull' => false));

        $this->addIndex( 'passwd', 'passwd_email_key_idx', array('fields'=>array('email')) );
        $this->addIndex( 'passwd', 'passwd_lastserver_key', array('fields'=>array('lastserver')) );
    }

    public function down()
    {
        $this->removeIndex('passwd','passwd_email_key');
        
        $this->removeColumn($this->_tableName2, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
