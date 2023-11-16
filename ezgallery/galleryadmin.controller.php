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

if (!defined('ELK'))
	die('No access...');

class ezGalleryAdmin_Controller extends Action_Controller
{

	public function action_index()
	{
	    global $modSettings, $boardurl, $currentVersion;
        $currentVersion = '6.0';

        // Load the main template file
        loadtemplate('Gallery');

        // Load the language files
        if (loadlanguage('Gallery') == false)
            loadLanguage('Gallery','english');

        require_once(SUBSDIR . '/gallery.subs.php');

        // Setup Gallery Path and Url
        if (empty($modSettings['gallery_url']))
            $modSettings['gallery_url'] = $boardurl . '/gallery/';

        if (empty($modSettings['gallery_path']))
            $modSettings['gallery_path'] = BOARDDIR . '/gallery/';

        if (empty($modSettings['gallery_set_images_per_page']))
            $modSettings['gallery_set_images_per_page'] = 20;

        if (empty($modSettings['gallery_thumb_height']))
            $modSettings['gallery_thumb_height'] = 78;

        if (empty($modSettings['gallery_thumb_width']))
            $modSettings['gallery_thumb_width'] = 120;


	// Gallery Actions
	$subActions = array(
		'admincat' => 'AdminCats',
		'settings'=> 'AdminSettings',
		'settings2'=> 'AdminSettings2',
		'deletereport' => 'DeleteReport',
		'reportlist' => 'ReportList',
		'delcomment' => 'DeleteComment',
		'addcat' => 'AddCategory',
		'addcat2' => 'AddCategory2',
		'editcat' => 'EditCategory',
		'editcat2' => 'EditCategory2',
		'deletecat' => 'DeleteCategory',
		'deletecat2' => 'DeleteCategory2',
		'viewc' => 'ViewC',
		'approvelist' => 'ApproveList',
		'approve' => 'ApprovePicture',
		'unapprove' => 'UnApprovePicture',
		'regen' => 'ReGenerateThumbnails',
		'regen2' => 'ReGenerateThumbnails2',
        'copyright' => 'Gallery_CopyrightRemoval',

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
		return $this->AdminSettings();


	}
	
		
function AdminSettings()
{
	global $context, $txt;
	isAllowedTo('ezgallery_manage');

	DoGalleryAdminTabs();

	$context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_settings'];

	$context['sub_template']  = 'settings';

}

function AdminSettings2()
{

	isAllowedTo('ezgallery_manage');

	// Get the settings
	$gallery_max_height = (int) $_REQUEST['gallery_max_height'];
	$gallery_max_width =  (int) $_REQUEST['gallery_max_width'];
	$gallery_max_filesize =  (int) $_REQUEST['gallery_max_filesize'];
	$gallery_commentchoice =  isset($_REQUEST['gallery_commentchoice']) ? 1 : 0;
	$gallery_set_images_per_page = (int) $_REQUEST['gallery_set_images_per_page'];
	$gallery_set_images_per_row = (int) $_REQUEST['gallery_set_images_per_row'];
	$gallery_thumb_width = (int) $_REQUEST['gallery_thumb_width'];
	$gallery_thumb_height = (int) $_REQUEST['gallery_thumb_height'];

	$gallery_path = $_REQUEST['gallery_path'];
	$gallery_url = $_REQUEST['gallery_url'];
	$gallery_who_viewing = isset($_REQUEST['gallery_who_viewing']) ? 1 : 0;

	// Image Linking codes
	$gallery_set_showcode_bbc_image = isset($_REQUEST['gallery_set_showcode_bbc_image']) ? 1 : 0;
	$gallery_set_showcode_directlink = isset($_REQUEST['gallery_set_showcode_directlink']) ? 1 : 0;
	$gallery_set_showcode_htmllink = isset($_REQUEST['gallery_set_showcode_htmllink']) ? 1 : 0;


	updateSettings(
	array(
	'gallery_max_height' => $gallery_max_height,
	'gallery_max_width' => $gallery_max_width,
	'gallery_max_filesize' => $gallery_max_filesize,
	'gallery_path' => $gallery_path,
	'gallery_url' => $gallery_url,
	'gallery_commentchoice' => $gallery_commentchoice,
	'gallery_who_viewing' => $gallery_who_viewing,
	'gallery_set_images_per_page' => $gallery_set_images_per_page,
	'gallery_set_images_per_row' => $gallery_set_images_per_row,
	'gallery_thumb_width' => $gallery_thumb_width,
	'gallery_thumb_height' => $gallery_thumb_height,

	'gallery_set_showcode_bbc_image' => $gallery_set_showcode_bbc_image,
	'gallery_set_showcode_directlink' => $gallery_set_showcode_directlink,
	'gallery_set_showcode_htmllink' => $gallery_set_showcode_htmllink,

	));

	redirectexit('action=admin;area=gallery;sa=settings');

}

function AdminCats()
{
	global $context, $txt;
	isAllowedTo('ezgallery_manage');

	$db = database();

	$context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_managecats'];


	DoGalleryAdminTabs();

	$context['sub_template']  = 'manage_cats';

	$dbresult = $db->query('', "
		SELECT
			id_cat, title, roworder, description, image
		FROM {db_prefix}gallery_cat ORDER BY roworder ASC");
	$context['gallery_cat_list'] = array();
	while($row = $db->fetch_assoc($dbresult))
		{
			$context['gallery_cat_list'][] = $row;
		}
		$db->free_result($dbresult);
}



function Gallery_CopyrightRemoval()
{
    global $context, $txt;
	isAllowedTo('ezgallery_manage');

    if (isset($_REQUEST['save']))
    {

        $gallery_copyrightkey = addslashes($_REQUEST['gallery_copyrightkey']);

        updateSettings(
    	array(
    	'gallery_copyrightkey' => $gallery_copyrightkey,
    	)

    	);
    }


	DoGalleryAdminTabs();

	$context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_txt_copyrightremoval'];

	$context['sub_template']  = 'gallerycopyright';


}


function ApproveList()
{
	global $context, $txt;

	isAllowedTo('ezgallery_manage');

	$db = database();

	DoGalleryAdminTabs();

	$context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_approveimages'];
	$context['sub_template']  = 'approvelist';


	$dbresult = $db->query('', "
		  	SELECT
		  		p.id_picture, p.thumbfilename, p.title, p.id_member, m.member_name, m.real_name, p.date, p.description,
		  		p.filename, p.height, p.width
		  	FROM {db_prefix}gallery_pic as p
		  	LEFT JOIN {db_prefix}members AS m  on (p.id_member = m.id_member)
		  	WHERE p.approved = 0 ORDER BY p.id_picture DESC");
	$context['gallery_approve_list'] = array();
	while($row = $db->fetch_assoc($dbresult))
		{
			$context['gallery_approve_list'][] = $row;
		}
		$db->free_result($dbresult);

}






function ReportList()
{
	global $context, $txt;

	isAllowedTo('ezgallery_manage');

	$db = database();

	$context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_reportimages'];


	DoGalleryAdminTabs();

	$context['sub_template']  = 'reportlist';



	$dbresult = $db->query('', "
		  	SELECT
		  		r.ID, r.id_picture, r.id_member, m.member_name, m.real_name, r.date,r.comment
		  	FROM {db_prefix}gallery_report as r
		  	LEFT JOIN {db_prefix}members AS m on (r.id_member = m.id_member) ORDER BY r.id_picture DESC");
	$context['gallery_report_list'] = array();
	while($row = $db->fetch_assoc($dbresult))
		{
			$context['gallery_report_list'][] = $row;
		}
		$db->free_result($dbresult);

}

function DeleteReport()
{
	global $txt;

	// Check the permission
	isAllowedTo('ezgallery_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_report_selected']);

	$db = database();
	$db->query('', "DELETE FROM {db_prefix}gallery_report WHERE ID = $id LIMIT 1");

	// Redirect to redirect list
	redirectexit('action=admin;area=gallery;sa=reportlist');
}


}