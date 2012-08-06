<?php
/**
 * The form on choosing a table from the specified DB
 *
 * @package DatabaseTransfer
 * @author Iwe Muiser
 * @copyright Meertens institute 2012
 */

class DatabaseTransfer_Form_ChooseTable extends Omeka_Form
{
    private $_tableNames = array();
	private $_aString;

    public function init()
    {
		parent::init();
        $this->setAttrib('id', 'databasetransfer-choosetable');
        $this->setMethod('post');
		
		$values = get_db()->getTable('Collection')->findPairsForSelectForm();

#		die(print_r($this->_tableNames));
		
		echo "testing 123 <pre>:";
		print_r($this);
		echo $this->aString;
		print "</pre>";
		
		
		$this->addElement('select', 'collection_id', array(
		    'label' => 'Select Collection',
		    'multiOptions' => $this->_tableNames,
		));
		
		//Set the collection that the imported items will belong to
#        $values = get_db()->getTable('Collection')->findPairsForSelectForm();
#        $values = array('' => 'Select Collection') + $values;
#        $this->addElement('select', 'collection_id', array(
#            'label' => 'Select Collection',
#            'multiOptions' => $values,
#        ));

		//next button!
        $this->addElement('submit', 'submit', array(
            'label' => 'Assign column names',
            'class' => 'submit submit-medium',
        ));
    }

    public function setTableNames($tableNames)
    {
        $this->_tableNames = $tableNames;
		print_r($this->_tableNames);
    }
    
	public function getTableNames()
    {
        return $this->_tableNames;
    }
}
