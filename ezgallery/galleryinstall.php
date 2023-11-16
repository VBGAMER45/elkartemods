<?php
/*
ezGallery Lite Edition
Version 5.6
by:vbgamer45
http://www.elkartemods.com


############################################
License Information:
Links to http://www.elkartemods.com must remain unless
branding free option is purchased.
#############################################
*/
//Install the Database tables

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no ELK?
elseif (!defined('ELK'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as Elkarte\'s index.php.');

$db = database();

//Picture Table
$db->query('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_pic(
id_picture int(11) NOT NULL auto_increment,
id_member mediumint(8) unsigned NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
title varchar(100) NOT NULL,
description text,
 views int(10) NOT NULL default '0',
 filesize int(10) NOT NULL default '0',
 height int(10) NOT NULL default '0',
 width int(10) NOT NULL default '0',
 filename tinytext,
 thumbfilename tinytext,
 commenttotal int(10) NOT NULL default '0',
 id_cat int(10) NOT NULL default '0',
 approved tinyint(4) NOT NULL default '0',
 allowcomments tinyint(4) NOT NULL default '0',
 keywords varchar(100),
 totalratings int(10) NOT NULL default '0',
rating int(10) NOT NULL default '0',
type tinyint(4) NOT NULL default '0',
user_id_cat int(10) NOT NULL default '0',
mediumfilename tinytext,
videofile tinytext,
PRIMARY KEY  (id_picture))
ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci

");



//Picture comments
$db->query('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_comment(
id_comment int(11) NOT NULL auto_increment,
id_picture int(11) NOT NULL,
id_member mediumint(8) unsigned NOT NULL default '0',
approved tinyint(4) NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id_comment))
ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
");

//Gallery Category
$db->query('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_cat
(id_cat mediumint(8) NOT NULL auto_increment,
title varchar(100) NOT NULL,
description text,
roworder mediumint(8) unsigned NOT NULL default '0',
image varchar(255) NOT NULL,
id_parent smallint(5) unsigned NOT NULL default '0',
redirect tinyint(4) NOT NULL default '0',
PRIMARY KEY  (id_cat))
ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
");



		


//Gallery Reported Images
$db->query('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_report
(id int(11) NOT NULL auto_increment,
id_picture int(11) NOT NULL,
id_member mediumint(8) unsigned NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id))
ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
");

// Insert the settings
$db->query('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('gallery_max_height', '2500'),
('gallery_max_width', '2500'),
('gallery_max_filesize', '5000000'),
('gallery_who_viewing', '0'),
('gallery_commentchoice', '0'),
('gallery_set_images_per_page', '20'),
('gallery_set_images_per_row','4'),
('gallery_thumb_width', '120'),
('gallery_thumb_height', '78'),
('gallery_shop_commentadd', '0'),
('gallery_shop_picadd', '0'),
('gallery_set_showcode_bbc_image', '0'),
('gallery_set_showcode_directlink', '0'),
('gallery_set_showcode_htmllink', '0'),
('gallery_copyrightkey', ''),
('gallery_make_medium', '1'),
('gallery_medium_width', '600'),
('gallery_medium_height', '600'),
('gallery_avea_imported','0')
");

// Indexes
$dbresult = $db->query('', "SHOW INDEX FROM  {db_prefix}gallery_pic");

$indexUSER_ID_CAT = 1;
$indexID_CAT = 1;
$indexID_MEMBER = 1;
$indexRating = 1;
$indexViews = 1;
$indexcommenttotal = 1;
$indextotalratings = 1;

while ($row = $db->fetch_assoc($dbresult))
{
	if ($row['Column_name'] == 'ID_CAT')
		$indexID_CAT = 0;
	if ($row['Column_name'] == 'USER_ID_CAT')
		$indexUSER_ID_CAT = 0;	
	if ($row['Column_name'] == 'ID_MEMBER')
		$indexID_MEMBER = 0;	
	if ($row['Column_name'] == 'rating')
		$indexRating = 0;	
	if ($row['Column_name'] == 'views')
		$indexViews  = 0;	
	if ($row['Column_name'] == 'commenttotal')
		$indexcommenttotal = 0;	
	if ($row['Column_name'] == 'totalratings')
		$indextotalratings = 0;	
		
		
}

if ($indexID_CAT)
	$db->query('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (ID_CAT)");

if ($indexUSER_ID_CAT)
	$db->query('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (USER_ID_CAT)");

if ($indexID_MEMBER)
	$db->query('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (ID_MEMBER)");
	
if ($indexRating)
	$db->query('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (rating)");

if ($indexViews)
	$db->query('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (views)");

if ($indexcommenttotal)
	$db->query('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (commenttotal)");

if ($indextotalratings)
	$db->query('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (totalratings)");

	




// Permissions array
$permissions = array(
	'ezgallery_view' => array(-1, 0, 2), // ALL
);

addPermissions($permissions);

function addPermissions($permissions)
{
    $db = database();

	$perm = array();

	foreach ($permissions as $permission => $default)
	{
		$result = $db->query('', '
			SELECT COUNT(*)
			FROM {db_prefix}permissions
			WHERE permission = {string:permission}',
			array(
				'permission' => $permission
			)
		);

		list ($num) = $db->fetch_row($result);

		if ($num == 0)
		{
			foreach ($default as $grp)
				$perm[] = array($grp, $permission);
		}
	}

	if (empty($perm))
		return;

	$db->insert('insert',
		'{db_prefix}permissions',
		array(
			'id_group' => 'int',
			'permission' => 'string'
		),
		$perm,
		array()
	);
}

?>