<?php

/**
 * CsvImport_File - represents a csv file
 * 
 * @package CsvImport
 * @author CHNM
 **/
class DatabaseTransfer_Table implements IteratorAggregate
{

	private $_db;
    private $_filePath;
    private $_columnNames = array();
    private $_columnExamples = array();
    private $_delimiter;
    private $_parseErrors = array();
    private $_rowIterator;

    /**
     * @param string $filePath Absolute path to the file.
     * @param string|null $delimiter Optional Column delimiter for the CSV file.
     */
    public function __construct($filePath, $delimiter = null) 
    {
        $this->_filePath = $filePath;
        if ($delimiter) {
            $this->_delimiter = $delimiter;
        }
    }

    /**
     * Absolute path to the file.
     * 
     * @return string
     */
    public function getFilePath() 
    {
        return $this->_filePath;
    }

    /**
     * Get an array of headers for the column names
     * 
     * @return array
     */
    public function getColumnNames() 
    {
        if (!$this->_columnNames) {
            throw new LogicException("CSV file must be validated before "
                . "retrieving the list of columns.");
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
