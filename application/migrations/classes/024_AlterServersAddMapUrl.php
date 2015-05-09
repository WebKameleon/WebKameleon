<?php
class AlterServersAddMapUrl extends Doctrine_Migration_Base
{
    protected $_tableName  = 'servers';
    protected $_columnName = 'map_url';

    public function up()
    {
        $this->addColumn($this->_tableName, $this->_columnName, 'Varchar(120)');
        $this->addIndex( 'servers', 'servers_map_url_key_idx', array('fields'=>array('map_url') ));
    }

    public function down()
    {
        $this->removeColumn($this->_tableName, $this->_columnName);
    }
}
