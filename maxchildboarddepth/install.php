<?php
/*
	Count Child Child Boards Posts
	https://www.smfhacks.com
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no ELK?
elseif (!defined('ELK'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as Elkarte\'s index.php.');




// Create Tables
$db = database();
 
// Set up default settings
$db->query('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('boardindex_max_depth', '2')");



?>