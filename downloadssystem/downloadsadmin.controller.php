<?php

/*
Download System
Version 3.0
by:vbgamer45
http://www.elkartemods.com
Copyright 2017 elkartemods.com

############################################
License Information:

Links to http://www.elkartemods.com must remain unless
branding free option is purchased.
#############################################
*/

if (!defined('ELK'))
	die('No access...');

class DownloadsAdmin_Controller extends Action_Controller
{

	public function action_index()
    {

	global $boardurl, $modSettings, $currentVersion, $context;

	$currentVersion = '3.0.4';

	require_once(SUBSDIR . '/downloads.subs.php');

	if (empty($modSettings['down_url']))
		$modSettings['down_url'] = $boardurl . '/downloads/';

	if (empty($modSettings['down_path']))
		$modSettings['down_path'] = BOARDDIR . '/downloads/';

	// Load the language files
	if (loadlanguage('Downloads') == false)
		loadLanguage('Downloads','english');


	// Load the main template file
    loadtemplate('Downloads');


	// Download Actions pretty big array heh
	$subActions = array(
		'view' => 'Downloads_ViewDownload',
		'bulkactions' => 'Downloads_BulkActions',
		'adminset'=> 'Downloads_AdminSettings',
		'adminset2'=> 'Downloads_AdminSettings2',
		'report' => 'Downloads_ReportDownload',
		'report2' => 'Downloads_ReportDownload2',
		'deletereport' => 'Downloads_DeleteReport',
		'reportlist' => 'Downloads_ReportList',
		'comment' => 'Downloads_AddComment',
		'comment2' => 'Downloads_AddComment2',
		'editcomment' => 'Downloads_EditComment',
		'editcomment2' => 'Downloads_EditComment2',
		'apprcomment' => 'Downloads_ApproveComment',
		'apprcomall' => 'Downloads_ApproveAllComments',
		'reportcomment' => 'Downloads_ReportComment',
		'reportcomment2' => 'Downloads_ReportComment2',
		'delcomment' => 'Downloads_DeleteComment',
		'delcomreport' => 'Downloads_DeleteCommentReport',
		'commentlist' => 'Downloads_CommentList',
		'viewrating' => 'Downloads_ViewRating',
		'delrating' => 'Downloads_DeleteRating',
		'catup' => 'Downloads_CatUp',
		'catdown' => 'Downloads_CatDown',
		'catperm' => 'Downloads_CatPerm',
		'catperm2' => 'Downloads_CatPerm2',
		'catpermlist' => 'Downloads_CatPermList',
		'catpermdelete' => 'Downloads_CatPermDelete',
		'catimgdel' => 'Downloads_CatImageDelete',
		'addcat' => 'Downloads_AddCategory',
		'addcat2' => 'Downloads_AddCategory2',
		'editcat' => 'Downloads_EditCategory',
		'editcat2' => 'Downloads_EditCategory2',
		'deletecat' => 'Downloads_DeleteCategory',
		'deletecat2' => 'Downloads_DeleteCategory2',
		'viewc' => 'Downloads_ViewC',
		'approvelist' => 'Downloads_ApproveList',
		'approve' => 'Downloads_ApproveDownload',
		'unapprove' => 'Downloads_UnApproveDownload',
		'filespace' => 'Downloads_FileSpaceAdmin',
		'filelist' => 'Downloads_FileSpaceList',
		'recountquota' => 'Downloads_RecountFileQuotaTotals',
		'addquota' => 'Downloads_AddQuota',
		'deletequota' => 'Downloads_DeleteQuota',
		'cusup' => 'Downloads_CustomUp',
		'cusdown' => 'Downloads_CustomDown',
		'cusadd' => 'Downloads_CustomAdd',
		'cusdelete' => 'Downloads_CustomDelete',


	);
	// Follow the sa or just go to main function
    if (isset($_GET['sa']))
        $sa = $_GET['sa'];
    else
        $sa = '';

	if (!empty($subActions[$sa]))
	{
		$saName = (string) $subActions[$sa];


		return $this->$saName();
	}
	else
		return $this->Downloads_AdminSettings();


	}
	


function Downloads_ApproveList()
{
	global $context, $mbname, $txt, $scripturl;

	isAllowedTo('downloads_manage');

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_approvedownloads'];



	$context['sub_template']  = 'approvelist';

	DoDownloadsAdminTabs();

	$context['start'] = (int) $_REQUEST['start'];

	$db = database();

	// Get Total Pages
		$dbresult = $db->query('', "
		SELECT
			COUNT(*) AS total
		FROM {db_prefix}down_file as p
		WHERE p.approved = 0 ORDER BY ID_FILE DESC");
		$row = $db->fetch_assoc($dbresult);
		$total = $row['total'];
		$db->free_result($dbresult);
	$context['downloads_total'] = $total;

	// List all the unapproved downloads
	$dbresult = $db->query('', "
	SELECT
		p.ID_FILE, p.ID_CAT, p.title, p.id_member, m.real_name, p.date, p.description, c.title catname
	FROM {db_prefix}down_file AS p
		LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
		LEFT JOIN {db_prefix}down_cat AS c ON (c.ID_CAT = p.ID_CAT)
	WHERE p.approved = 0
	ORDER BY p.ID_FILE DESC LIMIT $context[start],10");
	$context['downloads_file'] = array();
	 while($row = $db->fetch_assoc($dbresult)) {
         $context['downloads_file'][] = $row;
     }
	$db->free_result($dbresult);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=downloads;sa=approvelist', $_REQUEST['start'], $total, 10);


}




function Downloads_ApproveDownload()
{
	global $txt;
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	// Approve the download
	Downloads_ApproveFileByID($id);

	// Redirect to approval list
	redirectexit('action=admin;area=downloads;sa=approvelist');

}

function Downloads_UnApproveDownload()
{
	global $txt;
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	Downloads_UnApproveFileByID($id);

	// Redirect to approval list
	redirectexit('action=admin;area=downloads;sa=approvelist');
}



function Downloads_ApproveComment()
{
	global $txt;
	isAllowedTo('downloads_manage');


	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);

	$db = database();

	// Approve the comment
	$db->query('', "UPDATE {db_prefix}down_comment
		SET approved = 1 WHERE ID_COMMENT = $id LIMIT 1");

	// Redirect the comment list
	redirectexit('action=admin;area=downloads;sa=commentlist');
}

function Downloads_CommentList()
{
	global $context, $txt, $scripturl;

	isAllowedTo('downloads_manage');

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_approvecomments'];
	$context['sub_template']  = 'comment_list';


	$context['start'] = (int) $_REQUEST['start'];
    $db = database();

		// Get Total Pages
		$dbresult = $db->query('', "
		SELECT
			COUNT(*) AS total
		FROM {db_prefix}down_comment
		WHERE approved = 0 ORDER BY ID_COMMENT DESC");
		$row = $db->fetch_assoc($dbresult);
		$total = $row['total'];
		$db->free_result($dbresult);
		$context['downloads_total'] = $total;

		$dbresult = $db->query('', "
		SELECT
			c.ID_COMMENT, c.ID_FILE, c.comment, c.date, c.id_member, m.real_name
		FROM {db_prefix}down_comment as c
			LEFT JOIN {db_prefix}members AS m ON (c.id_member = m.id_member)
		WHERE c.approved = 0 ORDER BY c.ID_COMMENT DESC LIMIT $context[start],10");
		$context['downloads_comments'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_comments'][] = $row;
		}

		$db->free_result($dbresult);

		$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=downloads;sa=commentlist', $_REQUEST['start'], $total, 10);


	// Reported Comments
	$dbresult = $db->query('', "
	SELECT
		c.ID, c.ID_FILE, c.ID_COMMENT,  c.id_member, m.real_name, c.date,c.comment,
		d.comment OringalComment
	FROM ({db_prefix}down_creport as c, {db_prefix}down_comment AS d)
	LEFT JOIN {db_prefix}members AS m on  (c.id_member = m.id_member)
	WHERE  c.ID_COMMENT = d.ID_COMMENT
	ORDER BY c.ID_FILE DESC");
	$context['downloads_reports'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_reports'][] = array(
			'ID' => $row['ID'],
			'ID_FILE' => $row['ID_FILE'],
			'ID_COMMENT' => $row['ID_COMMENT'],
			'id_member' => $row['id_member'],
			'real_name' => $row['real_name'],
			'date' => $row['date'],
			'comment' => $row['comment'],
			'OringalComment' => $row['OringalComment'],

			);
		}

	$db->free_result($dbresult);


	DoDownloadsAdminTabs();

}

function Downloads_AdminSettings()
{
	global $context,$txt;
	isAllowedTo('downloads_manage');

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_settings'];

	DoDownloadsAdminTabs();

	$context['sub_template']  = 'settings';

}

function Downloads_AdminSettings2()
{


	isAllowedTo('downloads_manage');

	// Get the settings
	$down_max_filesize =  (int) $_REQUEST['down_max_filesize'];
	$down_set_files_per_page = (int) $_REQUEST['down_set_files_per_page'];
	$down_commentchoice =  isset($_REQUEST['down_commentchoice']) ? 1 : 0;
	$down_path = $_REQUEST['down_path'];
	$down_url = $_REQUEST['down_url'];
	$down_who_viewing = isset($_REQUEST['down_who_viewing']) ? 1 : 0;

	$down_set_commentsnewest = isset($_REQUEST['down_set_commentsnewest']) ? 1 : 0;
	$down_set_enable_multifolder = isset($_REQUEST['down_set_enable_multifolder']) ? 1 : 0;
	$down_show_ratings =  isset($_REQUEST['down_show_ratings']) ? 1 : 0;
	$down_index_toprated =  isset($_REQUEST['down_index_toprated']) ? 1 : 0;
	$down_index_recent =   isset($_REQUEST['down_index_recent']) ? 1 : 0;
	$down_index_mostviewed =  isset($_REQUEST['down_index_mostviewed']) ? 1 : 0;
	$down_index_mostcomments = isset($_REQUEST['down_index_mostcomments']) ? 1 : 0;
	$downloads_index_mostdownloaded = isset($_REQUEST['downloads_index_mostdownloaded']) ? 1 : 0;
	$down_index_showtop = isset($_REQUEST['down_index_showtop']) ? 1 : 0;
	$down_set_show_quickreply = isset($_REQUEST['down_set_show_quickreply']) ? 1 : 0;
	$down_set_cat_width = (int) $_REQUEST['down_set_cat_width'];
	$down_set_cat_height = (int) $_REQUEST['down_set_cat_height'];
	// Category view category settings
	$down_set_t_downloads = isset($_REQUEST['down_set_t_downloads']) ? 1 : 0;
	$down_set_t_views = isset($_REQUEST['down_set_t_views']) ? 1 : 0;
	$down_set_t_filesize = isset($_REQUEST['down_set_t_filesize']) ? 1 : 0;
	$down_set_t_date = isset($_REQUEST['down_set_t_date']) ? 1 : 0;
	$down_set_t_comment = isset($_REQUEST['down_set_t_comment']) ? 1 : 0;
	$down_set_t_username = isset($_REQUEST['down_set_t_username']) ? 1 : 0;
	$down_set_t_rating = isset($_REQUEST['down_set_t_rating']) ? 1 : 0;
	$down_set_t_title = isset($_REQUEST['down_set_t_title']) ? 1 : 0;
	$down_set_count_child = isset($_REQUEST['down_set_count_child']) ? 1 : 0;

	// Download display settings
	$down_set_file_prevnext = isset($_REQUEST['down_set_file_prevnext']) ? 1 : 0;
	$down_set_file_desc = isset($_REQUEST['down_set_file_desc']) ? 1 : 0;
	$down_set_file_title = isset($_REQUEST['down_set_file_title']) ? 1 : 0;
	$down_set_file_views = isset($_REQUEST['down_set_file_views']) ? 1 : 0;
	$down_set_file_downloads = isset($_REQUEST['down_set_file_downloads']) ? 1 : 0;
	$down_set_file_lastdownload = isset($_REQUEST['down_set_file_lastdownload']) ? 1 : 0;
	$down_set_file_poster = isset($_REQUEST['down_set_file_poster']) ? 1 : 0;
	$down_set_file_date = isset($_REQUEST['down_set_file_date']) ? 1 : 0;
	$down_set_file_showfilesize = isset($_REQUEST['down_set_file_showfilesize']) ? 1 : 0;
	$down_set_file_showrating = isset($_REQUEST['down_set_file_showrating']) ? 1 : 0;
	$down_set_file_keywords = isset($_REQUEST['down_set_file_keywords']) ? 1 : 0;



	// Download Linking codes
	$down_set_showcode_directlink = isset($_REQUEST['down_set_showcode_directlink']) ? 1 : 0;
	$down_set_showcode_htmllink = isset($_REQUEST['down_set_showcode_htmllink']) ? 1 : 0;

	if (empty($down_set_cat_height))
		$down_set_cat_height = 120;

	if (empty($down_set_cat_width))
		$down_set_cat_width = 120;


	// Save the setting information
	updateSettings(
	array(
	'down_max_filesize' => $down_max_filesize,
	'down_path' => $down_path,
	'down_url' => $down_url,
	'down_commentchoice' => $down_commentchoice,
	'down_who_viewing' => $down_who_viewing,
	'down_set_count_child' => $down_set_count_child,
	'down_show_ratings' => $down_show_ratings,
	'down_index_toprated' => $down_index_toprated,
	'down_index_recent' => $down_index_recent,
	'down_index_mostviewed' => $down_index_mostviewed,
	'down_index_mostcomments' => $down_index_mostcomments,
	'downloads_index_mostdownloaded' => $downloads_index_mostdownloaded,
	'down_index_showtop' => $down_index_showtop,

	'down_set_files_per_page' => $down_set_files_per_page,
	'down_set_commentsnewest' => $down_set_commentsnewest,
	'down_set_show_quickreply' => $down_set_show_quickreply,
	'down_set_enable_multifolder' => $down_set_enable_multifolder,

	'down_set_cat_height' => $down_set_cat_height,
	'down_set_cat_width' => $down_set_cat_width,
	'down_set_t_downloads' => $down_set_t_downloads,
	'down_set_t_views' => $down_set_t_views,
	'down_set_t_filesize' => $down_set_t_filesize,
	'down_set_t_date' => $down_set_t_date,
	'down_set_t_comment' => $down_set_t_comment,
	'down_set_t_username' => $down_set_t_username,
	'down_set_t_rating' => $down_set_t_rating,
	'down_set_t_title' => $down_set_t_title,
	'down_set_file_prevnext' => $down_set_file_prevnext,
	'down_set_file_desc' => $down_set_file_desc,
	'down_set_file_title' => $down_set_file_title,
	'down_set_file_views' => $down_set_file_views,
	'down_set_file_downloads' => $down_set_file_downloads,
	'down_set_file_lastdownload' => $down_set_file_lastdownload,
	'down_set_file_poster' => $down_set_file_poster,
	'down_set_file_date' => $down_set_file_date,
	'down_set_file_showfilesize' => $down_set_file_showfilesize,
	'down_set_file_showrating' => $down_set_file_showrating,
	'down_set_file_keywords' => $down_set_file_keywords,
	'down_set_showcode_directlink' => $down_set_showcode_directlink,
	'down_set_showcode_htmllink' => $down_set_showcode_htmllink,

	));

	redirectexit('action=admin;area=downloads;sa=adminset');

}

function Downloads_BulkActions()
{
	isAllowedTo('downloads_manage');

	if (isset($_REQUEST['files']))
	{
		$baction = $_REQUEST['doaction'];

		foreach ($_REQUEST['files'] as $value)
		{

			if ($baction == 'approve')
				Downloads_ApproveFileByID($value);
			if ($baction == 'delete')
				Downloads_DeleteFileByID($value);

		}
	}

	// Redirect to approval list
	redirectexit('action=admin;area=downloads;sa=approvelist');
}



function Downloads_CatPermList()
{
	global $mbname, $txt, $context;
	isAllowedTo('downloads_manage');

	$db = database();

	// Load the template
	$context['sub_template']  = 'catpermlist';

	// Set the page title
	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_catpermlist'];

	$dbresult = $db->query('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,
		c.ID_GROUP, m.group_name,a.title catname
	FROM ({db_prefix}down_catperm as c, {db_prefix}membergroups AS m,{db_prefix}down_cat as a)
	WHERE  c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT");
	$context['downloads_membergroups'] = array();
	while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_membergroups'][] = $row;

		}
	$db->free_result($dbresult);

	$dbresult = $db->query('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,  c.ID_GROUP,a.title catname
	FROM {db_prefix}down_catperm as c,{db_prefix}down_cat as a
	WHERE  c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['downloads_regmem'] = array();
	while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_regmem'][] = $row;

		}
	$db->free_result($dbresult);


	$dbresult = $db->query('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,  c.ID_GROUP,a.title catname
	FROM {db_prefix}down_catperm as c,{db_prefix}down_cat as a
	WHERE  c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['downloads_guestmem'] = array();
	while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_guestmem'][] = $row;

		}
	$db->free_result($dbresult);

	DoDownloadsAdminTabs();
}

function Downloads_CatPermDelete()
{
	isAllowedTo('downloads_manage');

	$db = database();

	$id = (int) $_REQUEST['id'];

	// Delete the Permission
	$db->query('', "DELETE FROM {db_prefix}down_catperm WHERE ID = " . $id . ' LIMIT 1');
	// Redirect to the ratings
	redirectexit('action=admin;area=downloads;sa=catpermlist');

}


function Downloads_ReportList()
{
	global $context, $txt;

	isAllowedTo('downloads_manage');

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_reportdownloads'];


	$context['sub_template']  = 'reportlist';

	$db = database();

	$dbresult = $db->query('', "
	SELECT
		r.ID, r.ID_FILE, r.id_member, m.real_name, r.date, r.comment
	FROM {db_prefix}down_report as r
		  LEFT JOIN {db_prefix}members AS m ON  (m.id_member = r.id_member)
	ORDER BY r.ID_FILE DESC");

	$context['downloads_reports'] = array();
	while ($row = $db->fetch_assoc($dbresult))
	{
			$context['downloads_reports'][] = $row;
	}
	$db->free_result($dbresult);

	DoDownloadsAdminTabs();

}

function Downloads_DeleteReport()
{
	global $txt;
	// Check the permission
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_report_selected']);

	$db = database();

	$db->query('', "DELETE FROM {db_prefix}down_report WHERE ID = $id LIMIT 1");

	// Redirect to redirect list
	redirectexit('action=admin;area=downloads;sa=reportlist');
}

function Downloads_DeleteCommentReport()
{
	global $txt;
	// Check the permission
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_report_selected']);

	$db = database();

	$db->query('', "DELETE FROM {db_prefix}down_creport WHERE ID = $id LIMIT 1");

	// Redirect to redirect list
	redirectexit('action=admin;area=downloads;sa=commentlist');
}


function Downloads_FileSpaceAdmin()
{
	global $mbname, $txt, $context, $scripturl;
	// Check if they are allowed to manage the downloads
	isAllowedTo('downloads_manage');

	loadLanguage('Admin');

	$db = database();

	// Set the page tile
	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_filespace'];
	// Load the subtemplate for the file manager
	$context['sub_template']  = 'filespace';

	// Load the membergroups
	$dbresult = $db->query('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 ORDER BY group_name");
	while ($row = $db->fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$db->free_result($dbresult);

	$db = database();

	$dbresult = $db->query('', "
	SELECT
		q.totalfilesize,  q.ID_GROUP, m.group_name
	FROM {db_prefix}down_groupquota as q, {db_prefix}membergroups AS m
	WHERE  q.ID_GROUP = m.ID_GROUP ORDER BY q.totalfilesize");
	$context['downloads_membergroups'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_membergroups'][] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'totalfilesize' => $row['totalfilesize'],
			'group_name' => $row['group_name'],


			);

		}
	$db->free_result($dbresult);

	$dbresult = $db->query('', "
	SELECT
		q.totalfilesize, q.ID_GROUP
	FROM {db_prefix}down_groupquota as q
	WHERE  q.ID_GROUP = 0 LIMIT 1");
	$context['downloads_reggroup'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_reggroup'][] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'totalfilesize' => $row['totalfilesize'],
			);

		}
	$db->free_result($dbresult);


	$context['start'] = (int) $_REQUEST['start'];

	// Get Total Pages
	$dbresult = $db->query('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}down_userquota as q");
	$row = $db->fetch_assoc($dbresult);
	$total = $row['total'];
	$db->free_result($dbresult);
	$context['downloads_total'] = $total;

	$dbresult = $db->query('', "
	SELECT
		q.totalfilesize,  q.id_member, m.real_name
	FROM {db_prefix}down_userquota as q, {db_prefix}members AS m
	WHERE  q.id_member = m.id_member
	ORDER BY q.totalfilesize DESC  LIMIT $context[start],20");
	$context['downloads_members'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_members'][] = $row;

		}
	$db->free_result($dbresult);

	DoDownloadsAdminTabs();

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=downloads;sa=filespace', $_REQUEST['start'], $total, 20);


}

function Downloads_FileSpaceList()
{
	global $txt, $context, $scripturl;
	// Check if they are allowed to manage the downloads
	isAllowedTo('downloads_manage');


	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_user_selected']);

	$db = database();

    $dbresult = $db->query('', "
    SELECT
    	m.real_name
    FROM {db_prefix}members AS m
    WHERE m.id_member = $id  LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$context['downloads_filelist_real_name'] = $row['real_name'];
	$context['downloads_filelist_userid'] = $id;
	$db->free_result($dbresult);

	// Set the page tile
	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_filespace'] . ' - ' . $context['downloads_filelist_real_name'];
	// Load the subtemplate for the file manager
	$context['sub_template']  = 'filelist';

	$context['start'] = (int) $_REQUEST['start'];

	// Get Total Pages
	$dbresult = $db->query('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}down_file
	WHERE id_member = " . $context['downloads_filelist_userid']);
	$row = $db->fetch_assoc($dbresult);
	$total = $row['total'];
	$db->free_result($dbresult);
	$context['downloads_total'] = $total;



	$dbresult = $db->query('', "
	SELECT
		p.ID_FILE,p.title, p.filesize,p.id_member
	FROM {db_prefix}down_file as p
	WHERE p.id_member = " . $context['downloads_filelist_userid'] . "
	ORDER BY p.filesize DESC  LIMIT $context[start],20");
	$context['downloads_files'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_files'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'filesize' => $row['filesize'],
			'id_member' => $row['id_member'],


			);

		}
	$db->free_result($dbresult);

	DoDownloadsAdminTabs('filespace');

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=downloads;sa=filelist&id=' . $context['downloads_filelist_userid'], $_REQUEST['start'], $total, 20);

}

function Downloads_RecountFileQuotaTotals($redirect = true)
{

	if ($redirect == true)
		isAllowedTo('downloads_manage');

	$db = database();

	// Show all the user's with quota information
	$dbresult = $db->query('', "
	SELECT
		id_member
	FROM {db_prefix}down_userquota");
	while($row = $db->fetch_assoc($dbresult))
	{
		// Loop though the all the files for the member and get the total
		$dbresult2 = $db->query('', "
		SELECT
			SUM(filesize) as total
		FROM {db_prefix}down_file
		WHERE id_member = " . $row['id_member']);

		$row2 = $db->fetch_assoc($dbresult2);
		$total = $row2['total'];

		if ($total == '')
			$total = 0;

		$db->free_result($dbresult2);
		// Update the quota
		$db->query('', "UPDATE {db_prefix}down_userquota SET totalfilesize = $total WHERE id_member = " . $row['id_member'] . " LIMIT 1");

	}
	$db->free_result($dbresult);

	if ($redirect == true)
		redirectexit('action=admin;area=downloads;sa=filespace');
}


function Downloads_AddQuota()
{
	global $txt;

	isAllowedTo('downloads_manage');

	$groupid = (int) $_REQUEST['groupname'];

	$filelimit = (double) $_REQUEST['filelimit'];
	if (empty($filelimit))
	{
		fatal_error($txt['downloads_error_noquota'],false);
	}

	$db = database();

	$dbresult = $db->query('', "
	SELECT
		ID_GROUP
	FROM {db_prefix}down_groupquota
	WHERE ID_GROUP = $groupid LIMIT 1");
	$count = $db->affected_rows();
	$db->free_result($dbresult);

	if ($count == 0)
	{
		// Create the record
		$db->query('', "INSERT INTO {db_prefix}down_groupquota (ID_GROUP, totalfilesize) VALUES ($groupid, $filelimit)");
	}
	else
	{
		fatal_error($txt['downloads_error_quotaexist'],false);
	}

	redirectexit('action=admin;area=downloads;sa=filespace');
}

function Downloads_DeleteQuota()
{

	isAllowedTo('downloads_manage');
	$id = (int) $_REQUEST['id'];

	$db = database();

	$db->query('', "DELETE FROM {db_prefix}down_groupquota WHERE ID_GROUP = " . $id . ' LIMIT 1');

	redirectexit('action=admin;area=downloads;sa=filespace');
}


function Downloads_ApproveAllComments()
{
	isAllowedTo('downloads_manage');

	$db = database();

	// Approve all the comments
	$db->query('', "UPDATE {db_prefix}down_comment
		SET approved = 1 WHERE approved = 0");

	// Reditrect the comment list
	redirectexit('action=admin;area=downloads;sa=commentlist');
}


}