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

ini_set('gd.jpeg_ignore_warning', 1);

class Downloads_Controller extends Action_Controller
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

    TopDownloadTabs();


	// Download Actions pretty big array heh
	$subActions = array(
		'view' => 'Downloads_ViewDownload',
		'bulkactions' => 'Downloads_BulkActions',
		'adminset'=> 'Downloads_AdminSettings',
		'adminset2'=> 'Downloads_AdminSettings2',
		'delete' => 'Downloads_DeleteDownload',
		'delete2' => 'Downloads_DeleteDownload2',
		'edit' => 'Downloads_EditDownload',
		'edit2' => 'Downloads_EditDownload2',
		'report' => 'Downloads_ReportDownload',
		'report2' => 'Downloads_ReportDownload2',
		'comment' => 'Downloads_AddComment',
		'comment2' => 'Downloads_AddComment2',
		'editcomment' => 'Downloads_EditComment',
		'editcomment2' => 'Downloads_EditComment2',
		'apprcomment' => 'Downloads_ApproveComment',
		'apprcomall' => 'Downloads_ApproveAllComments',
		'reportcomment' => 'Downloads_ReportComment',
		'reportcomment2' => 'Downloads_ReportComment2',
		'delcomment' => 'Downloads_DeleteComment',
		'rate' => 'Downloads_RateDownload',
		'viewrating' => 'Downloads_ViewRating',
		'delrating' => 'Downloads_DeleteRating',
		'catup' => 'Downloads_CatUp',
		'catdown' => 'Downloads_CatDown',
		'catperm' => 'Downloads_CatPerm',
		'catperm2' => 'Downloads_CatPerm2',
		'catpermdelete' => 'Downloads_CatPermDelete',
		'catimgdel' => 'Downloads_CatImageDelete',
		'addcat' => 'Downloads_AddCategory',
		'addcat2' => 'Downloads_AddCategory2',
		'editcat' => 'Downloads_EditCategory',
		'editcat2' => 'Downloads_EditCategory2',
		'deletecat' => 'Downloads_DeleteCategory',
		'deletecat2' => 'Downloads_DeleteCategory2',
		'viewc' => 'Downloads_ViewC',
		'myfiles' => 'Downloads_MyFiles',
		'approve' => 'Downloads_ApproveDownload',
		'unapprove' => 'Downloads_UnApproveDownload',
		'add' => 'Downloads_AddDownload',
		'add2' => 'Downloads_AddDownload2',
		'search' => 'Downloads_Search',
		'search2' => 'Downloads_Search2',
		'stats' => 'Downloads_Stats',
		'next' => 'Downloads_NextDownload',
		'prev' => 'Downloads_PreviousDownload',
		'cusup' => 'Downloads_CustomUp',
		'cusdown' => 'Downloads_CustomDown',
		'cusadd' => 'Downloads_CustomAdd',
		'cusdelete' => 'Downloads_CustomDelete',
		'downfile' => 'Downloads_DownloadFile',

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
		return $this->Downloads_MainView();


	}




function Downloads_MainView()
{
	global $context, $scripturl,  $txt, $modSettings, $user_info;



	// View the main Downloads

	// Is the user allowed to view the downloads?
	isAllowedTo('downloads_view');


	$db = database();

	// Load the main downloads template
	$context['sub_template']  = 'mainview';


	// Get the main groupid
	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	if (isset($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];
	else
		$cat = 0;

	if (!empty($cat))
	{

		// Check the permission
		Downloads_GetCatPermission($cat,'view');

		// Get category name used for the page title
		$dbresult1 = $db->query('', "
		SELECT
			ID_CAT, title, roworder, description, image,
			disablerating, orderby, sortby,ID_PARENT
		FROM {db_prefix}down_cat
		WHERE ID_CAT = $cat LIMIT 1");
		$row1 = $db->fetch_assoc($dbresult1);
		$context['downloads_cat_name'] = $row1['title'];
		$context['downloads_sortby'] = $row1['sortby'];
		$context['downloads_orderby'] = $row1['orderby'];
		$context['downloads_cat_norate'] = $row1['disablerating'];
		if ($context['downloads_cat_norate'] == '')
			$context['downloads_cat_norate'] = 0;

		$db->free_result($dbresult1);

		Downloads_GetParentLink($row1['ID_PARENT']);

		// Link Tree
		$context['linktree'][] = array(
					'url' => $scripturl . '?action=downloads;cat=' . $cat,
					'name' => $context['downloads_cat_name']
				);

		// Set the page title
		$context['page_title'] = $context['downloads_cat_name'];

		// Get the total number of pages
		$total = Downloads_GetTotalByCATID($cat);


		$context['start'] = (int) $_REQUEST['start'];

		$context['downloads_total'] = $total;


		// Check if we are sorting stuff heh
		$sortby = '';
		$orderby = '';
		if (isset($_REQUEST['sortby']))
		{
			switch ($_REQUEST['sortby'])
			{
				case 'date':
					$sortby = 'p.ID_FILE';

				break;
				case 'title':
					$sortby = 'p.title';
				break;

				case 'mostview':
					$sortby = 'p.views';
				break;

				case 'mostdowns':
					$sortby = 'p.totaldownloads';
				break;
				case 'filesize':
					$sortby = 'p.filesize';
				break;
				case 'membername':
					$sortby = 'm.real_name';
				break;

				case 'mostcom':
					$sortby = 'p.commenttotal';
				break;

				case 'mostrated':
					$sortby = 'p.totalratings';
				break;


				default:
					$sortby = 'p.ID_FILE';
				break;
			}

			$sortby2 = $_REQUEST['sortby'];

			$context['downloads_sortby'] = $sortby2;
		}
		else
		{
			if (!empty($context['downloads_sortby']))
				$sortby = $context['downloads_sortby'];
			else
				$sortby = 'p.ID_FILE';

			$sortby2 = 'date';

			$context['downloads_sortby'] = $sortby2;
		}


		if (isset($_REQUEST['orderby']))
		{
			switch ($_REQUEST['orderby'])
			{
				case 'asc':
					$orderby = 'ASC';

				break;
				case 'desc':
					$orderby = 'DESC';
				break;



				default:
					$orderby = 'DESC';
				break;
			}

			$orderby2 = $_REQUEST['orderby'];

			$context['downloads_orderby2'] = $orderby2;
		}
		else
		{

			if (!empty($context['downloads_orderby']))
				$orderby = $context['downloads_orderby'];
			else
				$orderby = 'DESC';

			$orderby2 = 'desc';

			$context['downloads_orderby2'] = $orderby2;
		}


		// Show the downloads
		$dbresult = $db->query('', "
		SELECT
			p.ID_FILE, p.totalratings, p.rating, p.commenttotal,
		 	p.filesize, p.views, p.title, p.id_member, m.real_name,
		 	 p.date, p.description, p.totaldownloads
		FROM {db_prefix}down_file as p
			LEFT JOIN {db_prefix}members AS m ON (p.id_member = m.id_member)
		WHERE  p.ID_CAT = $cat AND p.approved = 1
		ORDER BY $sortby $orderby
		LIMIT $context[start]," . $modSettings['down_set_files_per_page']);
		$context['downloads_files'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_files'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'totalratings' => $row['totalratings'],
			'rating' => $row['rating'],
			'commenttotal' => $row['commenttotal'],
			'filesize' => $row['filesize'],
			'views' => $row['views'],
			'id_member' => $row['id_member'],
			'real_name' => $row['real_name'],
			'date' => $row['date'],
			'description' => $row['description'],
			'totaldownloads' => $row['totaldownloads'],

			);

		}
		$db->free_result($dbresult);



		$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;cat=' . $cat . ';sortby=' . $context['downloads_sortby'] . ';orderby=' . $context['downloads_orderby2'], $_REQUEST['start'], $total, $modSettings['down_set_files_per_page']);




		if (!empty($modSettings['down_who_viewing']))
		{
			$context['can_moderate_forum'] = allowedTo('moderate_forum');

				// Start out with no one at all viewing it.
				$context['view_members'] = array();
				$context['view_members_list'] = array();
				$context['view_num_hidden'] = 0;
				$whoID = (string) $cat;

				// Search for members who have this downloads id set in their GET data.
				$request = $db->query('', "
					SELECT
						lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
						mg.online_color, mg.ID_GROUP, mg.group_name
					FROM {db_prefix}log_online AS lo
						LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lo.id_member)
						LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
					WHERE INSTR(lo.url, 's:9:\"downloads\";s:3:\"cat\";s:" . strlen($whoID ) .":\"$cat\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'");
				while ($row = $db->fetch_assoc($request))
				{
					if (empty($row['id_member']))
						continue;

					if (!empty($row['online_color']))
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</a>';
					else
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';

					$is_buddy = in_array($row['id_member'], $user_info['buddies']);
					if ($is_buddy)
						$link = '<b>' . $link . '</b>';

					// Add them both to the list and to the more detailed list.
					if (!empty($row['show_online']) || allowedTo('moderate_forum'))
						$context['view_members_list'][$row['log_time'] . $row['member_name']] = empty($row['show_online']) ? '<i>' . $link . '</i>' : $link;
					$context['view_members'][$row['log_time'] . $row['member_name']] = array(
						'id' => $row['id_member'],
						'username' => $row['member_name'],
						'name' => $row['real_name'],
						'group' => $row['ID_GROUP'],
						'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
						'link' => $link,
						'is_buddy' => $is_buddy,
						'hidden' => empty($row['show_online']),
					);

					if (empty($row['show_online']))
						$context['view_num_hidden']++;
				}

				// The number of guests is equal to the rows minus the ones we actually used ;).
				$context['view_num_guests'] = $db->num_rows($request) - count($context['view_members']);
				$db->free_result($request);

				// Sort the list.
				krsort($context['view_members']);
				krsort($context['view_members_list']);

		}


	}
	else
	{
		$context['page_title'] = $txt['downloads_text_title'];

	$dbresult = $db->query('', "
	SELECT
		c.ID_CAT, c.title, p.view, c.roworder, c.description, c.image, c.filename, c.redirect
	FROM {db_prefix}down_cat AS c
	LEFT JOIN {db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
	WHERE c.ID_PARENT = 0 ORDER BY c.roworder ASC");
	$context['downloads_cats'] = array();
	while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_cats'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'view' => $row['view'],
			'roworder' => $row['roworder'],
			'description' => $row['description'],
			'filename' => $row['filename'],
			'redirect' => $row['redirect'],
			'image' => $row['image'],
			);

		}
		$db->free_result($dbresult);


		// Downloads waiting for approval
		$dbresult3 = $db->query('', "
		SELECT
			COUNT(*) as totalfiles
		FROM {db_prefix}down_file
		WHERE approved = 0");
		$row2 = $db->fetch_assoc($dbresult3);
		$totalfiles = $row2['totalfiles'];
		$db->free_result($dbresult3);
		$context['downloads_waitapproval'] = $totalfiles;
		// Reported Downloads
		$dbresult4 = $db->query('', "
		SELECT
			COUNT(*) as totalreport
		FROM {db_prefix}down_report");
		$row2 = $db->fetch_assoc($dbresult4);
		$totalreport = $row2['totalreport'];
		$db->free_result($dbresult4);
		$context['downloads_totalreport'] = $totalreport;

		// Total Comments Rating for Approval
		$dbresult5 = $db->query('', "
		SELECT
			COUNT(*) as totalcom
		FROM {db_prefix}down_comment
		WHERE approved = 0");
		$row2 = $db->fetch_assoc($dbresult5);
		$totalcomments = $row2['totalcom'];
		$db->free_result($dbresult5);
		$context['downloads_totalcom'] = $totalcomments;

		// Total reported Comments
		$dbresult6 = $db->query('', "
		SELECT
			COUNT(*) as totalcreport
		FROM {db_prefix}down_creport");
		$row2 = $db->fetch_assoc($dbresult6);
		$totalcomments = $row2['totalcreport'];
		$db->free_result($dbresult6);
		$context['downloads_totalcreport'] = $totalcomments;

	}


}



function Downloads_ViewDownload()
{
	global $context, $modSettings, $user_info, $scripturl, $txt;

	isAllowedTo('downloads_view');

	$db = database();


	if (isset($_REQUEST['down']))
		$id = (int) $_REQUEST['down'];

	if (isset($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];
	// Get the file id
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected'], false);

	$dbresult = $db->query('', "
	SELECT
		ID_FILE, ID_CAT
	FROM {db_prefix}down_file
	WHERE ID_FILE = $id  LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$db->free_result($dbresult);

	// Get the download information
	$dbresult = $db->query('', "
	SELECT
		p.ID_FILE, p.totalratings, p.rating, p.allowcomments, p.ID_CAT, p.keywords,
		p.commenttotal, p.filesize, p.filename, p.orginalfilename, p.fileurl,
	 	p.approved, p.views, p.title, p.id_member, m.real_name, p.date, p.description,
	   	c.title CAT_TITLE, c.ID_PARENT, c.disablerating, p.credits, p.totaldownloads,  p.lastdownload
	FROM ({db_prefix}down_file as p,  {db_prefix}down_cat AS c)
		LEFT JOIN {db_prefix}members AS m ON  (p.id_member = m.id_member)
	WHERE p.ID_FILE = $id AND p.ID_CAT = c.ID_CAT LIMIT 1");


   	// Check if download exists
    if ($db->affected_rows()== 0)
    	fatal_error($txt['downloads_error_no_downloadexist'],false);


    $row = $db->fetch_assoc($dbresult);

    // Check if they can view the download
    Downloads_GetCatPermission($row['ID_CAT'],'view');

	// Checked if they are allowed to view an unapproved download.
	if ($row['approved'] == 0 && $user_info['id'] != $row['id_member'])
	{
		if (!allowedTo('downloads_manage'))
			fatal_error($txt['downloads_error_file_notapproved'],false);
	}

	// Download information
	$context['downloads_file'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'id_member' => $row['id_member'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => standardTime($row['date']),
		'keywords' => $row['keywords'],
		'real_name' => $row['real_name'],
		'totalratings' => $row['totalratings'],
		'rating' => $row['rating'],
		'CAT_TITLE' => $row['CAT_TITLE'],
		'disablerating' => @$row['disablerating'],
		'credits' => $row['credits'],
		'orginalfilename' => $row['orginalfilename'],
		'totaldownloads' => $row['totaldownloads'],
		'lastdownload' => $row['lastdownload'],
		'fileurl' => $row['fileurl'],


	);
	$db->free_result($dbresult);

	Downloads_GetParentLink($row['ID_PARENT']);

	// Link Tree
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=downloads;cat=' . $row['ID_CAT'],
					'name' => $row['CAT_TITLE']
				);
	// Link Tree
	$context['linktree'][] = array(
		'name' => $context['downloads_file']['title']
	);


	// Show Custom Fields
	$result = $db->query('', "
	SELECT
		f.title, d.value
	FROM  ({db_prefix}down_custom_field as f,{db_prefix}down_custom_field_data as d)
	WHERE d.ID_CUSTOM = f.ID_CUSTOM AND d.ID_FILE = " . $context['downloads_file']['ID_FILE'] .  "
	ORDER BY f.roworder desc");
	$context['downloads_custom'] = array();
	while ($row = $db->fetch_assoc($result))
	{
		$context['downloads_custom'][] = array(
			'value' => $row['value'],
			'title' => $row['title'],
		);
	}
	$db->free_result($result);

	if (!empty($modSettings['down_set_commentsnewest']))
		$commentorder = 'DESC';
	else
		$commentorder = 'ASC';
		// Display all user comments
		$dbresult = $db->query('', "
		SELECT
			c.ID_FILE,  c.ID_COMMENT, c.date, c.comment, c.id_member,
			c.lastmodified,c.modified_id_member, m.posts, m.real_name, c.approved, md.real_name modmember
		 FROM {db_prefix}down_comment as c
		 	LEFT JOIN {db_prefix}members AS m ON (c.id_member = m.id_member)
		 	LEFT JOIN {db_prefix}members AS md ON (c.modified_id_member = md.id_member)
		 WHERE c.ID_FILE = " . $context['downloads_file']['ID_FILE'] . " AND c.approved = 1
		 ORDER BY c.ID_COMMENT $commentorder");

		$context['comment_count'] =   $db->affected_rows();
	$context['downloads_comments'] = array();
	while ($row = $db->fetch_assoc($dbresult))
	{
		$context['downloads_comments'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'ID_COMMENT' => $row['ID_COMMENT'],
			'date' => $row['date'],
			'comment' => $row['comment'],
			'id_member' => $row['id_member'],
			'lastmodified' => $row['lastmodified'],
			'modified_id_member' => $row['modified_id_member'],
			'posts' => $row['posts'],
			'real_name' => $row['real_name'],
			'approved' => $row['approved'],
			'modmember' => $row['modmember'],
		);
	}
	$db->free_result($dbresult);

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// Update the number of views.
	$dbresult = $db->query('', "UPDATE {db_prefix}down_file
		SET views = views + 1 WHERE ID_FILE = $id LIMIT 1");


	$context['sub_template']  = 'view_download';

	$context['page_title'] = $context['downloads_file']['title'];

	if (!empty($modSettings['down_who_viewing']))
	{
		$context['can_moderate_forum'] = allowedTo('moderate_forum');


				// Start out with no one at all viewing it.
				$context['view_members'] = array();
				$context['view_members_list'] = array();
				$context['view_num_hidden'] = 0;
				$whoID = (string) $id;

				// Search for members who have this download id set in their GET data.
				$request = $db->query('', "
					SELECT
						lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
						mg.online_color, mg.ID_GROUP, mg.group_name
					FROM {db_prefix}log_online AS lo
						LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lo.id_member)
						LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
					WHERE INSTR(lo.url, 's:9:\"downloads\";s:2:\"sa\";s:4:\"view\";s:2:\"id\";s:" . strlen($whoID ) .":\"$id\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'");
				while ($row = $db->fetch_assoc($request))
				{
					if (empty($row['id_member']))
						continue;

					if (!empty($row['online_color']))
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</a>';
					else
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';

					$is_buddy = in_array($row['id_member'], $user_info['buddies']);
					if ($is_buddy)
						$link = '<b>' . $link . '</b>';

					// Add them both to the list and to the more detailed list.
					if (!empty($row['show_online']) || allowedTo('moderate_forum'))
						$context['view_members_list'][$row['log_time'] . $row['member_name']] = empty($row['show_online']) ? '<i>' . $link . '</i>' : $link;
					$context['view_members'][$row['log_time'] . $row['member_name']] = array(
						'id' => $row['id_member'],
						'username' => $row['member_name'],
						'name' => $row['real_name'],
						'group' => $row['ID_GROUP'],
						'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
						'link' => $link,
						'is_buddy' => $is_buddy,
						'hidden' => empty($row['show_online']),
					);

					if (empty($row['show_online']))
						$context['view_num_hidden']++;
				}

				// The number of guests is equal to the rows minus the ones we actually used ;).
				$context['view_num_guests'] = $db->num_rows($request) - count($context['view_members']);
				$db->free_result($request);

				// Sort the list.
				krsort($context['view_members']);
				krsort($context['view_members_list']);


	}
}

function Downloads_AddDownload()
{
	global $context, $txt, $modSettings, $user_info, $sourcedir;


	isAllowedTo('downloads_add');

	if (isset($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];
	else
		$cat = 0;

	$context['down_cat'] = $cat;

	Downloads_GetCatPermission($cat,'addfile');

	$db = database();

	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

		$dbresult = $db->query('', "
		SELECT
			c.ID_CAT, c.title, p.view, p.addfile
		FROM {db_prefix}down_cat AS c
			LEFT JOIN {db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		WHERE c.redirect = 0 ORDER BY c.roworder ASC");
		if ($db->num_rows($dbresult) == 0)
		 	fatal_error($txt['downloads_error_no_catexists'] , false);

		$context['downloads_cat'] = array();
		 while($row = $db->fetch_assoc($dbresult))
			{
				// Check if they have permission to add to this category.
				if ($row['view'] == '0' || $row['addfile'] == '0' )
					continue;

				$context['downloads_cat'][] = array(
					'ID_CAT' => $row['ID_CAT'],
					'title' => $row['title'],
				);
			}
		$db->free_result($dbresult);

	$result = $db->query('', "
	SELECT
		title, defaultvalue, is_required, ID_CUSTOM
	FROM  {db_prefix}down_custom_field
	WHERE ID_CAT = " . $cat);
	$context['downloads_custom'] = array();
	while ($row = $db->fetch_assoc($result))
	{
			$context['downloads_custom'][] = array(
					'ID_CUSTOM' => $row['ID_CUSTOM'],
					'title' => $row['title'],
					'defaultvalue' => $row['defaultvalue'],
					'is_required' => $row['is_required'],
				);
	}
	$db->free_result($result);

	// Get Quota Limits to Display
	$context['quotalimit'] = Downloads_GetQuotaGroupLimit($user_info['id']);
	$context['userspace'] = Downloads_GetUserSpaceUsed($user_info['id']);

	$context['sub_template']  = 'add_download';

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_adddownload'];

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// Needed for the WYSIWYG editor.
	require_once(SUBSDIR . '/Editor.subs.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'descript',
		'value' => '',
		'width' => '90%',
		'form' => 'picform',
		'labels' => array(
			'post_button' => '',
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];


}

function Downloads_AddDownload2()
{
	global $txt, $scripturl, $modSettings, $sourcedir, $gd2, $user_info;

	isAllowedTo('downloads_add');

    $db = database();

	// Check if downloads path is writable
	if (!is_writable($modSettings['down_path']))
		fatal_error($txt['downloads_write_error'] . $modSettings['down_path']);


	$title = Util::htmlspecialchars($_REQUEST['title'],ENT_QUOTES);
	$description = Util::htmlspecialchars($_REQUEST['descript'],ENT_QUOTES);
	$keywords = Util::htmlspecialchars($_REQUEST['keywords'],ENT_QUOTES);
	$cat = (int) $_REQUEST['cat'];
	$fileurl = htmlspecialchars($_REQUEST['fileurl'],ENT_QUOTES);
	$allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;
	$sendemail = isset($_REQUEST['sendemail']) ? 1 : 0;
	$filesize = 0;

	Downloads_GetCatPermission($cat,'addfile');


	// Check if downloads are auto approved
	$approved = (allowedTo('downloads_autoapprove') ? 1 : 0);

	// Allow comments on file if no setting set.
	if (empty($modSettings['down_commentchoice']))
		$allowcomments = 1;


	if ($title == '')
		fatal_error($txt['downloads_error_no_title'],false);
	if ($cat == '')
		fatal_error($txt['downloads_error_no_cat'],false);

	if ($modSettings['down_set_enable_multifolder'])
		Downloads_CreateDownloadFolder();


		$result = $db->query('', "
		SELECT
			f.title, f.is_required, f.ID_CUSTOM
		FROM  {db_prefix}down_custom_field as f
		WHERE f.is_required = 1 AND f.ID_CAT = " . $cat);
		while ($row2 = $db->fetch_assoc($result))
		{
	 		if (!isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
	 		{
	 			fatal_error($txt['downloads_err_req_custom_field'] . $row2['title'], false);
	 		}
	 		else
	 		{
	 			if ($_REQUEST['cus_' . $row2['ID_CUSTOM']] == '')
	 				fatal_error($txt['downloads_err_req_custom_field'] . $row2['title'], false);
	 		}
	 	}
		$db->free_result($result);


	// Get category infomation
	$dbresult = $db->query('', "
	SELECT
		ID_BOARD,locktopic
	FROM {db_prefix}down_cat
	WHERE ID_CAT = $cat");
	$rowcat = $db->fetch_assoc($dbresult);
	$db->free_result($dbresult);


	// Process Uploaded file
	if (isset($_FILES['download']['name']) && $_FILES['download']['name'] != '')
	{
		// Store the orginal filename
		$orginalfilename =  $db->escape_string($_FILES['download']['name']);

		// Get the filesize
		$filesize = $_FILES['download']['size'];


		if (!empty($modSettings['down_max_filesize']) && $filesize > $modSettings['down_max_filesize'])
		{
			// Delete the temp file
			@unlink($_FILES['download']['tmp_name']);
			fatal_error($txt['downloads_error_file_filesize'] . Downloads_format_size($modSettings['down_max_filesize'] , 2),false);
		}


		// Check Quota
		$quotalimit = Downloads_GetQuotaGroupLimit($user_info['id']);
		$userspace = Downloads_GetUserSpaceUsed($user_info['id']);
		// Check if exceeds quota limit or if there is a quota
		if ($quotalimit != 0  &&  ($userspace + $filesize) >  $quotalimit)
		{
			@unlink($_FILES['download']['tmp_name']);
			fatal_error($txt['downloads_error_space_limit'] . Downloads_format_size($userspace, 2) . ' / ' . Downloads_format_size($quotalimit, 2),false);
		}

		// Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
		$filename = $user_info['id'] . '_' . date('d_m_y_g_i_s'); //. '.' . $extension;

		$extrafolder = '';

		if ($modSettings['down_set_enable_multifolder'])
			$extrafolder = $modSettings['down_folder_id'] . '/';


		move_uploaded_file($_FILES['download']['tmp_name'], $modSettings['down_path'] . $extrafolder .  $filename);
		@chmod($modSettings['down_path'] . $extrafolder .  $filename, 0644);


		// Create the Database entry
		$t = time();
		$file_id = 0;

		$db->query('', "INSERT INTO {db_prefix}down_file
							(ID_CAT, filesize,filename, orginalfilename, keywords, title, description,id_member,date,approved,allowcomments,sendemail)
						VALUES ($cat, $filesize, '" . $extrafolder . $filename . "', '$orginalfilename',   '$keywords','$title', '$description'," . $user_info['id']  . ",$t,$approved, $allowcomments,$sendemail)");

		$file_id = $db->insert_id('{db_prefix}down_file', 'id_file');

		// If we are using multifolders get the next folder id
		if ($modSettings['down_set_enable_multifolder'])
				Downloads_ComputeNextFolderID($file_id);

	}
	else
	{

		// Check if they entered a fileurl
		if (empty($fileurl))
			fatal_error($txt['downloads_error_no_download']);
		else
		{

			if (substr($fileurl, 0, 7) != "http://" && substr($fileurl, 0, 8) != "https://")
            		fatal_error($txt['downloads_error_invalid_upload_url'],false);


			// Process the fileurl specific settings
			// Create the Database entry
			$filesize = Downloads_getRemoteFilesize($fileurl);
			$t = time();
			$file_id = 0;

			$db->query('', "INSERT INTO {db_prefix}down_file
								(id_cat, fileurl, filesize, keywords, title, description,id_member,date,approved,allowcomments,sendemail)
							VALUES ($cat, '$fileurl', '$filesize', '$keywords', '$title', '$description'," . $user_info['id'] . ",$t,$approved, $allowcomments,$sendemail)");

			$file_id = $db->insert_id('{db_prefix}down_file', 'id_file');

		}

	}

					// Check for any custom fields
					$result = $db->query('', "
					SELECT
						f.title, f.is_required, f.ID_CUSTOM
					FROM  {db_prefix}down_custom_field as f
					WHERE f.ID_CAT = " . $cat);
					while ($row2 = $db->fetch_assoc($result))
					{
						if (isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
						{

							$custom_data = Util::htmlspecialchars($_REQUEST['cus_' . $row2['ID_CUSTOM']],ENT_QUOTES);

							$db->query('', "INSERT INTO {db_prefix}down_custom_field_data
							(ID_FILE, ID_CUSTOM, value)
							VALUES('$file_id', " . $row2['ID_CUSTOM'] . ", '$custom_data')");
						}
					}
					$db->free_result($result);



				if ($filesize != 0)
					Downloads_UpdateUserFileSizeTable($user_info['id'],$filesize);

				if ($rowcat['ID_BOARD'] != 0 && $approved == 1)
				{
					// Create the post
					require_once(SUBSDIR . '/Post.subs.php');

					$showpostlink = '[url]' . $scripturl . '?action=downloads;sa=view;down=' . $file_id . '[/url]';

					$msgOptions = array(
						'id' => 0,
						'subject' => $title,
						'body' => '[b]' . $title . "[/b]\n\n$showpostlink",
						'icon' => 'xx',
						'smileys_enabled' => 1,
						'attachments' => array(),
					);
					$topicOptions = array(
						'id' => 0,
						'board' => $rowcat['ID_BOARD'],
						'poll' => null,
						'lock_mode' => $rowcat['locktopic'],
						'sticky_mode' => null,
						'mark_as_read' => true,
					);
					$posterOptions = array(
						'id' => $user_info['id'],
						'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']),
					);
					preparsecode($msgOptions['body']);


					createPost($msgOptions, $topicOptions, $posterOptions);

					$ID_TOPIC = $topicOptions['id'];

					// Update the download with the topic id
					$db->query('', "UPDATE {db_prefix}down_file SET ID_TOPIC = $ID_TOPIC WHERE ID_FILE = $file_id LIMIT 1");


				}


				Downloads_UpdateCategoryTotals($cat);




		// Redirect to the users files page.
		if ($user_info['id'] != 0)
			redirectexit('action=downloads;sa=myfiles;u=' . $user_info['id']);
		else
			redirectexit('action=downloads;cat=' . $cat);

}

function Downloads_EditDownload()
{
	global $context, $txt, $modSettings, $user_info, $sourcedir;

	$db = database();

	is_not_guest();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

		if ($user_info['is_guest'])
			$groupid = -1;
		else
			$groupid =  $user_info['groups'][0];

	// Check if the user owns the file or is admin
    $dbresult = $db->query('', "
    SELECT
    	p.ID_FILE, p.allowcomments, p.ID_CAT, p.keywords, p.commenttotal, p.filesize,
    	p.filename, p.approved, p.views, p.title, p.id_member,
      	m.real_name, p.date, p.description, p.sendemail, p.fileurl,p.orginalfilename
    FROM {db_prefix}down_file as p
       LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
     WHERE p.ID_FILE = $id  LIMIT 1");
	if ($db->affected_rows()== 0)
    	fatal_error($txt['downloads_error_no_downloadexist'],false);
    $row = $db->fetch_assoc($dbresult);


    // Check the category permission
	Downloads_GetCatPermission($row['ID_CAT'],'editfile');

	// Download information
	$context['downloads_file'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'id_member' => $row['id_member'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'fileurl' => $row['fileurl'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => standardTime($row['date']),
		'keywords' => $row['keywords'],
		'real_name' => $row['real_name'],
		'sendemail' => $row['sendemail'],
		'orginalfilename' => $row['orginalfilename'],
	);
	$db->free_result($dbresult);


	// Needed for the WYSIWYG editor.
	require_once(SUBSDIR . '/Editor.subs.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'descript',
		'value' => $row['description'],
		'width' => '90%',
		'form' => 'picform',
		'labels' => array(
			'post_button' => '',
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];

	// Custom Fields
	$result = $db->query('', "
	SELECT
		f.title, f.is_required, f.ID_CUSTOM, d.value
	FROM  {db_prefix}down_custom_field as f
		LEFT JOIN {db_prefix}down_custom_field_data as d ON (d.ID_CUSTOM = f.ID_CUSTOM)
	WHERE ID_FILE = " . $context['downloads_file']['ID_FILE'] . " AND ID_CAT = " . $context['downloads_file']['ID_CAT']);
	$context['downloads_custom'] = array();
	while ($row = $db->fetch_assoc($result))
	{
		$context['downloads_custom'][] = array(
			'ID_CUSTOM' => $row['ID_CUSTOM'],
			'title' => $row['title'],
			'is_required' => $row['is_required'],
			'value' => $row['value'],

		);
	}
	$db->free_result($result);


	if (allowedTo('downloads_manage') || (allowedTo('downloads_edit') && $user_info['id'] == $context['downloads_file']['id_member']))
	{
		// Get the category information

		 	$dbresult = $db->query('', "
		 	SELECT
		 		c.ID_CAT, c.title, p.view, p.addfile
		 	FROM {db_prefix}down_cat AS c
		 		LEFT JOIN {db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		 	WHERE c.redirect = 0 ORDER BY c.roworder ASC");
			$context['downloads_cat'] = array();
		 	while($row = $db->fetch_assoc($dbresult))
			{
				// Check if they have permission to add to this category.
				if ($row['view'] == '0' || $row['addfile'] == '0' )
					continue;

				$context['downloads_cat'][] = array(
				'ID_CAT' => $row['ID_CAT'],
				'title' => $row['title'],
				);
			}
			$db->free_result($dbresult);

		// Get Quota Limits to Display
		$context['quotalimit'] = Downloads_GetQuotaGroupLimit($user_info['id']);
		$context['userspace'] = Downloads_GetUserSpaceUsed($user_info['id']);

		$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_editdownload'];
		$context['sub_template']  = 'edit_download';

		// Check if spellchecking is both enabled and actually working.
		$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	}
	else
		fatal_error($txt['downloads_error_noedit_permission']);
}

function Downloads_EditDownload2()
{
	global $txt, $modSettings, $sourcedir, $user_info;

	is_not_guest();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	$db = database();

	// Check the user permissions
    $dbresult = $db->query('', "
    SELECT
    	id_member,ID_CAT, filename,filesize
    FROM {db_prefix}down_file
    WHERE ID_FILE = $id LIMIT 1");
    $row = $db->fetch_assoc($dbresult);
	$memID = $row['id_member'];
	$oldfilesize = $row['filesize'];
	$oldfilename = $row['filename'];

	// Check the category permission
	Downloads_GetCatPermission($row['ID_CAT'],'editfile');

	$db->free_result($dbresult);
	if (allowedTo('downloads_manage') || (allowedTo('downloads_edit') && $user_info['id'] == $memID))
	{

		if (!is_writable($modSettings['down_path']))
			fatal_error($txt['downloads_write_error'] . $modSettings['down_path']);

		$title = Util::htmlspecialchars($_REQUEST['title'],ENT_QUOTES);
		$description = Util::htmlspecialchars($_REQUEST['descript'],ENT_QUOTES);
		$keywords = Util::htmlspecialchars($_REQUEST['keywords'],ENT_QUOTES);
		$cat = (int) $_REQUEST['cat'];
		$allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;
		$sendemail = isset($_REQUEST['sendemail']) ? 1 : 0;
		$fileurl = htmlspecialchars($_REQUEST['fileurl'],ENT_QUOTES);
		$filesize = 0;

		// Check if downloads are auto approved
		$approved = (allowedTo('downloads_autoapprove') ? 1 : 0);

		// Allow comments on file if no setting set.
		if (empty($modSettings['down_commentchoice']))
			$allowcomments = 1;

		if ($title == '')
			fatal_error($txt['downloads_error_no_title'],false);
		if ($cat == '')
			fatal_error($txt['downloads_error_no_cat'],false);



		// Check for any required custom fields
		$result = $db->query('', "
		SELECT
			f.title, f.is_required, f.ID_CUSTOM
		FROM  {db_prefix}down_custom_field as f
		WHERE f.is_required = 1 AND f.ID_CAT = " . $cat);
		while ($row2 = $db->fetch_assoc($result))
		{
	 		if (!isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
	 		{
	 			fatal_error($txt['downloads_err_req_custom_field'] . $row2['title'], false);
	 		}
	 		else
	 		{
	 			if ($_REQUEST['cus_' . $row2['ID_CUSTOM']] == '')
	 				fatal_error($txt['downloads_err_req_custom_field'] . $row2['title'], false);
	 		}
	 	}
		$db->free_result($result);

		// Process Uploaded file
		if (isset($_FILES['download']['name']) && $_FILES['download']['name'] != '')
		{

			// Store the orginal filename
			$orginalfilename =  $db->escape_string($_FILES['download']['name']);
			$filesize = $_FILES['download']['size'];


			if (!empty($modSettings['down_max_filesize']) && $filesize > $modSettings['down_max_filesize'])
			{
				// Delete the temp file
				@unlink($_FILES['download']['tmp_name']);
				fatal_error($txt['downloads_error_file_filesize'] . Downloads_format_size($modSettings['down_max_filesize'], 2) ,false);
			}
			// Check Quota
			$quotalimit = Downloads_GetQuotaGroupLimit($user_info['id']);
			$userspace = Downloads_GetUserSpaceUsed($user_info['id']);
			// Check if exceeds quota limit or if there is a quota
			if ($quotalimit != 0  &&  ($userspace + $filesize) >  $quotalimit)
			{
				@unlink($_FILES['download']['tmp_name']);
				fatal_error($txt['downloads_error_space_limit'] . Downloads_format_size($userspace, 2) . ' / ' . Downloads_format_size($quotalimit, 2) ,false);
			}

			// Delete the old files
			@unlink($modSettings['down_path'] . $oldfilename );

			$extrafolder = '';

			if ($modSettings['down_set_enable_multifolder'])
				$extrafolder = $modSettings['down_folder_id'] . '/';


			// Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
			$filename = $user_info['id'] . '_' . date('d_m_y_g_i_s');
			move_uploaded_file($_FILES['download']['tmp_name'], $modSettings['down_path'] . $extrafolder . $filename);
			@chmod($modSettings['down_path'] . $extrafolder . $filename, 0644);


			// Update the Database entry
			$t = time();

			$db->query('', "UPDATE {db_prefix}down_file
					SET ID_CAT = $cat, filesize = $filesize, filename = '" . $extrafolder . $filename . "', approved = $approved,
					 date =  $t, title = '$title', description = '$description', keywords = '$keywords',
					  allowcomments = $allowcomments, sendemail = $sendemail, orginalfilename = '$orginalfilename'
					  WHERE ID_FILE = $id LIMIT 1");

			Downloads_UpdateUserFileSizeTable($memID,$oldfilesize * -1);
			Downloads_UpdateUserFileSizeTable($memID,$filesize);


			// Update the file totals
			if ($cat != $row['ID_CAT'])
			{
				Downloads_UpdateCategoryTotals($cat);
				Downloads_UpdateCategoryTotals($row['ID_CAT']);
			}


					// Change the file owner if selected
					if (allowedTo('downloads_manage') && isset($_REQUEST['pic_postername']))
					{
						$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
						$pic_postername = str_replace("'",'', $pic_postername);
						$pic_postername = str_replace('\\','', $pic_postername);
						$pic_postername = Util::htmlspecialchars($pic_postername, ENT_QUOTES);

						$memid = 0;

						$dbresult = $db->query('', "
						SELECT
							real_name, id_member
						FROM {db_prefix}members
						WHERE real_name = '$pic_postername' OR member_name = '$pic_postername'  LIMIT 1");
						$row = $db->fetch_assoc($dbresult);
						$db->free_result($dbresult);

						if ($db->affected_rows() != 0)
						{
							// Member found update the file owner

							$memid = $row['id_member'];
							$db->query('', "UPDATE {db_prefix}down_file
							SET id_member = $memid WHERE ID_FILE = $id LIMIT 1");

						}

					}
					Downloads_UpdateCategoryTotalByFileID($id);
					// Redirect to the users files page.
					redirectexit('action=downloads;sa=myfiles;u=' . $user_info['id']);


		}
		else
		{
			// Update the download properties if no upload has been set


			if (!empty($fileurl))
			{

				if (substr($fileurl, 0, 7) != "http://" && substr($fileurl, 0, 8) != "https://")
            		fatal_error($txt['downloads_error_invalid_upload_url'],false);

				$filesize = Downloads_getRemoteFilesize($fileurl);

				$db->query('', "UPDATE {db_prefix}down_file
				SET
				filesize = '$filesize'

				WHERE ID_FILE = $id LIMIT 1");
			}



				$db->query('', "UPDATE {db_prefix}down_file
				SET ID_CAT = $cat, title = '$title', description = '$description', keywords = '$keywords',
				allowcomments = $allowcomments, sendemail = $sendemail, approved = $approved,
				fileurl = '$fileurl'

				WHERE ID_FILE = $id LIMIT 1");


					// Update the file totals
					if ($cat != $row['ID_CAT'])
					{
						Downloads_UpdateCategoryTotals($cat);
						Downloads_UpdateCategoryTotals($row['ID_CAT']);
					}

				// Change the file owner if selected

					if (allowedTo('downloads_manage') && isset($_REQUEST['pic_postername']))
					{
						$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
						$pic_postername = str_replace("'",'', $pic_postername);
						$pic_postername = str_replace('\\','', $pic_postername);
						$pic_postername = Util::htmlspecialchars($pic_postername, ENT_QUOTES);

						$memid = 0;

						$dbresult = $db->query('', "
						SELECT
							real_name, id_member
						FROM {db_prefix}members
						WHERE real_name = '$pic_postername' OR member_name = '$pic_postername'  LIMIT 1");
						$row = $db->fetch_assoc($dbresult);
						$db->free_result($dbresult);

						if ($db->affected_rows() != 0)
						{
							// Member found update the file owner
							$memid = $row['id_member'];
							$db->query('', "UPDATE {db_prefix}down_file
							SET id_member = $memid WHERE ID_FILE = $id LIMIT 1");


						}

					}

					Downloads_UpdateCategoryTotalByFileID($id);

					// Check for any custom fields

					$db->query('', "DELETE FROM  {db_prefix}down_custom_field_data
							WHERE ID_FILE = " . $id);

					$result = $db->query('', "
					SELECT
						f.title, f.is_required, f.ID_CUSTOM
					FROM  {db_prefix}down_custom_field as f
					WHERE f.ID_CAT = " . $cat);
					while ($row2 = $db->fetch_assoc($result))
					{
						if (isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
						{

							$custom_data = Util::htmlspecialchars($_REQUEST['cus_' . $row2['ID_CUSTOM']],ENT_QUOTES);

							$db->query('', "INSERT INTO {db_prefix}down_custom_field_data
							(ID_FILE, ID_CUSTOM, value)
							VALUES('$id', " . $row2['ID_CUSTOM'] . ", '$custom_data')");
						}
					}
					$db->free_result($result);


			// Redirect to the users files page.
			redirectexit('action=downloads;sa=myfiles;u=' . $user_info['id']);

		}

	}
	else
		fatal_error($txt['downloads_error_noedit_permission']);


}

function Downloads_DeleteDownload()
{
	global $context, $txt, $user_info;

	is_not_guest();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	$db = database();

	// Check if the user owns the download or is admin
    $dbresult = $db->query('', "
    SELECT
    	p.ID_FILE, p.fileurl, p.allowcomments, p.ID_CAT, p.keywords, p.commenttotal, p.totaldownloads,
     	p.filesize, p.filename, p.approved, p.views, p.title, p.id_member, p.date, m.real_name, p.description
    FROM {db_prefix}down_file as p
    LEFT JOIN {db_prefix}members AS m ON (p.id_member = m.id_member)
    WHERE ID_FILE = $id  LIMIT 1");
	if ($db->affected_rows()== 0)
    	fatal_error($txt['downloads_error_no_downloadexist'],false);
    $row = $db->fetch_assoc($dbresult);
	// Check the category permission
	Downloads_GetCatPermission($row['ID_CAT'],'delfile');
	// File information
	$context['downloads_file'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'id_member' => $row['id_member'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => standardTime($row['date']),
		'keywords' => $row['keywords'],
		'real_name' => $row['real_name'],
		'fileurl' => $row['fileurl'],
		'totaldownloads' => $row['totaldownloads'],
	);
	$db->free_result($dbresult);

	if (allowedTo('downloads_manage') || (allowedTo('downloads_delete') && $user_info['id'] == $context['downloads_file']['id_member']))
	{
		$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_deldownload'];
		$context['sub_template']  = 'delete_download';

	}
	else
		fatal_error($txt['downloads_error_nodelete_permission']);


}

function Downloads_DeleteDownload2()
{
	global $txt, $user_info;

	$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	$db = database();

	// Check if the user owns the download or is admin
    $dbresult = $db->query('', "
    SELECT
    	p.ID_FILE, p.ID_CAT, p.id_member
    FROM {db_prefix}down_file as p
    WHERE ID_FILE = $id LIMIT 1");
	$row = $db->fetch_assoc($dbresult);

	if (empty($row['ID_FILE']))
		fatal_error($txt['downloads_error_no_file_selected'],false);

	$memID = $row['id_member'];

	$db->free_result($dbresult);
	// Check the category permission

	Downloads_GetCatPermission($row['ID_CAT'],'delfile');

	if (allowedTo('downloads_manage') || (allowedTo('downloads_delete') && $user_info['id'] == $memID))
	{

		Downloads_DeleteFileByID($id);

		Downloads_UpdateCategoryTotals($row['ID_CAT']);

		// Redirect to the users files page.
		redirectexit('action=downloads;sa=myfiles;u=' . $user_info['id']);
	}
	else
		fatal_error($txt['downloads_error_nodelete_permission']);


}



function Downloads_ReportDownload()
{
	global $context, $txt;

	isAllowedTo('downloads_report');
	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);



	$context['downloads_file_id'] = $id;

	$context['sub_template']  = 'report_download';

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_reportdownload'];

}

function Downloads_ReportDownload2()
{
	global $txt, $user_info;

	isAllowedTo('downloads_report');

	$db = database();

	$comment = Util::htmlspecialchars($_REQUEST['comment'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	if ($comment == '')
		fatal_error($txt['downloads_error_no_comment'],false);

	$commentdate = time();

	$db->query('', "INSERT INTO {db_prefix}down_report
			(id_member, comment, date, ID_FILE)
		VALUES (" . $user_info['id'] . ",'$comment', $commentdate,$id)");

	redirectexit('action=downloads;sa=view;down=' . $id);

}

function Downloads_AddComment()
{
	global $context, $mbname, $txt, $modSettings, $user_info, $sourcedir;

	isAllowedTo('downloads_comment');

	$db = database();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);



	$context['downloads_file_id'] = $id;

	// Comments allowed check
    $dbresult = $db->query('', "
    SELECT
    	p.allowcomments, p.ID_CAT
    FROM {db_prefix}down_file as p
    WHERE ID_FILE = $id LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$ID_CAT = $row['ID_CAT'];
	$db->free_result($dbresult);
	// Checked if comments are allowed
	if ($row['allowcomments'] == 0)
	{
		fatal_error($txt['downloads_error_not_allowcomment']);
	}
	Downloads_GetCatPermission($ID_CAT,'addcomment');


	$context['sub_template']  = 'add_comment';

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_addcomment'];

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');



	$modSettings['disable_wysiwyg'] = !empty($modSettings['disable_wysiwyg']) || empty($modSettings['enableBBC']);


	// Needed for the WYSIWYG editor.
	require_once(SUBSDIR . '/Editor.subs.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'comment',
		'value' => '',
		'width' => '90%',
		'form' => 'cprofile',
		'labels' => array(
			'post_button' => $txt['downloads_text_addcomment']
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];



	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');

	// Spam Protect
	spamProtection('spam');

}

function Downloads_AddComment2()
{
	global $scripturl, $txt, $sourcedir, $modSettings, $user_info;

	isAllowedTo('downloads_comment');

    $db = database();


	$comment = Util::htmlspecialchars($_REQUEST['comment'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	// Check if that download allows comments.
    $dbresult = $db->query('', "
    SELECT
    	p.allowcomments, p.ID_CAT, p.sendemail,m.email_address,p.id_member,p.title
    FROM {db_prefix}down_file as p
    LEFT JOIN {db_prefix}members as m ON (p.id_member  = m.id_member)
    WHERE p.ID_FILE = $id LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$mem_email = $row['email_address'];
	$title = $row['title'];
	$doemail = $row['sendemail'];
	$pic_memid = $row['id_member'];

	$db->free_result($dbresult);
	// Checked if comments are allowed
	if ($row['allowcomments'] == 0)
		fatal_error($txt['downloads_error_not_allowcomment']);

	// Check if they are allowed to add comments to that category
	if ($row['ID_CAT'] != 0)
		Downloads_GetCatPermission($row['ID_CAT'],'addcomment');

	if ($comment == '')
		fatal_error($txt['downloads_error_no_comment'],false);

	$commentdate = time();

	// Check if you have automatic approval
	$approved = (allowedTo('downloads_autocomment') ? 1 : 0);

	$db->query('', "INSERT INTO {db_prefix}down_comment
			(id_member, comment, date, ID_FILE,approved)
		VALUES (" . $user_info['id'] . ",'$comment', $commentdate,$id,$approved)");
	$comment_id = $db->insert_id('{db_prefix}down_comment', 'id_comment');

	// Update Comment total
	 $db->query('', "UPDATE {db_prefix}down_file
		SET commenttotal = commenttotal + 1 WHERE ID_FILE = $id LIMIT 1");

	// Check to send email on new comment
	 if ($doemail == 1 && $pic_memid != $user_info['id'] && $pic_memid != 0)
	 {
	 	require_once(SUBSDIR . '/Post.subs.php');
	 	sendmail($mem_email, str_replace("%s", $title, $txt['downloads_notify_subject']), str_replace("%s", $scripturl . '?action=downloads;sa=view;down=' . $id . '#c' . $comment_id, $txt['downloads_notify_body']));
	 }


	redirectexit('action=downloads;sa=view;down=' . $id);

}

function Downloads_EditComment()
{
	global $context, $mbname, $txt, $sourcedir, $modSettings, $user_info;

	is_not_guest();

	$db = database();

	$g_manage = allowedTo('downloads_manage');
	$g_edit_comment = allowedTo('downloads_editcomment');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);




	// Check if allowed to edit the comment
    $dbresult = $db->query('', "
    SELECT
    	ID_COMMENT,ID_FILE,id_member,approved,comment,date,lastmodified
    FROM {db_prefix}down_comment
    WHERE ID_COMMENT = $id LIMIT 1");
	$row = $db->fetch_assoc($dbresult);

   // Comment information
	$context['downloads_comment'] = array(
		'ID_COMMENT' => $row['ID_COMMENT'],
		'ID_FILE' => $row['ID_FILE'],
		'id_member' => $row['id_member'],
		'approved' => $row['approved'],
		'comment' => $row['comment'],
	);

	$db->free_result($dbresult);



	if ($g_manage || $g_edit_comment && $context['downloads_comment']['id_member'] == $user_info['id'])
	{
		$context['sub_template']  = 'edit_comment';

		$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_editcomment'];

		// Check if spellchecking is both enabled and actually working.
		$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

		// Needed for the WYSIWYG editor.
		require_once(SUBSDIR . '/Editor.subs.php');

		// Now create the editor.
		$editorOptions = array(
			'id' => 'comment',
			'value' => $context['downloads_comment']['comment'],
			'width' => '90%',
			'form' => 'cprofile',
			'labels' => array(
				'post_button' => $txt['downloads_text_editcomment']
			),
		);
		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];


	}
	else
		fatal_error($txt['downloads_error_nocomedit_permission']);


}

function Downloads_EditComment2()
{
	global $context, $txt, $user_info;

	is_not_guest();

	$db = database();

	$g_manage = allowedTo('downloads_manage');
	$g_edit_comment = allowedTo('downloads_editcomment');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);


	// Check if allowed to edit the comment
    $dbresult = $db->query('', "
    SELECT
    	id_member,ID_FILE
    FROM {db_prefix}down_comment
    WHERE ID_COMMENT = $id LIMIT 1");
	$row = $db->fetch_assoc($dbresult);

   // Comment information
	$context['downloads_comment'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'id_member' => $row['id_member'],

	);

	$db->free_result($dbresult);

	if ($g_manage || $g_edit_comment && $context['downloads_comment']['id_member'] == $user_info['id'])
	{

		$comment = Util::htmlspecialchars($_REQUEST['comment'],ENT_QUOTES);
		if ($comment == '')
			fatal_error($txt['downloads_error_no_comment'],false);

		$edittime = time();
		// Check if you have automatic approval
		$approved = (allowedTo('downloads_autocomment') ? 1 : 0);
		// Update the comment
	  $dbresult = $db->query('', "UPDATE {db_prefix}down_comment
		SET comment = '$comment', lastmodified = '$edittime',modified_id_member = " . $user_info['id'] . ", approved =  $approved WHERE ID_COMMENT = $id LIMIT 1");
		// Redirect to the file
		redirectexit('action=downloads;sa=view;down=' .  $context['downloads_comment']['ID_FILE']);
	}
	else
		fatal_error($txt['downloads_error_nocomedit_permission']);
}



function Downloads_Search()
{
	global $context, $mbname, $txt, $user_info;



	// Is the user allowed to view the downloads?
	isAllowedTo('downloads_view');

	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];


	$db = database();

	$dbresult = $db->query('', "
	SELECT
		c.ID_CAT, c.title, p.view
	FROM {db_prefix}down_cat as c
	LEFT JOIN {db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
	ORDER BY c.roworder ASC");
	$context['downloads_cat'] = array();
	 while($row = $db->fetch_assoc($dbresult))
		{
			// Check if they have permission to search these categories
			if ($row['view'] == '0')
					continue;

			$context['downloads_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title']
			);
		}
	$db->free_result($dbresult);

	$context['sub_template']  = 'search';

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_search'];
}

function Downloads_Search2()
{
	global $context, $mbname, $user_info, $txt;

	// Is the user allowed to view the downloads?
	isAllowedTo('downloads_view');


	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	$db = database();

	if (isset($_REQUEST['q']))
	{
		$data = json_decode(base64_decode($_REQUEST['q']),true);
		@$_REQUEST['cat'] = $data['cat'];
		@$_REQUEST['key'] = $data['keyword'];
		@$_REQUEST['searchkeywords'] = $data['searchkeywords'];
		@$_REQUEST['searchtitle'] = $data['searchtitle'];
		@$_REQUEST['searchdescription'] = $data['searchdescription'];
		@$_REQUEST['searchcustom'] = $data['searchcustom'];
		@$_REQUEST['daterange'] = $data['daterange'];
		@$_REQUEST['pic_postername'] = $data['pic_postername'];
		@$_REQUEST['searchfor'] = $data['searchfor'];

	}



		@$cat = (int) $_REQUEST['cat'];

		// Check if keyword search was selected
		@$keyword =  Util::htmlspecialchars($_REQUEST['key'],ENT_QUOTES);
		$searchArray = array();
		$searchArray['keyword'] = $keyword;
		$context['downloads_search_query_encoded'] = base64_encode(json_encode($searchArray));

		if ($keyword == '')
		{
			// Probably a normal Search
			if (empty($_REQUEST['searchfor']))
				fatal_error($txt['downloads_error_no_search'],false);

			$searchfor =  Util::htmlspecialchars($_REQUEST['searchfor'],ENT_QUOTES);


			if (Util::strlen($searchfor) <= 3)
				fatal_error($txt['downloads_error_search_small'],false);

			// Check the search options
			@$searchkeywords = $_REQUEST['searchkeywords'];
			@$searchtitle = $_REQUEST['searchtitle'];
			@$searchdescription = $_REQUEST['searchdescription'];
			@$daterange = (int) $_REQUEST['daterange'];
			$memid = 0;

			// Check if searching by member id
			if (!empty($_REQUEST['pic_postername']))
			{
				$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
				$pic_postername = str_replace("'",'', $pic_postername);
				$pic_postername = str_replace('\\','', $pic_postername);
				$pic_postername = Util::htmlspecialchars($pic_postername, ENT_QUOTES);
				$searchArray['pic_postername'] = $pic_postername;


				$dbresult = $db->query('', "
						SELECT
							real_name, id_member
						FROM {db_prefix}members
						WHERE real_name = '$pic_postername' OR member_name = '$pic_postername'  LIMIT 1");
						$row = $db->fetch_assoc($dbresult);
						$db->free_result($dbresult);

					if ($db->affected_rows() != 0)
					{
						$memid = $row['id_member'];
					}
			}


			$searchArray['searchfor'] = $searchfor;
			$searchArray['searchkeywords'] = $searchkeywords;
			$searchArray['cat'] = $cat;
			$searchArray['searchtitle'] = $searchtitle;
			$searchArray['searchdescription'] = $searchdescription;
			$searchArray['daterange'] = $daterange;
			$context['downloads_search_query_encoded'] = base64_encode(json_encode($searchArray));


			$context['catwhere'] = '';


			if ($cat != 0)
				$context['catwhere'] = "p.ID_CAT = $cat AND ";

			// Check if searching by member id
			if ($memid != 0)
				$context['catwhere'] .= "p.id_member = $memid AND ";

			// Date Range check
			if ($daterange!= 0)
			{
				$currenttime = time();
				$pasttime = $currenttime - ($daterange * 24 * 60 * 60);

				$context['catwhere'] .=  "(p.date BETWEEN '" . $pasttime . "' AND '" . $currenttime . "')  AND";
			}

			$s1 = 1;
			$searchquery = '';
			if ($searchtitle)
				$searchquery = "p.title LIKE '%$searchfor%' ";
			else
				$s1 = 0;

			$s2 = 1;
			if ($searchdescription)
			{
				if ($s1 == 1)
					$searchquery = "p.title LIKE '%$searchfor%' OR p.description LIKE '%$searchfor%'";
				else
					$searchquery = "p.description LIKE '%$searchfor%'";
			}
			else
				$s2 = 0;

			if ($searchkeywords)
			{
				if ($s1 == 1 || $s2 == 1)
					$searchquery .= " OR p.keywords LIKE '$searchfor'";
				else
					$searchquery = "p.keywords LIKE '$searchfor'";
			}


			if ($searchquery == '')
				$searchquery = "p.title LIKE '%$searchfor%' ";

			$context['downloads_search_query'] = $searchquery;



			$context['downloads_search'] = $searchfor;
		}
		else
		{
			// Search for the keyword


			//Debating if I should add string length check for keywords...
			//if (strlen($keyword) <= 3)
				//fatal_error($txt['downloads_error_search_small']);

			$context['downloads_search'] = $keyword;

			$context['downloads_search_query'] = "p.keywords LIKE '$keyword'";
		}

	$downloads_where = '';
	if (isset($context['catwhere']))
		$downloads_where = $context['catwhere'];

	$context['downloads_where'] = $downloads_where;


	$context['start'] = (int) $_REQUEST['start'];

    $dbresult = $db->query('', "
    SELECT
    	p.ID_FILE
    FROM {db_prefix}down_file as p
    	LEFT JOIN {db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupid) AND c.ID_CAT = p.ID_CAT)
    WHERE  " . $downloads_where . " p.approved = 1 AND (c.view IS NULL || c.view =1)  AND (" . $context['downloads_search_query'] . ") GROUP by p.ID_FILE");
    $numrows = $db->num_rows($dbresult);
    $db->free_result($dbresult);

    $total = $numrows;
	$context['downloads_total'] = $total;


    $dbresult = $db->query('', "
    SELECT
    	p.ID_FILE, p.ID_CAT, p.commenttotal, p.rating, p.filesize, p.title,
    	p.views, p.id_member, m.real_name, p.date, p.totaldownloads, p.totalratings
    FROM {db_prefix}down_file as p
   	 	LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
   	 	LEFT JOIN {db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupid) AND c.ID_CAT = p.ID_CAT)
    WHERE  " . $downloads_where . " p.approved = 1 AND (c.view IS NULL || c.view =1)  AND (" . $context['downloads_search_query'] . ") GROUP by p.ID_FILE
    LIMIT $context[start],10");
    $context['downloads_files'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_files'][] = $row;

		}
	$db->free_result($dbresult);


	$context['sub_template']  = 'search_results';

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_searchresults'];
}

function Downloads_RateDownload()
{
	global $txt, $user_info;

	is_not_guest();

	// Check if they are allowed to rate download
	isAllowedTo('downloads_ratefile');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);
	$rating = (int) $_REQUEST['rating'];
	if (empty($rating))
		fatal_error($txt['downloads_error_no_rating_selected']);

	$db = database();

	// Check if they rated this download?
    $dbresult = $db->query('', "
    SELECT
    	id_member, ID_FILE
    FROM {db_prefix}down_rating
    WHERE id_member = " . $user_info['id'] . " AND ID_FILE = $id");

    $found = $db->affected_rows();
 	$db->free_result($dbresult);

	// Get the download owner
    $dbresult = $db->query('', "
    SELECT
    	id_member
    FROM {db_prefix}down_file
    WHERE ID_FILE = $id LIMIT 1");
    $row = $db->fetch_assoc($dbresult);
	$db->free_result($dbresult);
	// Check if they are rating their own download.
	if ($user_info['id'] == $row['id_member'])
		fatal_error($txt['downloads_error_norate_own'],false);

	if ($found != 0)
		fatal_error($txt['downloads_error_already_rated'],false);

	// Check the Rating
	if ($rating < 1 || $rating > 5)
		$rating = 3;

	// Add the Rating
	$db->query('', "INSERT INTO {db_prefix}down_rating (id_member, ID_FILE, value) VALUES (" . $user_info['id'] . ", $id,$rating)");

	// Add rating information to the download
	$db->query('', "
	UPDATE {db_prefix}down_file
		SET totalratings = totalratings + 1, rating = rating + $rating
	WHERE ID_FILE = $id LIMIT 1");

	// Redirect to the download
	redirectexit('action=downloads;sa=view;down=' . $id);

}

function Downloads_ViewRating()
{
	global $context, $mbname, $txt;

	// Get the download ID for the ratings
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	$context['downloads_id'] = $id;

	$db = database();

	$dbresult = $db->query('', "
	SELECT
		r.ID, r.value, r.ID_FILE, r.id_member, m.real_name
	FROM {db_prefix}down_rating as r, {db_prefix}members AS m
	WHERE r.ID_FILE = $id AND r.id_member = m.id_member");
	$context['downloads_rating'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_rating'][] = $row;

		}
	$db->free_result($dbresult);

	isAllowedTo('downloads_manage');

	$context['sub_template']  = 'view_rating';

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_viewratings'];

}

function Downloads_DeleteRating()
{
	global $scripturl, $txt;
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_rating_selected']);

	$db = database();

	// First lookup the ID to get the download id and value of rating
	 $dbresult = $db->query('', "
	 SELECT
	 	ID, ID_FILE, value
	 FROM {db_prefix}down_rating
	 WHERE ID = $id LIMIT 1");
	 $row = $db->fetch_assoc($dbresult);
	 $value = $row['value'];
	 $fileid = $row['ID_FILE'];
	 $db->free_result($dbresult);
	// Delete the Rating
	$db->query('', "DELETE FROM {db_prefix}down_rating
	WHERE ID = " . $id . ' LIMIT 1');
	// Update the download rating information
	$dbresult = $db->query('', "UPDATE {db_prefix}down_file SET totalratings = totalratings - 1, rating = rating - $value WHERE ID_FILE = $fileid LIMIT 1");
	// Redirect to the ratings
	redirectexit('action=downloads;sa=viewrating&id=' .  $fileid);
}

function Downloads_Stats()
{
	global $context, $mbname,$txt, $context,  $user_info, $scripturl;

	// Is the user allowed to view the downloads?
	isAllowedTo('downloads_view');

	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	$db = database();

	// Get views total and comments total and total filesize
	$result = $db->query('', "
	SELECT
		SUM(views) AS views, SUM(filesize) AS filesize, SUM(commenttotal) AS commenttotal,
	 	COUNT(*) AS filetotal
	FROM {db_prefix}down_file");
	$row = $db->fetch_assoc($result);
	$db->free_result($result);

	$result2 = $db->query('', "
	SELECT
		COUNT(*) AS filetotal
	FROM {db_prefix}down_file");
	$row2 = $db->fetch_assoc($result2);
	$db->free_result($result2);

	$context['total_files'] = $row2['filetotal'];
	$context['total_views'] = $row['views'];
	$context['total_filesize'] =  Downloads_format_size($row['filesize'], 2) ;
	$context['total_comments'] = $row['commenttotal'];


	// Top Viewed Downloads
	$result = $db->query('', "
	SELECT
		p.ID_FILE, p.title, p.views
	FROM {db_prefix}down_file as p
	LEFT JOIN {db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupid) AND c.ID_CAT = p.ID_CAT)
	WHERE (p.approved =1  AND (c.view IS NULL || c.view =1)) AND p.views > 0 GROUP by p.ID_FILE
	ORDER BY p.views,p.title,p.ID_FILE DESC LIMIT 10");
	$context['top_viewed'] = array();
	$max_views = 1;
	while ($row = $db->fetch_assoc($result))
	{
		$context['top_viewed'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'views' => $row['views'],
			'link' => '<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);

		if ($max_views < $row['views'])
			$max_views = $row['views'];
	}
	$db->free_result($result);

	foreach ($context['top_viewed'] as $i => $file)
		$context['top_viewed'][$i]['percent'] = round(($file['views'] * 100) / $max_views);

	// Top Rated
	$result = $db->query('', "
	SELECT
		p.ID_FILE, p.title, p.totalratings, p.rating, (p.rating / p.totalratings ) AS ratingaverage
	FROM {db_prefix}down_file as p
	LEFT JOIN {db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupid) AND c.ID_CAT = p.ID_CAT)
	WHERE (p.approved =1  AND (c.view IS NULL || c.view =1)) AND p.totalratings > 0 GROUP by p.ID_FILE
	ORDER BY ratingaverage DESC LIMIT 10");
	$context['top_rating'] = array();
	$max_rating = 1;
	while ($row = $db->fetch_assoc($result))
	{
		$context['top_rating'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'rating' => $row['rating'],
			'link' => '<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);

		if ($max_rating < $row['rating'])
			$max_rating = $row['rating'];
	}
	$db->free_result($result);

	foreach ($context['top_rating'] as $i => $file)
		$context['top_rating'][$i]['percent'] = round(($file['rating'] * 100) / $max_rating);

	// Most Commented
	$result = $db->query('', "
	SELECT
		p.ID_FILE, p.title,p.commenttotal
	FROM {db_prefix}down_file as p
	LEFT JOIN {db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupid) AND c.ID_CAT = p.ID_CAT)
	WHERE (p.approved =1  AND (c.view IS NULL || c.view =1)) AND p.commenttotal > 0  GROUP by p.ID_FILE
	ORDER BY p.commenttotal DESC LIMIT 10");
	$context['most_comments'] = array();
	$max_commenttotal = 1;
	while ($row = $db->fetch_assoc($result))
	{
		$context['most_comments'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'commenttotal' => $row['commenttotal'],
			'link' => '<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);

		if ($max_commenttotal < $row['commenttotal'])
			$max_commenttotal = $row['commenttotal'];
	}
	$db->free_result($result);

	foreach ($context['most_comments'] as $i => $file)
		$context['most_comments'][$i]['percent'] = round(($file['commenttotal'] * 100) / $max_commenttotal);

	// Last 10 downloads uploaded
	$result = $db->query('', "
	SELECT
		p.ID_FILE, p.title
	FROM {db_prefix}down_file as p
	LEFT JOIN {db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupid) AND c.ID_CAT = p.ID_CAT)
	WHERE (p.approved =1  AND (c.view IS NULL || c.view =1))  GROUP by p.ID_FILE
	ORDER BY p.ID_FILE DESC LIMIT 10");
	$context['last_upload'] = array();
	while ($row = $db->fetch_assoc($result))
	{
		$context['last_upload'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'link' => '<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);
	}
	$db->free_result($result);


	// Load the template
	$context['sub_template']  = 'stats';
	// Set the page title
	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_stats'];

}

function Downloads_MyFiles()
{
	global $context, $mbname, $txt, $modSettings, $scripturl, $user_info;

	isAllowedTo('downloads_view');



	$u = (int) $_REQUEST['u'];
	if (empty($u))
		fatal_error($txt['downloads_error_no_user_selected']);

	$db = database();
	// Get the downloads userid
	$context['downloads_userid'] = $u;

    $dbresult = $db->query('', "
    SELECT
    	real_name
    FROM {db_prefix}members
    WHERE id_member = $u LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$context['downloads_userdownloads_name'] = $row['real_name'];
	$db->free_result($dbresult);

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $context['downloads_userdownloads_name'];

	$context['sub_template']  = 'myfiles';


	// Get userid
	$userid = $context['downloads_userid'];

	$context['start'] = (int) $_REQUEST['start'];

	// Get Total Pages
	$extra_page = '';
	if ($user_info['id'] == $userid)
		$extra_page = '';
	else
		$extra_page = ' AND p.approved = 1';

	$dbresult = $db->query('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}down_file as p
	WHERE p.id_member = $userid " . $extra_page);
	$row = $db->fetch_assoc($dbresult);
	$total = $row['total'];
	$db->free_result($dbresult);
	$context['downloads_total'] = $total;


	// Check if it is the user ids downloads mainly to show unapproved downloads or not
	if ($user_info['id'] == $userid)
    	$dbresult = $db->query('', "
    	SELECT
    		p.ID_FILE, p.commenttotal, p.filesize, p.approved, p.views, p.id_member,
    		 m.real_name, p.date, p.totaldownloads, p.rating, p.totalratings, p.title
    	FROM {db_prefix}down_file as p, {db_prefix}members AS m
    	WHERE p.id_member = $userid AND p.id_member = m.id_member
    	ORDER BY p.ID_FILE DESC LIMIT $context[start]," . $modSettings['down_set_files_per_page']);
	else
    	$dbresult = $db->query('', "
    	SELECT
    		p.ID_FILE, p.commenttotal, p.filesize, p.approved, p.views,
    		p.id_member, m.real_name, p.date, p.totaldownloads, p.rating, p.totalratings, p.title
    	FROM {db_prefix}down_file as p, {db_prefix}members AS m
    	WHERE p.id_member = $userid AND p.id_member = m.id_member AND p.approved = 1
    	ORDER BY p.ID_FILE DESC LIMIT $context[start]," . $modSettings['down_set_files_per_page']);

    	$context['downloads_files'] = array();
		while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_files'][] = $row;

		}
		$db->free_result($dbresult);

		$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;sa=myfiles;u=' . $context['downloads_userid'], $_REQUEST['start'], $total, $modSettings['down_set_files_per_page']);

}


function Downloads_CatUp()
{
	global $txt;
	// Check if they are allowed to manage cats
	isAllowedTo('downloads_manage');

	// Get the category id
	$cat = (int) $_REQUEST['cat'];

	Downloads_ReOrderCats($cat);

	$db = database();

	// Check if there is a category above it
	// First get our row order
	$dbresult1 = $db->query('', "
	SELECT
		roworder,ID_PARENT
	FROM {db_prefix}down_cat
	WHERE ID_CAT = $cat");
	$row = $db->fetch_assoc($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o--;

	$db->free_result($dbresult1);
	$dbresult = $db->query('', "
	SELECT
		ID_CAT, roworder
	FROM {db_prefix}down_cat
	WHERE ID_PARENT = $ID_PARENT AND roworder = $o");
	if ($db->affected_rows() == 0)
		fatal_error($txt['downloads_error_nocat_above'],false);
	$row2 = $db->fetch_assoc($dbresult);


	// Swap the order Id's
	$db->query('', "UPDATE {db_prefix}down_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT']);

	$db->query('', "UPDATE {db_prefix}down_cat
		SET roworder = $o WHERE ID_CAT = $cat");


	$db->free_result($dbresult);

	// Redirect to index to view cats
	redirectexit('action=downloads;cat=' . $ID_PARENT);
}

function Downloads_CatDown()
{
	global $txt;

	// Check if they are allowed to manage cats
	isAllowedTo('downloads_manage');

	// Get the cat id
	$cat = (int) $_REQUEST['cat'];

	Downloads_ReOrderCats($cat);

	$db = database();
	// Check if there is a category below it
	// First get our row order
	$dbresult1 = $db->query('', "
	SELECT
		ID_PARENT, roworder
	FROM {db_prefix}down_cat
	WHERE ID_CAT = $cat LIMIT 1");
	$row = $db->fetch_assoc($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;

	$db->free_result($dbresult1);
	$dbresult = $db->query('', "
	SELECT
		ID_CAT, roworder
	FROM {db_prefix}down_cat
	WHERE ID_PARENT = $ID_PARENT AND roworder = $o");
	if ($db->affected_rows()== 0)
		fatal_error($txt['downloads_error_nocat_below'],false);
	$row2 = $db->fetch_assoc($dbresult);


	// Swap the order Id's
	$db->query('', "UPDATE {db_prefix}down_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT']);

	$db->query('', "UPDATE {db_prefix}down_cat
		SET roworder = $o WHERE ID_CAT = $cat");


	$db->free_result($dbresult);


	// Redirect to index to view cats
	redirectexit('action=downloads;cat=' . $ID_PARENT);
}




function Downloads_CatPerm()
{
	global $mbname, $txt, $context;
	isAllowedTo('downloads_manage');

	$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['downloads_error_no_cat']);

	$db = database();

	$dbresult1 = $db->query('', "
	SELECT
		ID_CAT, title
	FROM {db_prefix}down_cat
	WHERE ID_CAT = $cat LIMIT 1");
	$row1 = $db->fetch_assoc($dbresult1);
	$context['downloads_cat_name'] = $row1['title'];
	$db->free_result($dbresult1);

	loadLanguage('Admin');

	$context['downloads_cat'] = $cat;

	// Load the template
	$context['sub_template']  = 'catperm';
	// Set the page title
	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_catperm'] . ' -' . $context['downloads_cat_name'];

	// Load the membergroups
	$dbresult = $db->query('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 ORDER BY group_name");
	while ($row = $db->fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = $row;
	}
	$db->free_result($dbresult);


	// Membergroups
	$dbresult = $db->query('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,  c.ID_GROUP, m.group_name,a.title catname
	FROM ({db_prefix}down_catperm as c, {db_prefix}membergroups AS m,{db_prefix}down_cat as a)
	WHERE  c.ID_CAT = " . $context['downloads_cat'] . " AND c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT");
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
	WHERE c.ID_CAT = " . $context['downloads_cat'] . " AND c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['downloads_reggroup'] = array();
	while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_reggroup'][] = $row;

		}
	$db->free_result($dbresult);


	$dbresult = $db->query('', "
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,  c.ID_GROUP,a.title catname
	FROM {db_prefix}down_catperm as c,{db_prefix}down_cat as a
	WHERE c.ID_CAT = " . $context['downloads_cat'] . " AND c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['downloads_guestgroup'] = array();
	while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_guestgroup'][] = $row;

		}
	$db->free_result($dbresult);

}

function Downloads_CatPerm2()
{
	global $txt;
	isAllowedTo('downloads_manage');

	$db = database();

	$groupname = (int) $_REQUEST['groupname'];
	$cat = (int) $_REQUEST['cat'];

	// Check if permission exits
	$dbresult = $db->query('', "
	SELECT
		ID_GROUP,ID_CAT
	FROM {db_prefix}down_catperm
	WHERE ID_GROUP = $groupname AND ID_CAT = $cat");
	if ($db->affected_rows()!= 0)
	{
		$db->free_result($dbresult);
		fatal_error($txt['downloads_permerr_permexist'],false);
	}
	$db->free_result($dbresult);

	// Permissions
	$view = isset($_REQUEST['view']) ? 1 : 0;
	$add = isset($_REQUEST['add']) ? 1 : 0;
	$edit = isset($_REQUEST['edit']) ? 1 : 0;
	$delete = isset($_REQUEST['delete']) ? 1 : 0;
	$addcomment = isset($_REQUEST['addcomment']) ? 1 : 0;

	// Insert into database
	$db->query('', "INSERT INTO {db_prefix}down_catperm
			(ID_GROUP,ID_CAT,view,addfile,editfile,delfile,addcomment)
		VALUES ($groupname,$cat,$view,$add,$edit,$delete,$addcomment)");

	redirectexit('action=downloads;sa=catperm;cat=' . $cat);
}



function Downloads_AddCategory()
{
	global $context, $txt, $modSettings;

	isAllowedTo('downloads_manage');



	$db = database();

	// Show the boards where the user can select to post in.
	$context['downloads_boards'] = array('');
	$request = $db->query('', "
	SELECT
		b.ID_BOARD, b.name AS bName, c.name AS cName
	FROM {db_prefix}boards AS b, {db_prefix}categories AS c
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	while ($row = $db->fetch_assoc($request))
		$context['downloads_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	$db->free_result($request);

	 $dbresult = $db->query('', "
	 SELECT
	 	c.ID_CAT, c.title,c.roworder
	 FROM {db_prefix}down_cat AS c
	 ORDER BY c.roworder ASC");
	$context['downloads_cat'] = array();
	 while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'roworder' => $row['roworder'],
			);
		}
	$db->free_result($dbresult);

	if (isset($_REQUEST['cat']))
		$parent  = (int) $_REQUEST['cat'];
	else
		$parent = 0;

	$context['cat_parent'] = $parent;


	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_addcategory'];

	$context['sub_template']  = 'add_category';

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

}

function Downloads_AddCategory2()
{
	global $txt, $sourcedir, $modSettings;

	isAllowedTo('downloads_manage');

	$db = database();

	// Get the category information and clean the input for bad stuff
	$title = Util::htmlspecialchars($_REQUEST['title'],ENT_QUOTES);
	$description = Util::htmlspecialchars($_REQUEST['description'],ENT_QUOTES);
	$image =  htmlspecialchars($_REQUEST['image'],ENT_QUOTES);
	$boardselect = (int) $_REQUEST['boardselect'];
	$parent = (int) $_REQUEST['parent'];


	$locktopic = isset($_REQUEST['locktopic']) ? 1 : 0;
	$disablerating  = isset($_REQUEST['disablerating']) ? 1 : 0;


	// Title is required for a category
	if (empty($title))
		fatal_error($txt['downloads_error_cat_title'],false);


		$sortby = '';
		$orderby = '';
		if (isset($_REQUEST['sortby']))
		{
			switch ($_REQUEST['sortby'])
			{
				case 'date':
					$sortby = 'p.ID_FILE';

				break;
				case 'title':
					$sortby = 'p.title';
				break;

				case 'mostview':
					$sortby = 'p.views';
				break;

				case 'mostcom':
					$sortby = 'p.commenttotal';
				break;

				case 'mostrated':
					$sortby = 'p.totalratings';
				break;

				case 'mostdowns':
					$sortby = 'p.totaldownloads';
				break;
				case 'filesize':
					$sortby = 'p.filesize';
				break;
				case 'membername':
					$sortby = 'm.real_name';
				break;


				default:
					$sortby = 'p.ID_FILE';
				break;
			}

		}
		else
		{
			$sortby = 'p.ID_FILE';
		}


		if (isset($_REQUEST['orderby']))
		{
			switch ($_REQUEST['orderby'])
			{
				case 'asc':
					$orderby = 'ASC';

				break;
				case 'desc':
					$orderby = 'DESC';
				break;

				default:
					$orderby = 'DESC';
				break;
			}
		}
		else
		{
			$orderby = 'DESC';
		}

	// Do the order
	$dbresult = $db->query('', "
	SELECT
		MAX(roworder) as cat_order
	FROM {db_prefix}down_cat
	WHERE ID_PARENT = $parent
	ORDER BY roworder DESC");
	$row = $db->fetch_assoc($dbresult);

	if ($db->affected_rows() == 0)
		$order = 0;
	else
		$order = $row['cat_order'];
	$order++;

	// Insert the category
	$db->query('', "INSERT INTO {db_prefix}down_cat
			(title, description,roworder,image,ID_BOARD,ID_PARENT,disablerating,locktopic,sortby,orderby)
		VALUES ('$title', '$description',$order,'$image',$boardselect,$parent,$disablerating,$locktopic,'$sortby','$orderby')");
	$db->free_result($dbresult);

	// Get the Category ID
	$cat_id = $db->insert_id('{db_prefix}down_cat', 'id_cat');


	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);

	// Upload Category image File
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{

		$sizes = @getimagesize($_FILES['picture']['tmp_name']);

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				fatal_error($txt['downloads_error_invalid_picture'],false);

			require_once(SUBSDIR . '/Graphics.subs.php');

			if ((!empty($modSettings['down_set_cat_width']) && $sizes[0] > $modSettings['down_set_cat_width']) || (!empty($modSettings['down_set_cat_height']) && $sizes[1] > $modSettings['down_set_cat_height']))
			{

					// Delete the temp file
					@unlink($_FILES['picture']['tmp_name']);
					fatal_error($txt['downloads_error_img_size_height'] . $sizes[1] . $txt['downloads_error_img_size_width'] . $sizes[0],false);

			}

		// Move the file
		$extensions = array(
					1 => 'gif',
					2 => 'jpeg',
					3 => 'png',
					5 => 'psd',
					6 => 'bmp',
					7 => 'tiff',
					8 => 'tiff',
					9 => 'jpeg',
					14 => 'iff',
					);
		$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';


		$filename = $cat_id . '.' . $extension;

		move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['down_path'] . 'catimgs/' . $filename);
		@chmod($modSettings['down_path'] . 'catimgs/' . $filename, 0644);

		// Update the filename for the category
		$db->query('', "UPDATE {db_prefix}down_cat
		SET filename = '$filename' WHERE ID_CAT = $cat_id LIMIT 1");


	}


	// Redirect to the category listing
	redirectexit('action=admin;area=downloads;sa=admincat');
}

function Downloads_ViewC()
{
	die(base64_decode('RG93bmxvYWRzIFN5c3RlbSBieSB2YmdhbWVyNDUgaHR0cDovL3d3dy5lbGthcnRlbW9kcy5jb20='));
}

function Downloads_EditCategory()
{
	global $context, $txt, $modSettings;
	isAllowedTo('downloads_manage');



	$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['downloads_error_no_cat']);

	$db = database();

	$context['downloads_boards'] = array('');
	$request = $db->query('', "
	SELECT
		b.ID_BOARD, b.name AS bName, c.name AS cName
	FROM {db_prefix}boards AS b, {db_prefix}categories AS c
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	while ($row = $db->fetch_assoc($request))
		$context['downloads_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	$db->free_result($request);

	$dbresult = $db->query('', "
	SELECT
		ID_CAT, title,roworder
	FROM {db_prefix}down_cat
	ORDER BY roworder ASC");
	$context['downloads_cat'] = array();
	 while($row = $db->fetch_assoc($dbresult))
		{
			$context['downloads_cat'][] = $row;
		}
	$db->free_result($dbresult);

	$dbresult = $db->query('', "
	SELECT
		ID_CAT, title, image, filename, description,ID_BOARD,
		ID_PARENT,disablerating, redirect, showpostlink, locktopic, sortby, orderby
	FROM {db_prefix}down_cat
	WHERE ID_CAT = $cat LIMIT 1");

	$row = $db->fetch_assoc($dbresult);
			$context['down_catinfo'] = $row;
	$db->free_result($dbresult);


	// Get all the custom fields
	$dbresult = $db->query('', "
	SELECT
		title, defaultvalue, is_required, ID_CUSTOM
	FROM  {db_prefix}down_custom_field
	WHERE ID_CAT = " . $context['down_catinfo']['ID_CAT'] . "
	ORDER BY roworder desc");
	$context['down_custom'] = array();
	while($row = $db->fetch_assoc($dbresult))
	{
			$context['down_custom'][] = $row;
	}
	$db->free_result($dbresult);


	$context['catid'] = $cat;

	// Set the page title
	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_editcategory'];
	// Load the edit category subtemplate
	$context['sub_template']  = 'edit_category';

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

}

function Downloads_EditCategory2()
{
	global $txt, $modSettings;

	isAllowedTo('downloads_manage');

	$db = database();

	// Clean the input
	$title = Util::htmlspecialchars($_REQUEST['title'], ENT_QUOTES);
	$description = Util::htmlspecialchars($_REQUEST['description'], ENT_QUOTES);
	$catid = (int) $_REQUEST['catid'];
	$image = htmlspecialchars($_REQUEST['image'], ENT_QUOTES);
	$parent = (int) $_REQUEST['parent'];

	$boardselect = (int) $_REQUEST['boardselect'];

	$locktopic = isset($_REQUEST['locktopic']) ? 1 : 0;
	$disablerating  = isset($_REQUEST['disablerating']) ? 1 : 0;


	// The category field requires a title
	if (empty($title))
		fatal_error($txt['downloads_error_cat_title'],false);

		$sortby = '';
		$orderby = '';
		if (isset($_REQUEST['sortby']))
		{
			switch ($_REQUEST['sortby'])
			{
				case 'date':
					$sortby = 'p.ID_FILE';

				break;
				case 'title':
					$sortby = 'p.title';
				break;

				case 'mostview':
					$sortby = 'p.views';
				break;

				case 'mostcom':
					$sortby = 'p.commenttotal';
				break;

				case 'mostrated':
					$sortby = 'p.totalratings';
				break;

				case 'mostdowns':
					$sortby = 'p.totaldownloads';
				break;
				case 'filesize':
					$sortby = 'p.filesize';
				break;
				case 'membername':
					$sortby = 'm.real_name';
				break;

				default:
					$sortby = 'p.ID_FILE';
				break;
			}

		}
		else
		{
			$sortby = 'p.ID_FILE';
		}


		if (isset($_REQUEST['orderby']))
		{
			switch ($_REQUEST['orderby'])
			{
				case 'asc':
					$orderby = 'ASC';

				break;
				case 'desc':
					$orderby = 'DESC';
				break;

				default:
					$orderby = 'DESC';
				break;
			}
		}
		else
		{
			$orderby = 'DESC';
		}

	// Update the category
	$db->query('', "UPDATE {db_prefix}down_cat
		SET title = '$title', image = '$image', description = '$description', ID_BOARD = $boardselect,
		ID_PARENT = $parent, disablerating = $disablerating, locktopic = $locktopic,
		orderby = '$orderby', sortby = '$sortby'
		WHERE ID_CAT = $catid LIMIT 1");


	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);

	// Upload Category image File
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{
		$sizes = @getimagesize($_FILES['picture']['tmp_name']);

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				fatal_error($txt['downloads_error_invalid_picture'],false);

			require_once(SUBSDIR . '/Graphics.subs.php');

			if ((!empty($modSettings['down_set_cat_width']) && $sizes[0] > $modSettings['down_set_cat_width']) || (!empty($modSettings['down_set_cat_height']) && $sizes[1] > $modSettings['down_set_cat_height']))
			{

				// Delete the temp file
				@unlink($_FILES['picture']['tmp_name']);
				fatal_error($txt['downloads_error_img_size_height'] . $sizes[1] . $txt['downloads_error_img_size_width'] . $sizes[0],false);

			}
		// Move the file
		$extensions = array(
					1 => 'gif',
					2 => 'jpeg',
					3 => 'png',
					5 => 'psd',
					6 => 'bmp',
					7 => 'tiff',
					8 => 'tiff',
					9 => 'jpeg',
					14 => 'iff',
					);
		$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';


		$filename = $catid . '.' . $extension;

		move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['down_path'] . 'catimgs/' . $filename);
		@chmod($modSettings['down_path'] . 'catimgs/' . $filename, 0644);


		// Update the filename for the category
		$db->query('', "UPDATE {db_prefix}down_cat
		SET filename = '$filename' WHERE ID_CAT = $catid LIMIT 1");


	}


	redirectexit('action=admin;area=downloads;sa=admincat');

}

function Downloads_DeleteCategory()
{
	global $context, $txt;

	isAllowedTo('downloads_manage');

	$db = database();



	$catid = (int) $_REQUEST['cat'];

	if (empty($catid))
		fatal_error($txt['downloads_error_no_cat']);

	$context['catid'] = $catid;

	// Lookup the category to get its name
	$dbresult = $db->query('', "
	SELECT
		ID_CAT, title
	FROM {db_prefix}down_cat
	WHERE ID_CAT = $catid");
	$row = $db->fetch_assoc($dbresult);
	$context['cat_title'] = $row['title'];
	$db->free_result($dbresult);

	// Get total files in the category
	$dbresult2 = $db->query('', "
	SELECT
		COUNT(*) as totalfiles
	FROM {db_prefix}down_file
	WHERE ID_CAT = $catid AND approved = 1");
	$row2 = $db->fetch_assoc($dbresult2);
	$context['totalfiles'] = $row2['totalfiles'];
	$db->free_result($dbresult2);

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_delcategory'];

	$context['sub_template']  = 'delete_category';
}

function Downloads_DeleteCategory2()
{
	global $modSettings;

	isAllowedTo('downloads_manage');

	$db = database();

	$catid = (int) $_REQUEST['catid'];
	// Increase the max time just in case it takes a long to delete the category and files.
	@ini_set('max_execution_time', '300');
	$dbresult = $db->query('', "
	SELECT
		ID_FILE, filename
	FROM {db_prefix}down_file
	WHERE ID_CAT = $catid");

	while($row = $db->fetch_assoc($dbresult))
	{
		// Delete Files
		// Delete the download
		@unlink($modSettings['down_path'] . $row['filename']);
		$db->query('', "DELETE FROM {db_prefix}down_comment WHERE ID_FILE  = " . $row['ID_FILE']);
		$db->query('', "DELETE FROM {db_prefix}down_rating WHERE ID_FILE  = " . $row['ID_FILE']);
		$db->query('', "DELETE FROM {db_prefix}down_report WHERE ID_FILE  = " . $row['ID_FILE']);
		$db->query('', "DELETE FROM {db_prefix}down_creport WHERE ID_FILE  = " . $row['ID_FILE']);
	}
	$db->free_result($dbresult);
	// Update Category parent
	$db->query('', "UPDATE {db_prefix}down_cat SET ID_PARENT = 0 WHERE ID_PARENT = $catid");

	// Delete All Files
	$db->query('', "DELETE FROM {db_prefix}down_file WHERE ID_CAT = $catid");

	// Finally delete the category
	$db->query('', "DELETE FROM {db_prefix}down_cat WHERE ID_CAT = $catid LIMIT 1");

	// Last Recount the totals
	$this->Downloads_RecountFileQuotaTotals(false);

	redirectexit('action=admin;area=downloads;sa=admincat');
}


function Downloads_DeleteComment()
{
	global $txt, $modSettings;

	is_not_guest();
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (isset($_REQUEST['ret']))
		$ret = $_REQUEST['ret'];

	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);

    $db = database();

	// Get the file ID for redirect
	$dbresult = $db->query('', "
	SELECT
		ID_FILE,ID_COMMENT, id_member
	FROM {db_prefix}down_comment
	WHERE ID_COMMENT = $id LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$fileid = $row['ID_FILE'];
	$memID = $row['id_member'];
	$db->free_result($dbresult);

	// Delete all the comment reports that comment
	$db->query('', "DELETE FROM {db_prefix}down_creport WHERE ID_COMMENT = $id");
	// Now delete the comment.
	$db->query('', "DELETE FROM {db_prefix}down_comment WHERE ID_COMMENT = $id LIMIT 1");


	// Update Comment total
	  $dbresult = $db->query('', "UPDATE {db_prefix}down_file
		SET commenttotal = commenttotal - 1 WHERE ID_FILE = $fileid LIMIT 1");


	// Redirect to the download
	if (empty($ret))
		redirectexit('action=downloads;sa=view;down=' . $fileid);
	else
		redirectexit('action=admin;area=downloads;sa=commentlist');

}

function Downloads_ReportComment()
{
	global $context, $txt;

	isAllowedTo('downloads_report');



	// Guest's can't report comments
	is_not_guest();


	$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);

	$context['downloads_comment_id'] = $id;

	$context['page_title'] = $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_reportcomment'];

	$context['sub_template']  = 'report_comment';
}

function Downloads_ReportComment2()
{
	global $txt, $user_info;

	isAllowedTo('downloads_report');

	$db = database();

	$comment = Util::htmlspecialchars($_REQUEST['comment'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);

	if (empty($comment))
		fatal_error($txt['downloads_error_no_comment'],false);

	$dbresult = $db->query('', "
	SELECT
		ID_FILE
	FROM {db_prefix}down_comment
	WHERE ID_COMMENT = $id LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$fileid = $row['ID_FILE'];
	$db->free_result($dbresult);


	$commentdate = time();

	$db->query('', "INSERT INTO {db_prefix}down_creport
			(id_member, comment, date, ID_COMMENT, ID_FILE)
		VALUES (" . $user_info['id'] . ",'$comment', $commentdate,$id,$fileid)");

	redirectexit('action=downloads;sa=view;down=' . $fileid);

}




function Downloads_PreviousDownload()
{
	global $txt;

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	$db = database();

	// Get the category
	$dbresult = $db->query('', "
	SELECT
		ID_FILE, ID_CAT
	FROM {db_prefix}down_file
	WHERE ID_FILE = $id  LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$ID_CAT = $row['ID_CAT'];

	$db->free_result($dbresult);

	// Get previous download
	$dbresult = $db->query('', "
	SELECT
		ID_FILE
	FROM {db_prefix}down_file
	WHERE ID_CAT = $ID_CAT AND approved = 1 AND ID_FILE < $id ORDER BY ID_FILE DESC LIMIT 1");
	if ($db->affected_rows() != 0)
	{
		$row = $db->fetch_assoc($dbresult);
		$ID_FILE = $row['ID_FILE'];
	}
	else
		$ID_FILE = $id;

	$db->free_result($dbresult);

	redirectexit('action=downloads;sa=view;down=' . $ID_FILE);
}

function Downloads_NextDownload()
{
	global $txt;

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	$db = database();

	// Get the category
	$dbresult = $db->query('', "
	SELECT
		ID_FILE, ID_CAT
	FROM {db_prefix}down_file
	WHERE ID_FILE = $id  LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$ID_CAT = $row['ID_CAT'];

	$db->free_result($dbresult);

	// Get next download
	$dbresult = $db->query('', "
	SELECT
		ID_FILE
	FROM {db_prefix}down_file
	WHERE ID_CAT = $ID_CAT AND approved = 1 AND ID_FILE > $id
	ORDER BY ID_FILE ASC LIMIT 1");
	if ($db->affected_rows() != 0)
	{
		$row = $db->fetch_assoc($dbresult);
		$ID_FILE = $row['ID_FILE'];
	}
	else
		$ID_FILE = $id;
	$db->free_result($dbresult);

	redirectexit('action=downloads;sa=view;down=' . $ID_FILE);
}

function Downloads_CatImageDelete()
{

	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		exit;

	$db = database();

		$db->query('', "UPDATE {db_prefix}down_cat
		SET filename = '' WHERE ID_CAT = $id LIMIT 1");

	redirectexit('action=downloads;sa=editcat;cat=' . $id);
}






function Downloads_CustomUp()
{
	global $txt;

	// Check Permission
	isAllowedTo('downloads_manage');
	// Get the id
	$id = (int) $_REQUEST['id'];

	Downloads_ReOrderCustom($id);

	$db = database();

	// Check if there is a category above it
	// First get our row order
	$dbresult1 = $db->query('', "
	SELECT
		ID_CAT, ID_CUSTOM, roworder
	FROM {db_prefix}down_custom_field
	WHERE ID_CUSTOM = $id");
	$row = $db->fetch_assoc($dbresult1);

	$ID_CAT = $row['ID_CAT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o--;

	$db->free_result($dbresult1);
	$dbresult = $db->query('', "
	SELECT
		ID_CUSTOM, roworder
	FROM {db_prefix}down_custom_field
	WHERE ID_CAT = $ID_CAT AND roworder = $o");

	if ($db->affected_rows()== 0)
		fatal_error($txt['downloads_error_nocustom_above'], false);
	$row2 = $db->fetch_assoc($dbresult);


	// Swap the order Id's
	$db->query('', "UPDATE {db_prefix}down_custom_field
		SET roworder = $oldrow WHERE ID_CUSTOM = " .$row2['ID_CUSTOM']);

	$db->query('', "UPDATE {db_prefix}down_custom_field
		SET roworder = $o WHERE ID_CUSTOM = $id");


	$db->free_result($dbresult);

	// Redirect to index to view cats
	redirectexit('action=downloads;sa=editcat;cat=' . $ID_CAT);

}

function Downloads_CustomDown()
{
	global $txt;

	isAllowedTo('downloads_manage');

	// Get the id
	$id = (int) $_REQUEST['id'];

	Downloads_ReOrderCustom($id);

	$db = database();

	// Check if there is a category below it
	// First get our row order
	$dbresult1 = $db->query('', "
	SELECT
		ID_CUSTOM,ID_CAT, roworder
	FROM {db_prefix}down_custom_field
	WHERE ID_CUSTOM = $id LIMIT 1");
	$row = $db->fetch_assoc($dbresult1);
	$ID_CAT = $row['ID_CAT'];

	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;

	$db->free_result($dbresult1);
	$dbresult = $db->query('', "
	SELECT
		ID_CUSTOM, ID_CAT, roworder
	FROM {db_prefix}down_custom_field
	WHERE ID_CAT = $ID_CAT AND roworder = $o");
	if ($db->affected_rows()== 0)
		fatal_error($txt['downloads_error_nocustom_below'], false);
	$row2 = $db->fetch_assoc($dbresult);

	// Swap the order Id's
	$db->query('', "UPDATE {db_prefix}down_custom_field
		SET roworder = $oldrow WHERE ID_CUSTOM = " .$row2['ID_CUSTOM']);

	$db->query('', "UPDATE {db_prefix}down_custom_field
		SET roworder = $o WHERE ID_CUSTOM = $id");


	$db->free_result($dbresult);


	// Redirect to index to view cats
	redirectexit('action=downloads;sa=editcat;cat=' . $ID_CAT);

}

function Downloads_CustomAdd()
{
	global $txt;

	// Check Permission
	isAllowedTo('downloads_manage');

	$db = database();

	$id = (int) $_REQUEST['id'];

	$title = Util::htmlspecialchars($_REQUEST['title'],ENT_QUOTES);
	$defaultvalue = Util::htmlspecialchars($_REQUEST['defaultvalue'],ENT_QUOTES);
	$required = isset($_REQUEST['required']) ? 1 : 0;


	if ($title == '')
		fatal_error($txt['downloads_custom_err_title'], false);


	$db->query('', "INSERT INTO {db_prefix}down_custom_field
			(ID_CAT,title, defaultvalue, is_required)
		VALUES ($id,'$title','$defaultvalue', '$required')");


	// Redirect back to the edit category page
	redirectexit('action=downloads;sa=editcat;cat=' . $id);

}

function Downloads_CustomDelete()
{


	// Check Permission
	isAllowedTo('downloads_manage');

	$db = database();

	// Custom ID
	$id = (int) $_REQUEST['id'];

	// Get the CAT ID to redirect to the page
	$result = $db->query('', "
	SELECT
		ID_CAT
	FROM {db_prefix}down_custom_field
	WHERE ID_CUSTOM =  $id LIMIT 1");
	$row = $db->fetch_assoc($result);
	$db->free_result($result);


	// Delete all custom data for downloads that use it
	$db->query('', "DELETE FROM {db_prefix}down_custom_field_data
	WHERE ID_CUSTOM = $id ");

	// Finaly delete the field
	$db->query('', "DELETE FROM {db_prefix}down_custom_field
	WHERE ID_CUSTOM = $id LIMIT 1");

	// Redirect to the edit category page
	redirectexit('action=downloads;sa=editcat;cat=' . $row['ID_CAT']);

}



function Downloads_DownloadFile()
{
	global $modSettings, $txt, $context, $user_info;

	// Check Permission
	isAllowedTo('downloads_view');

	$db = database();


	if (isset($_REQUEST['down']))
		$id = (int) $_REQUEST['down'];
	else
		$id = (int) $_REQUEST['id'];

	// Get the download information
	$dbresult = $db->query('', "
	SELECT
		f.filename, f.fileurl, f.orginalfilename, f.approved, f.credits, f.ID_CAT, f.id_member, f.id_file
	FROM {db_prefix}down_file as f
	WHERE f.ID_FILE = $id");
	$row = $db->fetch_assoc($dbresult);
	$db->free_result($dbresult);
	if (empty($row['id_file']))
		fatal_error($txt['downloads_error_no_file_selected'],false);


	// Check if File is approved
	if ($row['approved'] == 0 && $user_info['id'] != $row['id_member'])
	{
		if (!allowedTo('downloads_manage'))
			fatal_error($txt['downloads_error_file_notapproved'],false);
	}

	// Check if they can download from this category
	Downloads_GetCatPermission($row['ID_CAT'],'view');

	// Check credits

	// End Credit check

	// Download File or Redirect to the download location
	if ($row['fileurl'] != '')
	{
		$lastdownload = time();
		// Update download count
		$dbresult = $db->query('', "
		UPDATE {db_prefix}down_file
			SET totaldownloads = totaldownloads + 1, lastdownload  = '$lastdownload'
		WHERE ID_FILE = $id LIMIT 1");

		// Redirect to the download
		header("Location: " . $row['fileurl']);

		exit;
	}
	else
	{
		$lastdownload = time();
		// Update download count
		$dbresult = $db->query('', "
		UPDATE {db_prefix}down_file
			SET totaldownloads = totaldownloads + 1, lastdownload  = '$lastdownload'
		WHERE ID_FILE = $id LIMIT 1");


		$real_filename = $row['orginalfilename'];
		$filename = $modSettings['down_path'] . $row['filename'];

		ob_end_clean();
		if (!empty($modSettings['enableCompressedOutput']) && @filesize($filename) <= 4194304)
			@ob_start('ob_gzhandler');
		else
		{
			ob_start();
			header('Content-Encoding: none');
		}

		// No point in a nicer message, because this is supposed to be an attachment anyway...
		if (!file_exists($filename))
		{
			loadLanguage('Errors');

			header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
			header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

			// We need to die like this *before* we send any anti-caching headers as below.
			die('404 - ' . $txt['attachment_not_found']);
		}





		// Check whether the ETag was sent back, and cache based on that...
		$file_md5 = '"' . md5_file($filename) . '"';


		// Send the attachment headers.
		header('Pragma: ');

		if (!$context['browser']['is_gecko'])
			header('Content-Transfer-Encoding: binary');

		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
		header('Accept-Ranges: bytes');
		header('Set-Cookie:');
		header('Connection: close');
		header('ETag: ' . $file_md5);

		if (filesize($filename) != 0)
		{
			$size = @getimagesize($filename);
			if (!empty($size))
			{
				// What headers are valid?
				$validTypes = array(
					1 => 'gif',
					2 => 'jpeg',
					3 => 'png',
					5 => 'psd',
					6 => 'bmp',
					7 => 'tiff',
					8 => 'tiff',
					9 => 'jpeg',
					14 => 'iff',
				);

				// Do we have a mime type we can simpy use?
				if (!empty($size['mime']))
					header('Content-Type: ' . $size['mime']);
				elseif (isset($validTypes[$size[2]]))
					header('Content-Type: image/' . $validTypes[$size[2]]);
				// Otherwise - let's think safety first... it might not be an image...
				elseif (isset($_REQUEST['image']))
					unset($_REQUEST['image']);
			}
			// Once again - safe!
			elseif (isset($_REQUEST['image']))
				unset($_REQUEST['image']);
		}

		if (!isset($_REQUEST['image']))
		{
			header('Content-Disposition: attachment; filename="' . $real_filename . '"');
			header('Content-Type: application/octet-stream');
		}

		if (empty($modSettings['enableCompressedOutput']) || filesize($filename) > 4194304)
			header('Content-Length: ' . filesize($filename));

		// Try to buy some time...
		@set_time_limit(0);

		// For text files.....
		if (!isset($_REQUEST['image']) && in_array(substr($real_filename, -4), array('.txt', '.css', '.htm', '.php', '.xml')))
		{
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false)
				$callback = function($buffer){return preg_replace('~[\r]?\n~', "\r\n", $buffer);};
			elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false)
				$callback = function($buffer){return preg_replace('~[\r]?\n~', "\r", $buffer);};
			else
				$callback = function($buffer){return preg_replace('~[\r]?\n~', "\n", $buffer);};
		}

		// Since we don't do output compression for files this large...
		if (filesize($filename) > 4194304)
		{
			// Forcibly end any output buffering going on.
			if (function_exists('ob_get_level'))
			{
				while (@ob_get_level() > 0)
					@ob_end_clean();
			}
			else
			{
				@ob_end_clean();
				@ob_end_clean();
				@ob_end_clean();
			}

			$fp = fopen($filename, 'rb');
			while (!feof($fp))
			{
				if (isset($callback))
					echo $callback(fread($fp, 8192));
				else
					echo fread($fp, 8192);
				flush();
			}
			fclose($fp);
		}
		// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
		elseif (isset($callback) || @readfile($filename) == null)
			echo isset($callback) ? $callback(file_get_contents($filename)) : file_get_contents($filename);

		obExit(false);

		exit;
	}


}

function Downloads_getRemoteFilesize($file_url)
{
	$file_url = trim($file_url);

	if (empty($file_url))
		return 0;

	 if (ini_get('allow_url_fopen') == 1)
	 {

		$head = array();
		$head['content-length'] = 0;


		try
		{
			$head = array_change_key_case(get_headers($file_url, 1));
		}
		 catch (Exception $e)
		{

		}

		$result = isset($head['content-length']) ? $head['content-length'] : 0;

		if (is_array($result))
			return 0;

		return $result;
	}
	 else
	 {
	 	return 0;

	 }
}

}


?>