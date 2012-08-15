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
		$sql = "CREATE TABLE IF NOT EXISTS `{$db->prefix}database_transfer_imported_items` (
			`id` int(10) unsigned NOT NULL auto_increment,
			`item_id` int(10) unsigned NOT NULL,
			`import_id` int(10) unsigned NOT NULL,       
			PRIMARY KEY  (`id`),
			KEY (`import_id`),
			UNIQUE (`item_id`)
	      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		
	    $db->query($sql);
		$sql2 = "CREATE TABLE IF NOT EXISTS `{$db->prefix}database_transfer_imports` (
	       `id` int(10) unsigned NOT NULL auto_increment,
	       `item_type_id` int(10) unsigned NOT NULL,
	       `collection_id` int(10) unsigned NOT NULL,       
	       `owner_id` int unsigned NOT NULL,
	       `delimiter` varchar(1) collate utf8_unicode_ci NOT NULL,
	       `original_dbname` text collate utf8_unicode_ci NOT NULL,
			`db_name` text collate utf8_unicode_ci NOT NULL,
			`db_user` text collate utf8_unicode_ci NOT NULL,
			`db_pw` text collate utf8_unicode_ci NOT NULL,
			`db_host` text collate utf8_unicode_ci NOT NULL,
			`db_table` text collate utf8_unicode_ci NOT NULL,
#	       `file_path` text collate utf8_unicode_ci NOT NULL,
	       `table_position` bigint unsigned NOT NULL,
	       `status` varchar(255) collate utf8_unicode_ci,
	       `skipped_row_count` int(10) unsigned NOT NULL,
	       `skipped_item_count` int(10) unsigned NOT NULL,
	       `is_public` tinyint(1) default '0',
	       `is_featured` tinyint(1) default '0',
	       `serialized_column_maps` text collate utf8_unicode_ci NOT NULL,
	       `added` timestamp NOT NULL default '0000-00-00 00:00:00',
	       PRIMARY KEY  (`id`)
	       ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$db->query($sql2);
   	}
    
    public static function uninstall()
    {
		    // drop the tables
		    $db = get_db();
		    $sql = "DROP TABLE IF EXISTS `{$db->prefix}database_transfer_imported_items`";
		    $db->query($sql);
		    $sql = "DROP TABLE IF EXISTS `{$db->prefix}database_transfer_imports`";
		    $db->query($sql);
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

}

?>