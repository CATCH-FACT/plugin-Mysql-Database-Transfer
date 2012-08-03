<?php
/**
 * The form on choosing a table from the specified DB
 *
 * @package DatabaseTransfer
 * @author Iwe Muiser
 * @copyright Meertens institute 2012
 */

class DatabaseTransfer_Form_Main extends Omeka_Form
{
	private $_db_host = "127.0.0.1";
	private $_db_user = "root";
	private $_db_pw = "";
	private $_db_name = "verhalenbank_plus";
	
    private $_columnDelimiter = ',';
    private $_fileDestinationDir;
    private $_maxFileSize;

    public function init()
    {
        parent::init();
        $this->setAttrib('id', 'databasetransfer');
        $this->setMethod('post');

		//Set the collection that the imported items will belong to
        $values = get_db()->getTable('Collection')->findPairsForSelectForm();
        $values = array('' => 'Select Collection') + $values;
        $this->addElement('select', 'collection_id', array(
            'label' => 'Select Collection',
            'multiOptions' => $values,
        ));
		//next button!
        $this->addElement('submit', 'submit', array(
            'label' => 'Assign column names',
            'class' => 'submit submit-medium',
        ));
    }
}
