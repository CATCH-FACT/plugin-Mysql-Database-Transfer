<?php

class DatabaseTransfer_Table extends Zend_Db_Table_Abstract
{
	
	private $_info;
	private $_db_name;
	private $_db_user;
	private $_db_pw;
	private $_db_host;
	private $_db_table;
	
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
        $select  = $this->select()->limit(5, 0); //get 5 first items from db
		$rows = $this->fetchAll($select);
		$rows = $rows->toArray();
		$onrblEdada = rand(0,4);
		return $rows[$onrblEdada]; //return a random item from the set
    }

    public function getColumnAllAsArray() 
    {
        $select  = $this->select();
		$rows = $this->fetchAll($select);
		$rows = $rows->toArray();
		return $rows; 
    }

	private function _getRandomExample(){
		$_example = $this->find("*");
		print gettype($_example);
		return $_example;
	}
	
	/**
     * Get iterator.
     * 
     * @return 
     */
    public function getIterator()
    {
        if (!$this->_rowIterator) {
            $this->_rowIterator = new DatabaseTransfer_RowIterator(
                $this->db_name,
				$this->db_user,
				$this->db_pw,
				$this->db_host, 
				$this->db_table);
        }
        return $this->_rowIterator;
    }
}
