<?php

/**
 * CsvImport_File - represents a csv file
 * 
 * @package CsvImport
 * @author CHNM
 **/
class DatabaseTransfer_Table extends Zend_Db_Table_Abstract
{
	private $_db;
    private $_columnNames = array();
    private $_columnExamples = array();
    private $_delimiter;
    private $_parseErrors = array();
    private $_rowIterator;

 	protected $_observer;

    public function init()
    {
        $this->_observer = new MyObserverClass();
    }


    /**
     * Get an array of headers for the column names
     * 
     * @return array
     */
    public function getColumnNames() 
    {
        if (!$this->_columnNames) {
            throw new LogicException("Database must be connected properly before getting ColumnNames.");
        }
        return $this->_columnNames;    
    }

    /**
     * Get an array of example data for the columns.
     * 
     * @return array Examples have the same order as the column names.
     */
    public function getColumnExamples() 
    {
        if (!$this->_columnExamples) {
            throw new LogicException("CSV file must be validated before "
                . "retrieving list of column examples.");
        }
        return $this->_columnExamples;    
    }



    /**
     * Get iterator.
     * 
     * @return CsvImport_RowIterator
     */
    public function getIterator()
    {
        if (!$this->_rowIterator) {
            $this->_rowIterator = new CsvImport_RowIterator(
                $this->getFilePath(), $this->_delimiter);
        }
        return $this->_rowIterator;
    }


    public function getErrorString()
    {
        return join(' ', $this->_parseErrors);
    }
}
