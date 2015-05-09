<?php

class AddSocialTemplatePriceToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'social_template_price_en';
    protected $_columnName2 = 'social_template_price_pl';

    protected $_columnName0 = 'from_social_template';

    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'Double precision', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName2, 'Double precision', null, array('notnull' => false));
        
        $this->changeColumn($this->_tableName1,$this->_columnName0,'Varchar(128)');

    }

    public function down()
    {
        $this->changeColumn($this->_tableName1,$this->_columnName0,'Varchar(32)');
        
        $this->removeColumn($this->_tableName1, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
