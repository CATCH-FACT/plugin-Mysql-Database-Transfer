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

    public function indexAction() //this happens when the form is submitted
    {
		echo "<H1>TEST PRINT 1</H1>";
        $form = $this->_getMainForm();
        $this->view->form = $form;

        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

#        if (!$form->csv_file->receive()) {
#            return $this->flashError("Error uploading file.  Please try again.");
#        }

#        $filePath = $form->csv_file->getFileName();
#        $filename = $_FILES['csv_file']['name'];
		
        $delimiter = $form->getValue('column_delimiter');
		$db_name = $form->getValue('db_name');
		$db_user = $form->getValue('db_user');
		$db_pw = $form->getValue('db_pw');
		$db_host = $form->getValue('db_host');

		$db = new Zend_Db_Adapter_Pdo_Mysql(array(
		    'host'     => $db_host,
		    'username' => $db_user,
		    'password' => $db_pw,
		    'dbname'   => $db_name
		));

		
		
#		$table = new DatabaseTransport_Table($db_host, $db_user, $db_pw, $db_name);
		
		#echo $db->getConnection()->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
		
		if (!$db) {
			return $this->flashError('Your file is incorrectly formatted. '
            . $db->getErrorString());
        }
		
		$table = new DatabaseTransfer_Table($db);
		
		
#        $file = new CsvImport_File($filePath, $delimiter);
#        if (!$file->parse()) {
#            return $this->flashError('Your file is incorrectly formatted. '
#                . $file->getErrorString());
#        }
#        $this->session->originalFilename = $filename;
        $this->session->originalDbname = $filename; //replaced
#        $this->session->filePath = $filePath;
        $this->session->columnDelimiter = $delimiter;

        $this->session->itemTypeId = $form->getValue('item_type_id');
        $this->session->itemsArePublic = $form->getValue('items_are_public');
        $this->session->itemsAreFeatured =  $form->getValue('items_are_featured');
        $this->session->collectionId = $form->getValue('collection_id');

		$this->session->columnNames = $table->getColumnNames();
		$this->session->columnExamples = $table->getColumnExamples();
		
#        $this->session->columnNames = $file->getColumnNames();
#        $this->session->columnExamples = $file->getColumnExamples();
#        $this->session->ownerId = $this->getInvokeArg('bootstrap')->currentuser->id;


		if($form->getValue('choosetable')) {
            $this->_helper->redirector->goto('choose-table');
        }
		else{
        	$this->_helper->redirector->goto('map-columns');
		}
    }
    

    public function mapColumnsAction()
    {
#        if (!$this->_sessionIsValid()) {
#            return $this->_helper->redirector->goto('index');
#        }

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
