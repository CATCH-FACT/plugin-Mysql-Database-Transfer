<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2008-2011
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package CsvImport
 */

/**
 * The CvsImport index controller class.
 *
 * @package CsvImport
 * @author CHNM
 * @copyright Center for History and New Media, 2008-2011
 */
class DatabaseTransfer_IndexController extends Omeka_Controller_Action
{
    protected $_browseRecordsPerPage = 10;

    private $_pluginConfig = array();

    public function init()
    {
        $this->session = new Zend_Session_Namespace('DatabaseTransfer');
        $this->_modelClass = 'DatabaseTransfer_Import';
    }

    public function preDispatch()
    {
        $this->view->navigation($this->_getNavigation());
    }



    private function _getMainForm()
    {
        require_once DATABASE_TRANSFER_DIRECTORY . '/forms/Main.php';
#        $csvConfig = $this->_getPluginConfig();
#        $form = new DatabaseTransfer_Form_Main($csvConfig);
        $form = new DatabaseTransfer_Form_Main();
        return $form;
    }

    private function _getChooseTableForm()
	    {
	        require_once DATABASE_TRANSFER_DIRECTORY . '/forms/ChooseTable.php';
	#        $csvConfig = $this->_getPluginConfig();
	#        $form = new DatabaseTransfer_Form_Main($csvConfig);
	        $form = new DatabaseTransfer_Form_ChooseTable();
	        return $form;
	    }


    private function _getNavigation()
    {
        return new Zend_Navigation(array(
            array(
                'label' => 'Select database',
                'action' => 'index',
                'module' => 'database-transfer',
            ),
            array(
                'label' => 'Assign fields',
                'action' => 'browse',
                'module' => 'database-transfer',
            ),
        ));
    }


	
    public function indexAction() //this happens when a form is submitted
    {
#		echo "<H1>TEST PRINT 1</H1>";
        $form = $this->_getMainForm();
        $this->view->form = $form;

        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $delimiter = $form->getValue('column_delimiter');
		$db_name = $form->getValue('db_name');
		$db_user = $form->getValue('db_user');
		$db_pw = $form->getValue('db_pw');
		$db_host = $form->getValue('db_host');
		
		$db = new Zend_Db_Adapter_Pdo_Mysql(array(
	    	'host'     => $db_host,
	    	'username' => $db_user,
	    	'password' => $db_pw,
	    	'dbname'   => $db_name));
	
#		$table = new DatabaseTransport_Table($db_host, $db_user, $db_pw, $db_name);
		#echo $db->getConnection()->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);

		if (!$db->getConnection()) {
			return $this->flashError('Database could not be reached. ' . $db->getErrorString());
        }
		
		//setting a whole bunch of nice session variables
        $this->session->originalDbname = $db_name; //replaced
        $this->session->columnDelimiter = $delimiter;
		$this->session->db_name = $db_name;
		$this->session->db_user = $db_user;
		$this->session->db_pw = $db_pw;
		$this->session->db_host = $db_host;
        $this->session->itemTypeId = $form->getValue('item_type_id');
        $this->session->itemsArePublic = $form->getValue('items_are_public');
        $this->session->itemsAreFeatured =  $form->getValue('items_are_featured');
        $this->session->collectionId = $form->getValue('collection_id');
        $this->session->ownerId = $this->getInvokeArg('bootstrap')->currentuser->id; //get the user that imported the stories
		
		$this->session->tableNames = $db->listTables();
		
        $this->_helper->redirector->goto('choose-table');
		
#		$table = new DatabaseTransfer_Table($db);

#		$this->session->columnNames = $table->getColumnNames();
#		$this->session->columnExamples = $table->getColumnExamples();
#		
#        $this->_helper->redirector->goto('map-columns');
    }
    

	public function chooseTableAction(){ #this triggers when the choose-table form button is pushed

        require_once DATABASE_TRANSFER_DIRECTORY . '/forms/ChooseTable.php';
        $form = new DatabaseTransfer_Form_ChooseTable(array(
            'tableNames' => $this->session->tableNames,
			'aString' => "ALWAYSTHESAME",
        ));
		$form->setTableNames($this->session->tableNames); 
        $this->view->form = $form;
		
		if (!$this->getRequest()->isPost()) { //to prevent the browser from redirecting 
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }
		$this->_helper->redirector->goto('map-columns');
	}

    public function mapColumnsAction()
    {
        if (!$this->_sessionIsValid()) {
            return $this->_helper->redirector->goto('index');
        }

        require_once DATABASE_TRANSFER_DIRECTORY . '/forms/Mapping.php';
#        $form = new CsvImport_Form_Mapping(array(
        $form = new DatabaseTransfer_Form_Mapping(array(
            'itemTypeId' => $this->session->itemTypeId,
            'columnNames' => $this->session->columnNames,
            'columnExamples' => $this->session->columnExamples,
        ));
        $this->view->form = $form;
                
        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $columnMaps = $form->getMappings();
        if (count($columnMaps) == 0) {
            return $this->flashError('Please map at least one column to an '
                . 'element, file, or tag.');
        }
        
        $databaseTransfer = new DatabaseTransfer_Import();
        foreach ($this->session->getIterator() as $key => $value) {
            $setMethod = 'set' . ucwords($key);
            if (method_exists($databaseTransfer, $setMethod)) {
                $databaseTransfer->$setMethod($value);
            }
        }
        $databaseTransfer->setColumnMaps($columnMaps);
        $databaseTransfer->setStatus(DatabaseTransfer_Import::QUEUED);
        $databaseTransfer->forceSave();

        $databaseTransfer = $this->_getPluginConfig();
        $jobDispatcher = Zend_Registry::get('job_dispatcher');
        $jobDispatcher->setQueueName('imports');
        $jobDispatcher->send('DatabaseTransfer_ImportTask',
            array(
                'importId' => $databaseTransfer->id,
                'memoryLimit' => @$databaseTransfer['memoryLimit'],
                'batchSize' => @$databaseTransfer['batchSize'],
            )
        );

        $this->session->unsetAll();
        $this->flashSuccess('Successfully started the import. Reload this page '
            . 'for status updates.');
        $this->_helper->redirector->goto('browse');
    }
    
    public function omekaCsvAction()
    {
		//LOTS OF CODE NEEDED HERE
    }
    
}
