<?php

defined('DATABASE_TRANSFER_DIRECTORY') or define('DATABASE_TRANSFER_DIRECTORY', dirname(__FILE__));

add_plugin_hook('install', 'DatabaseTransferPlugin::install');
add_plugin_hook('uninstall', 'DatabaseTransferPlugin::uninstall');

add_plugin_hook('define_acl', 'DatabaseTransferPlugin::defineAcl');

add_filter('admin_navigation_main', 'DatabaseTransferPlugin::adminNavigationMain');

class DatabaseTransferPlugin{
	
	public static function install()
    {
        $db = get_db();
	   // create imported items table for keeping track of imports
	   $db->exec("CREATE TABLE IF NOT EXISTS `{$db->prefix}database_transfer_imported_items` (
	      `id` int(10) unsigned NOT NULL auto_increment,
	      `item_id` int(10) unsigned NOT NULL,
	      PRIMARY KEY  (`id`),
	      UNIQUE (`item_id`)
	      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
   	}
    
    public static function uninstall()
    {
		    // drop the tables
		    $db = get_db();
		    $sql = "DROP TABLE IF EXISTS `{$db->prefix}database_transfer_imported_items`";
		    $db->query($sql);
    }


	
    public static function initialize()
	{
		
	}

	public static function defineAcl($acl)
    {
	    // only allow super users and admins to import csv files
        $acl->loadResourceList(array('DatabaseTransfer_Index' => array(
            'index',
			'map-columns', 
#            'undo-import', 
#            'clear-history', 
            'browse'
        )));
    	$acl->deny(null, 'DatabaseTransfer_Index', array('show', 'add', 'edit', 'delete'));
    	$acl->deny('admin', 'DatabaseTransfer_Index');

    }

	public static function adminNavigationMain($nav)
    {
		/*Adds a link to the admin navigation tab*/
        $nav['Database Transfer'] = uri('database-transfer');
        return $nav;
    }
	
	/** Stolen by Iwe
	 * @return array
	 */
	function csv_import_get_elements_by_element_set_name($itemTypeId)
	{
	    $params = $itemTypeId ? array('item_type_id' => $itemTypeId)
	                          : array('exclude_item_type' => true);
	    return get_db()->getTable('Element')->findPairsForSelectForm($params);
	}
}

?>