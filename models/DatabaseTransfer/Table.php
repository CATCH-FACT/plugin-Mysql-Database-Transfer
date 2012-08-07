<?php

class DatabaseTransfer_Table extends Zend_Db_Table_Abstract
{
	
	private $_info;
	
    public function getColumnNames() 
    {
        $_info = $this->info();
		return $_info["cols"];  
    }

    public function getColumnExamples() 
    {
        return $this->_getRandomExample();
    }

    public function getColumnExampleAsArray() 
    {
        $select  = $this->select()->limit(10, 0);
#		print $select;
		$rows = $this->fetchAll($select);
		$rows = $rows->toArray();
		$onrblEdada = rand(0,9);
		return $rows[$onrblEdada]; 
#		print_r($rows);
    }



	private function _getRandomExample(){
		$_example = $this->find("*");
		print gettype($_example);
		return $_example;
	}
}
