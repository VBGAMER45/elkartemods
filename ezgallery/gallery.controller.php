<?php
/*
ezGallery Lite Edition
Version 6.0
by:vbgamer45
https://www.elkartemods.com


############################################
License Information:
Links to http://www.elkartemods.com must remain unless
branding free option is purchased.
#############################################
*/

if (!defined('ELK'))
	die('No access...');

ini_set('gd.jpeg_ignore_warning', 1);

class Gallery_Controller extends Action_Controller
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

        GalleryUserTabs();


	// Gallery Actions
	$subActions = array(
		'view' => 'ViewPicture',
		'delete' => 'DeletePicture',
		'delete2' => 'DeletePicture2',
		'edit' => 'EditPicture',
		'edit2' => 'EditPicture2',
		'report' => 'ReportPicture',
		'report2' => 'ReportPicture2',
		'deletereport' => 'DeleteReport',
		'reportlist' => 'ReportList',
		'comment' => 'AddComment',
		'comment2' => 'AddComment2',
		'delcomment' => 'DeleteComment',
		'rate' => 'RatePicture',
		'catup' => 'CatUp',
		'catdown' => 'CatDown',
		'addcat' => 'AddCategory',
		'addcat2' => 'AddCategory2',
		'editcat' => 'EditCategory',
		'editcat2' => 'EditCategory2',
		'deletecat' => 'DeleteCategory',
		'deletecat2' => 'DeleteCategory2',
		'viewc' => 'ViewC',
		'myimages' => 'MyImages',
		'approvelist' => 'ApproveList',
		'approve' => 'ApprovePicture',
		'unapprove' => 'UnApprovePicture',
		'add' => 'AddPicture',
		'add2' => 'AddPicture2',
		'search' => 'Search',
		'search2' => 'Search2',
		'regen' => 'ReGenerateThumbnails',
		'regen2' => 'ReGenerateThumbnails2',
		'next' => 'NextImage',
		'prev' => 'PreviousImage',

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
		return $this->mainview();



	}



    function mainview()
    {
        global $context, $scripturl, $txt, $modSettings, $user_info;
        // View the main gallery

        // Is the user allowed to view the gallery?
        isAllowedTo('ezgallery_view');

        $db = database();

        // Load the main gallery template
        $context['sub_template']  = 'mainview';

        $context['gallery_cat_name'] = ' ';


        if (isset($_REQUEST['cat']))
            $cat = (int) $_REQUEST['cat'];
        else
            $cat = 0;


        if (!empty($cat))
        {
           $context['gallery_catid'] = $cat;

            // Get category name
            $dbresult1 = $db->query('', "
            SELECT
                id_cat, title, roworder, description, image
            FROM {db_prefix}gallery_cat
            WHERE id_cat = $cat LIMIT 1");

            $row1 = $db->fetch_assoc($dbresult1);
            $context['gallery_cat_name'] = $row1['title'];
            $db->free_result($dbresult1);

            // Link Tree
            $context['linktree'][] = array(
                        'url' =>  $scripturl . '?action=gallery;cat=' . $cat,
                        'name' => $context['gallery_cat_name']
                    );


            $context['page_title'] = $context['gallery_cat_name'];
            $context['sub_template']  = 'image_listing';

            if (!empty($modSettings['gallery_who_viewing']))
            {
                $context['can_moderate_forum'] = allowedTo('moderate_forum');

                    // Start out with no one at all viewing it.
                    $context['view_members'] = array();
                    $context['view_members_list'] = array();
                    $context['view_num_hidden'] = 0;

                    $whoID = (string) $cat;

                    // Search for members who have this picture id set in their GET data.
                    $request = $db->query('', "
                        SELECT
                            lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
                            mg.online_color, mg.id_group, mg.group_name
                        FROM {db_prefix}log_online AS lo
                            LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lo.id_member)
                            LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = IF(mem.id_group = 0, mem.id_post_group, mem.id_group))
                        WHERE INSTR(lo.url, 's:7:\"gallery\";s:3:\"cat\";s:" . strlen($whoID ) .":\"$cat\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'");
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
                            'group' => $row['id_group'],
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


            $context['start'] = (int) $_REQUEST['start'];

            $dbresult = $db->query('', "
            SELECT p.id_picture, p.commenttotal, p.filesize, p.views, p.thumbfilename, p.filename, p.height, p.width,
             p.title, p.id_member, m.member_name, m.real_name, p.date, p.description
             FROM {db_prefix}gallery_pic as p
            LEFT JOIN {db_prefix}members AS m on ( p.id_member = m.id_member)
            WHERE p.id_cat = $cat AND p.approved = 1 ORDER BY id_picture DESC LIMIT $context[start]," . $modSettings['gallery_set_images_per_page']);
            $context['gallery_image_list'] = array();
            while($row = $db->fetch_assoc($dbresult))
            {
                $context['gallery_image_list'][] = $row;
            }
            $db->free_result($dbresult);



            $context['gallery_image_count'] = $db->affected_rows();


        }
        else
        {
            $context['page_title'] = $txt['gallery_text_title'];

            // Category list
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

            // Get unapproved pictures
            $dbresult3 = $db->query('', "
                SELECT
                    COUNT(*) AS total
                FROM {db_prefix}gallery_pic
                WHERE approved = 0");
                $totalrow = $db->fetch_assoc($dbresult3);
                $totalpics = $totalrow['total'];
                $db->free_result($dbresult3);
            $context['gallery_unapproved_pics']	= $totalpics;

            // Get reported pictures
            $dbresult4 = $db->query('', "
                SELECT
                    COUNT(*) AS total
                FROM {db_prefix}gallery_report");
                $totalrow = $db->fetch_assoc($dbresult4);
                $totalreport = $totalrow['total'];
                $db->free_result($dbresult4);
            $context['gallery_reported_pics'] = $totalreport;

        }

    }

    function AddCategory()
    {
        global $context, $txt, $modSettings;

        isAllowedTo('ezgallery_manage');

        $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_addcategory'];

        $context['sub_template']  = 'add_category';

        $context['linktree'][] = array(
                'name' => '<em>' .  $txt['gallery_text_addcategory']. '</em>'
            );

        // Check if spellchecking is both enabled and actually working.
        $context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
        if ($context['show_spellchecking'])
            loadJavascriptFile('spellcheck.js', array('defer' => true));

        // Needed for the WYSIWYG editor.
        require_once(SUBSDIR . '/Editor.subs.php');

        // Now create the editor.
        $editorOptions = array(
                'id' => 'descript',
                'value' => '',
                'width' => '90%',
                'form' => 'catform',
                'labels' => array(
                    'post_button' => ''
                ),
            );


        create_control_richedit($editorOptions);
        $context['post_box_name'] = $editorOptions['id'];

    }

    function AddCategory2()
    {
        global $txt;

        isAllowedTo('ezgallery_manage');

        $db = database();



        $title = Util::htmlspecialchars($_REQUEST['title'], ENT_QUOTES);
        $description = Util::htmlspecialchars($_REQUEST['descript'], ENT_QUOTES);
        $image =  Util::htmlspecialchars($_REQUEST['image'], ENT_QUOTES);

        if (trim($title) == '')
            fatal_error($txt['gallery_error_cat_title'],false);

        // Do the order
        $dbresult = $db->query('', "
        SELECT
            roworder
        FROM {db_prefix}gallery_cat ORDER BY roworder DESC");
        $row = $db->fetch_assoc($dbresult);

        $order = $row['roworder'];
        $order++;

        // Insert the category
        $db->query('', "INSERT INTO {db_prefix}gallery_cat
                (title, description,roworder,image)
            VALUES ('$title', '$description',$order,'$image')");
        $db->free_result($dbresult);


         redirectexit('action=admin;area=gallery;sa=admincat');
    }

    function ViewC()
    {
        die(base64_decode('UG93ZXJlZCBieSBlekdhbGxlcnkgRm9yIG1hZGUgYnkgdmJnYW1lcjQ1IGh0dHA6Ly9odHRwOi8vd3d3LmVsa2FydGVtb2RzLmNvbS8='));
    }

    function EditCategory()
    {
        global $context, $txt, $modSettings;

        if (isset($_REQUEST['cat']))
           $cat = (int) $_REQUEST['cat'];
        else
            $cat = 0;

        if (empty($cat))
            fatal_error($txt['gallery_error_no_cat']);

        isAllowedTo('ezgallery_manage');

        $db = database();

        $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_editcategory'];

        $context['sub_template']  = 'edit_category';


        $context['linktree'][] = array(
                'name' => $txt['gallery_text_editcategory']
            );

        // Check if spellchecking is both enabled and actually working.
        $context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
        if ($context['show_spellchecking'])
            loadJavascriptFile('spellcheck.js', array('defer' => true));

        $dbresult = $db->query('', "
        SELECT
            id_cat, title, image, description
        FROM {db_prefix}gallery_cat
        WHERE ID_CAT = $cat LIMIT 1");
        $row = $db->fetch_assoc($dbresult);
        $context['gallery_cat_edit'] = $row;
        $db->free_result($dbresult);

        // Needed for the WYSIWYG editor.
       require_once(SUBSDIR . '/Editor.subs.php');

        // Now create the editor.
        $editorOptions = array(
                'id' => 'descript',
                'value' => $context['gallery_cat_edit']['description'],
                'width' => '90%',
                'form' => 'catform',
                'labels' => array(
                    'post_button' => ''
                ),
            );


            create_control_richedit($editorOptions);
            $context['post_box_name'] = $editorOptions['id'];

    }

    function EditCategory2()
    {
        global $txt;

        isAllowedTo('ezgallery_manage');

        $db = database();



        // Clean the input
        $title = Util::htmlspecialchars($_REQUEST['title'], ENT_QUOTES);
        $description = Util::htmlspecialchars($_REQUEST['descript'], ENT_QUOTES);
        $catid = (int) $_REQUEST['catid'];
        $image = Util::htmlspecialchars($_REQUEST['image'], ENT_QUOTES);

        if (trim($title) == '')
            fatal_error($txt['gallery_error_cat_title'],false);

        // Update the category
        $db->query('', "UPDATE {db_prefix}gallery_cat
            SET title = '$title', image = '$image', description = '$description' WHERE id_cat = $catid LIMIT 1");


        redirectexit('action=admin;area=gallery;sa=admincat');

    }

    function DeleteCategory()
    {
        global $context, $txt;

        if (isset($_REQUEST['cat']))
           $catid = (int) $_REQUEST['cat'];
        else
            $catid = 0;

        if (empty($catid))
            fatal_error($txt['gallery_error_no_cat']);

        $context['gallery_catid'] = $catid;

        isAllowedTo('ezgallery_manage');

        $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_delcategory'];

        $context['sub_template']  = 'delete_category';

        $context['linktree'][] = array(
                'name' => $txt['gallery_text_delcategory']
            );


    }

    function DeleteCategory2()
    {
        global $modSettings;

        isAllowedTo('ezgallery_manage');

        $db = database();

        $catid = (int) $_REQUEST['catid'];

        $dbresult = $db->query('', "
        SELECT
            id_picture, thumbfilename, filename
        FROM {db_prefix}gallery_pic
        WHERE id_cat = $catid");

        while($row = $db->fetch_assoc($dbresult))
        {
            // Delete Files

            // Delete Large image
            @unlink($modSettings['gallery_path'] . $row['filename']);
            // Delete Thumbnail
            @unlink($modSettings['gallery_path'] . $row['thumbfilename']);

            $db->query('', "DELETE FROM {db_prefix}gallery_comment WHERE id_picture = " . $row['id_picture']);

            $db->query('', "DELETE FROM {db_prefix}gallery_report WHERE id_picture = " . $row['id_picture']);

        }
        // Delete All Pictures
        $db->query('', "DELETE FROM {db_prefix}gallery_pic WHERE id_cat = $catid");

        // Finally delete the category
        $db->query('', "DELETE FROM {db_prefix}gallery_cat WHERE id_cat = $catid LIMIT 1");


        redirectexit('action=admin;area=gallery;sa=admincat');
    }

    function ViewPicture()
    {
        global $context, $modSettings, $user_info, $scripturl, $txt;

        isAllowedTo('ezgallery_view');

        // Get the picture ID
        if (isset($_REQUEST['pic']))
            $id = (int) $_REQUEST['pic'];

        if (isset($_REQUEST['id']))
            $id = (int) $_REQUEST['id'];

        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected'], false);

        $db = database();


        // Get the picture information
        $dbresult = $db->query('', "
        SELECT
            p.id_picture, p.width, p.height, p.allowcomments, p.id_cat, p.keywords, p.commenttotal, p.filesize, p.filename, p.approved,
            p.views, p.title, p.id_member, m.member_name, m.real_name, p.date, p.description, c.title CATNAME
        FROM {db_prefix}gallery_pic as p
        LEFT JOIN {db_prefix}gallery_cat AS c ON (c.id_cat= p.id_cat)
        LEFT JOIN {db_prefix}members AS m ON (p.id_member = m.id_member)
        WHERE p.id_picture= $id   LIMIT 1");
        $row = $db->fetch_assoc($dbresult);

        // Checked if they are allowed to view an unapproved picture.
        if ($row['approved'] == 0 && $user_info['id']!= $row['id_member'])
        {
            if (!AllowedTo('ezgallery_manage'))
                fatal_error($txt['gallery_error_pic_notapproved'],false);
        }

        if (empty($row['id_picture']))
        {
            fatal_error($txt['gallery_error_no_pic_selected'], false);
        }


        $context['linktree'][] = array(
                        'url' => $scripturl . '?action=gallery;cat=' . $row['id_cat'],
                        'name' => $row['CATNAME'],
                    );

        // Gallery picture information
        $context['gallery_pic'] = array(
            'id_picture' => $row['id_picture'],
            'id_member' => $row['id_member'],
            'commenttotal' => $row['commenttotal'],
            'views' => $row['views'],
            'title' => $row['title'],
            'description' => $row['description'],
            'filesize' => $row['filesize'],
            'filename' => $row['filename'],
            'width' => $row['width'],
            'height' => $row['height'],
            'allowcomments' => $row['allowcomments'],
            'id_cat' => $row['id_cat'],
            'date' => standardTime($row['date']),
            'keywords' => $row['keywords'],
            'member_name' => $row['member_name'],
            'real_name' => $row['real_name'],
        );
        $db->free_result($dbresult);


        // Update the number of views.
        $db->query('', "UPDATE {db_prefix}gallery_pic
            SET views = views + 1 WHERE id_picture= $id LIMIT 1");


        $context['sub_template']  = 'view_picture';

        $context['page_title'] = $context['gallery_pic']['title'];

    $dbresult = $db->query('', "
            SELECT
                c.id_picture,  c.id_comment, c.date, c.comment, c.id_member, m.posts, m.member_name,m.real_name
                FROM {db_prefix}gallery_comment as c
                LEFT JOIN {db_prefix}members AS m ON (c.id_member = m.id_member)
            WHERE   c.id_picture = " . $context['gallery_pic']['id_picture'] . "  ORDER BY c.id_comment DESC");
            $context['gallery_comment_count'] = $db->affected_rows();
            $context['gallery_comment_list'] = array();
        while($row = $db->fetch_assoc($dbresult))
            {
                $context['gallery_comment_list'][] = $row;
            }
            $db->free_result($dbresult);


        if (!empty($modSettings['gallery_who_viewing']))
        {
            $context['can_moderate_forum'] = allowedTo('moderate_forum');

                    // Start out with no one at all viewing it.
                    $context['view_members'] = array();
                    $context['view_members_list'] = array();
                    $context['view_num_hidden'] = 0;
                    $whoID = (string) $id;

                    // Search for members who have this picture id set in their GET data.
                    $request = $db->query('', "
                        SELECT
                            lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
                            mg.online_color, mg.id_group, mg.group_name
                        FROM {db_prefix}log_online AS lo
                            LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lo.id_member)
                            LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = IF(mem.id_group = 0, mem.id_post_group, mem.id_group))
                        WHERE INSTR(lo.url, 's:7:\"gallery\";s:2:\"sa\";s:4:\"view\";s:2:\"id\";s:" . strlen($whoID ) .":\"$id\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'");
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
                            'group' => $row['id_group'],
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

    function AddPicture()
    {
        global $context, $txt, $modSettings;

        isAllowedTo('ezgallery_add');

         if (isset($_REQUEST['cat']))
           $cat = (int) $_REQUEST['cat'];
        else
            $cat = 0;

        $context['gallery_cat_id'] = $cat;

        if (!isset($context['gallery_pic_title']))
            $context['gallery_pic_title'] = '';

        if (!isset($context['gallery_pic_description']))
            $context['gallery_pic_description'] = '';
        if (!isset($context['gallery_pic_keywords']))
            $context['gallery_pic_keywords'] = '';


        $db = database();


        $context['sub_template']  = 'add_picture';

        $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_addpicture'];

        // Check if spellchecking is both enabled and actually working.
        $context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
        if ($context['show_spellchecking'])
            loadJavascriptFile('spellcheck.js', array('defer' => true));

        $context['linktree'][] = array(
                'name' => '<em>' . $txt['gallery_form_addpicture'] . '</em>'
            );

        // Needed for the WYSIWYG editor.
        require_once(SUBSDIR . '/Editor.subs.php');

            // Now create the editor.
            $editorOptions = array(
                'id' => 'descript',
                'value' => $context['gallery_pic_description'],
                'width' => '90%',
                'form' => 'picform',
                'labels' => array(
                    'post_button' => ''
                ),
            );


            create_control_richedit($editorOptions);
            $context['post_box_name'] = $editorOptions['id'];

        $dbresult = $db->query('', "
        SELECT
            id_cat, title
        FROM {db_prefix}gallery_cat ORDER BY roworder ASC");
        $context['gallery_cat_list'] = array();
        while($row = $db->fetch_assoc($dbresult))
            {
                $context['gallery_cat_list'][] = $row;
            }
            $db->free_result($dbresult);

    }

    function AddPicture2()
    {
        global $txt,  $modSettings, $context, $gd2, $user_info;

        isAllowedTo('ezgallery_add');

        // Check if gallery path is writable
        if (!is_writable($modSettings['gallery_path']))
            fatal_error($txt['gallery_write_error'] . $modSettings['gallery_path']);


        $db = database();

        $errors = array();



        $title = Util::htmlspecialchars($_REQUEST['title'],ENT_QUOTES);
        $description = Util::htmlspecialchars($_REQUEST['descript'],ENT_QUOTES);
        $keywords = Util::htmlspecialchars($_REQUEST['keywords'],ENT_QUOTES);
        $cat = (int) $_REQUEST['cat'];

        $allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;

        // Check if pictures are auto approved
        $approved = (allowedTo('ezgallery_autoapprove') ? 1 : 0);

        // Allow comments on picture if no setting set.
        if (empty($modSettings['gallery_commentchoice']) || $modSettings['gallery_commentchoice'] == 0)
            $allowcomments = 1;
        else
        {
            if (empty($allowcomments))
                $allowcomments = 0;
            else
                $allowcomments = 1;
        }

        if (trim($title) == '')
        {
            $errors[] = $txt['gallery_error_no_title'];
            //fatal_error($txt['gallery_error_no_title'],false);

        }
        if (empty($cat))
        {
            $errors[] = $txt['gallery_error_no_cat'];
            //fatal_error($txt['gallery_error_no_cat'],false);
        }

        CheckGalleryCategoryExists($cat);


        $context['gallery_cat_id'] = $cat;
        $context['gallery_pic_title'] = $title;
        $context['gallery_pic_description'] = $description;
        $context['gallery_pic_keywords'] = $keywords;


        $testGD = get_extension_funcs('gd');
        $gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
        unset($testGD);

        //Process Uploaded file
        if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
        {


            $sizes = getimagesize($_FILES['picture']['tmp_name']);
            $failed = false;
            if ($sizes === false)
            {
                @unlink($modSettings['gallery_path'] . '/img.tmp');
                move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . '/img.tmp');

                $_FILES['picture']['tmp_name'] = $modSettings['gallery_path'] . '/img.tmp';
                $sizes = getimagesize($_FILES['picture']['tmp_name']);
                $failed =true;
            }

                // No size, then it's probably not a valid pic.
                if ($sizes === false)
                {
                    @unlink($_FILES['picture']['tmp_name']);
                    $errors[] = $txt['gallery_error_invalid_picture'];
                    //fatal_error($txt['gallery_error_invalid_picture'],false);
                }
                elseif ((!empty($modSettings['gallery_max_width']) && $sizes[0] > $modSettings['gallery_max_width']) || (!empty($modSettings['gallery_max_height']) && $sizes[1] > $modSettings['gallery_max_height']))
                {
                    //Delete the temp file
                    @unlink($_FILES['picture']['tmp_name']);
                    $errors[] = $txt['gallery_error_img_size_height'] . $sizes[1] . $txt['gallery_error_img_size_width'] . $sizes[0];

                    //fatal_error($txt['gallery_error_img_size_height'] . $sizes[1] . $txt['gallery_error_img_size_width'] . $sizes[0],false);
                }
                else
                {
                    //Get the filesize
                    $filesize = $_FILES['picture']['size'];

                    if (!empty($modSettings['gallery_max_filesize']) && $filesize > $modSettings['gallery_max_filesize'])
                    {
                        //Delete the temp file
                        @unlink($_FILES['picture']['tmp_name']);

                        $errors[] = $txt['gallery_error_img_filesize'] . gallery_format_size($modSettings['gallery_max_filesize'], 2);

                        //fatal_error($txt['gallery_error_img_filesize'] . gallery_format_size($modSettings['gallery_max_filesize'], 2) ,false);
                    }


                        // If errors return
                        if (!empty($errors))
                        {
                            $context['gallery_errors'] = $errors;
                            AddPicture();
                            return;
                        }

                    // Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
                    //$extension = substr(strrchr($_FILES['picture']['name'], '.'), 1);
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


                    $filename = $user_info['id'] . '_' . date('d_m_y_g_i_s') . '.' . $extension;

                    if ($failed == false)
                        move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $filename);
                    else
                        rename($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $filename);

                    @chmod($modSettings['gallery_path'] . $filename, 0644);
                    // Create thumbnail
                    require_once(SUBSDIR . '/Graphics.subs.php');

                    createThumbnail($modSettings['gallery_path'] . $filename, $modSettings['gallery_thumb_width'],$modSettings['gallery_thumb_height']);
                    rename($modSettings['gallery_path'] . $filename . '_thumb',  $modSettings['gallery_path'] . 'thumb_' . $filename);
                    $thumbname = 'thumb_' . $filename;



                    @chmod($modSettings['gallery_path'] . $thumbname, 0644);
                    // Create the Database entry
                    $t = time();
                    $db->query('', "INSERT INTO {db_prefix}gallery_pic
                                (id_cat, filesize,thumbfilename,filename, height, width, keywords, title, description,id_member,date,approved,allowcomments)
                            VALUES ($cat, $filesize,'$thumbname', '$filename', $sizes[1], $sizes[0], '$keywords','$title', '$description'," . $user_info['id'] . ",$t,$approved, $allowcomments)");


                // Badge Awards Mod Check
                GalleryCheckBadgeAwards($user_info['id']);

                    // Redirect to the users image page.
                    if ($user_info['id'] != 0)
                        redirectexit('action=gallery;sa=myimages;u=' . $user_info['id']);
                    else
                        redirectexit('action=gallery;cat=' . $cat);
                }

                        if (!empty($errors))
                        {
                            $context['gallery_errors'] = $errors;
                            AddPicture();
                            return;
                        }



        }
        else
        {
            $errors[] = $txt['gallery_error_no_picture'];
            // If errors return
            if (!empty($errors))
            {
                $context['gallery_errors'] = $errors;
                AddPicture();
                return;
            }


        }
    }

    function EditPicture()
    {
        global $context, $txt, $user_info, $modSettings;


        is_not_guest();

        $id = (int) $_REQUEST['pic'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected']);

        $db = database();

        //Check if the user owns the picture or is admin
        $dbresult = $db->query('', "
        SELECT
            p.id_picture, p.thumbfilename, p.width, p.height, p.allowcomments, p.id_cat, p.keywords,
        p.commenttotal, p.filesize, p.filename, p.approved, p.views, p.title, p.id_member, m.member_name, m.real_name, p.date, p.description
        FROM {db_prefix}gallery_pic as p
        LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
        WHERE id_picture= $id LIMIT 1");
        $row = $db->fetch_assoc($dbresult);

        // Gallery picture information
        $context['gallery_pic'] = array(
            'id_picture' => $row['id_picture'],
            'id_member' => $row['id_member'],
            'commenttotal' => $row['commenttotal'],
            'views' => $row['views'],
            'title' => $row['title'],
            'description' => $row['description'],
            'filesize' => $row['filesize'],
            'filename' => $row['filename'],
            'thumbfilename' => $row['thumbfilename'],
            'width' => $row['width'],
            'height' => $row['height'],
            'allowcomments' => $row['allowcomments'],
            'id_cat' => $row['id_cat'],
            'date' => standardTime($row['date']),
            'keywords' => $row['keywords'],
            'member_name' => $row['member_name'],
            'real_name' => $row['real_name'],
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
                    'post_button' => ''
                ),
            );


            create_control_richedit($editorOptions);
            $context['post_box_name'] = $editorOptions['id'];

    $dbresult = $db->query('', "
        SELECT
            id_cat, title
        FROM {db_prefix}gallery_cat ORDER BY roworder ASC");
        $context['gallery_cat_list'] = array();
        while($row = $db->fetch_assoc($dbresult))
            {
                $context['gallery_cat_list'][] = $row;
            }
            $db->free_result($dbresult);

        if (allowedTo('ezgallery_manage') || (allowedTo('ezgallery_edit') && $user_info['id'] == $context['gallery_pic']['id_member']))
        {
            $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_editpicture'];
            $context['sub_template']  = 'edit_picture';


          $context['linktree'][] = array(
                'name' => '<em>' . $txt['gallery_form_editpicture'] . '</em>'
            );

            // Check if spellchecking is both enabled and actually working.
            $context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
            if ($context['show_spellchecking'])
                loadJavascriptFile('spellcheck.js', array('defer' => true));
        }
        else
            fatal_error($txt['gallery_error_noedit_permission']);


    }

    function EditPicture2()
    {
        global $txt, $modSettings, $gd2, $user_info;

        is_not_guest();

        $id = (int) $_REQUEST['id'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected']);

        $db = database();


        // Check the user permissions
        $dbresult = $db->query('', "
        SELECT
            id_member,thumbfilename,filename
        FROM {db_prefix}gallery_pic
        WHERE id_picture= $id LIMIT 1");
        $row = $db->fetch_assoc($dbresult);
        $memID = $row['id_member'];
        $oldfilename = $row['filename'];
        $oldthumbfilename  = $row['thumbfilename'];

        $db->free_result($dbresult);
        if (allowedTo('ezgallery_manage') || (allowedTo('ezgallery_edit') && $user_info['id'] == $memID))
        {

            if (!is_writable($modSettings['gallery_path']))
                fatal_error($txt['gallery_write_error'] . $modSettings['gallery_path']);


            $title = Util::htmlspecialchars($_REQUEST['title'],ENT_QUOTES);
            $description = Util::htmlspecialchars($_REQUEST['descript'],ENT_QUOTES);
            $keywords = Util::htmlspecialchars($_REQUEST['keywords'],ENT_QUOTES);
            $cat = (int) $_REQUEST['cat'];

            $allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;

            //Check if pictures are auto approved
            $approved = (allowedTo('ezgallery_autoapprove') ? 1 : 0);

            //Allow comments on picture if no setting set.
            if (empty($modSettings['gallery_commentchoice']) || $modSettings['gallery_commentchoice'] == 0)
                $allowcomments = 1;
            else
            {
                if (empty($allowcomments))
                    $allowcomments = 0;
                else
                    $allowcomments = 1;
            }



            if (trim($title) == '')
                fatal_error($txt['gallery_error_no_title'],false);
            if (empty($cat))
                fatal_error($txt['gallery_error_no_cat'],false);

            CheckGalleryCategoryExists($cat);


        $testGD = get_extension_funcs('gd');
        $gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
        unset($testGD);

            // Process Uploaded file
            if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
            {

                    $sizes = getimagesize($_FILES['picture']['tmp_name']);
                    $failed = false;
                    if ($sizes === false)
                    {
                        @unlink($modSettings['gallery_path'] . '/img.tmp');
                        move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . '/img.tmp');

                        $_FILES['picture']['tmp_name'] = $modSettings['gallery_path'] . '/img.tmp';
                        $sizes = getimagesize($_FILES['picture']['tmp_name']);
                        $failed = true;
                    }

                    // No size, then it's probably not a valid pic.
                    if ($sizes === false)
                    {
                        @unlink($modSettings['gallery_path'] . '/img.tmp');
                        fatal_error($txt['gallery_error_invalid_picture'],false);
                    }
                    elseif ((!empty($modSettings['gallery_max_width']) && $sizes[0] > $modSettings['gallery_max_width']) || (!empty($modSettings['gallery_max_height']) && $sizes[1] > $modSettings['gallery_max_height']))
                    {
                        @unlink($modSettings['gallery_path'] . '/img.tmp');
                        fatal_error($txt['gallery_error_img_size_height'] . $sizes[1] . $txt['gallery_error_img_size_width']. $sizes[0],false);
                    }
                    else
                    {

                        //Get the filesize
                        $filesize = $_FILES['picture']['size'];
                        if (!empty($modSettings['gallery_max_filesize']) && $filesize > $modSettings['gallery_max_filesize'])
                        {
                            //Delete the temp file
                            @unlink($_FILES['picture']['tmp_name']);
                            fatal_error($txt['gallery_error_img_filesize'] . gallery_format_size($modSettings['gallery_max_filesize'], 2),false);
                        }
                        //Delete the old files
                        @unlink($modSettings['gallery_path'] . $oldfilename );
                        @unlink($modSettings['gallery_path'] . $oldthumbfilename);

                        //Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
                        //$extension = substr(strrchr($_FILES['picture']['name'], '.'), 1);
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


                        $filename = $user_info['id'] . '_' . date('d_m_y_g_i_s') . '.' . $extension;

                        if ($failed == false)
                            move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $filename);
                        else
                            rename($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $filename);

                        @chmod($modSettings['gallery_path'] . $filename, 0644);
                        //Create thumbnail
                        require_once(SUBSDIR . '/Graphics.subs.php');

                        createThumbnail($modSettings['gallery_path'] . $filename, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
                        rename($modSettings['gallery_path'] . $filename . '_thumb',  $modSettings['gallery_path'] . 'thumb_' . $filename);
                        $thumbname = 'thumb_' . $filename;


                        @chmod($modSettings['gallery_path'] . $thumbname, 0644);

                        //Update the Database entry
                        $t = time();

                        $db->query('', "UPDATE {db_prefix}gallery_pic
                        SET id_cat = $cat, filesize = $filesize, filename = '$filename',  thumbfilename = '$thumbname', height = $sizes[1], width = $sizes[0], approved = $approved, date =  $t, title = '$title', description = '$description', keywords = '$keywords', allowcomments = $allowcomments WHERE id_picture= $id LIMIT 1");


                        //Redirect to the users image page.
                        redirectexit('action=gallery;sa=myimages;u=' . $user_info['id']);
                    }

            }
            else
            {
                //Update the image properties if no upload has been set
                $db->query('', "UPDATE {db_prefix}gallery_pic
                    SET id_cat = $cat, title = '$title', description = '$description', keywords = '$keywords', allowcomments = $allowcomments WHERE id_picture= $id LIMIT 1");

                // Redirect to the users image page.
                redirectexit('action=gallery;sa=myimages;u=' . $user_info['id']);

            }

        }
        else
            fatal_error($txt['gallery_error_noedit_permission']);


    }

    function DeletePicture()
    {
        global $context, $txt, $user_info;

        is_not_guest();

        $id = (int) $_REQUEST['pic'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected']);

        $db = database();

        //Check if the user owns the picture or is admin
        $dbresult = $db->query('', "
        SELECT
            p.id_picture, p.thumbfilename, p.width, p.height, p.allowcomments, p.id_cat, p.keywords, p.commenttotal, p.filesize, p.filename, p.approved,
            p.views, p.title, p.id_member, m.member_name, m.real_name, p.date, p.description
        FROM {db_prefix}gallery_pic as p
        LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
        WHERE id_picture= $id  LIMIT 1");
        $row = $db->fetch_assoc($dbresult);

        //Gallery picture information
        $context['gallery_pic'] = array(
            'id_picture' => $row['id_picture'],
            'id_member' => $row['id_member'],
            'commenttotal' => $row['commenttotal'],
            'views' => $row['views'],
            'title' => $row['title'],
            'description' => $row['description'],
            'filesize' => $row['filesize'],
            'filename' => $row['filename'],
            'thumbfilename' => $row['thumbfilename'],
            'width' => $row['width'],
            'height' => $row['height'],
            'allowcomments' => $row['allowcomments'],
            'id_cat' => $row['id_cat'],
            'date' => standardTime($row['date']),
            'keywords' => $row['keywords'],
            'member_name' => $row['member_name'],
            'real_name' => $row['real_name'],
        );
        $db->free_result($dbresult);

        if (allowedTo('ezgallery_manage') || (allowedTo('ezgallery_delete') && $user_info['id'] == $context['gallery_pic']['id_member']))
        {
            $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_delpicture'];
            $context['sub_template']  = 'delete_picture';

            $context['linktree'][] = array(
                'name' => '<em>' . $txt['gallery_form_delpicture'] . '</em>'
            );

        }
        else
            fatal_error($txt['gallery_error_nodelete_permission']);
    }

    function DeletePicture2()
    {
        global $txt, $modSettings, $user_info;

        $id = (int) $_REQUEST['id'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected']);

        $db = database();

        //Check if the user owns the picture or is admin
        $dbresult = $db->query('', "
        SELECT
            p.id_picture, p.filename, p.thumbfilename,  p.id_member
        FROM {db_prefix}gallery_pic as p
        WHERE id_picture= $id LIMIT 1");
        $row = $db->fetch_assoc($dbresult);
        $memID = $row['id_member'];
        $db->free_result($dbresult);

        if (allowedTo('ezgallery_manage') || (allowedTo('ezgallery_delete') && $user_info['id'] == $memID))
        {

            //Delete Large image
            @unlink($modSettings['gallery_path'] . $row['filename']);
            //Delete Thumbnail
            @unlink($modSettings['gallery_path'] . $row['thumbfilename']);

            //Delete all the picture related db entries

            $db->query('', "DELETE FROM {db_prefix}gallery_comment WHERE id_picture = $id LIMIT 1");

            $db->query('', "DELETE FROM {db_prefix}gallery_report WHERE id_picture = $id LIMIT 1");

            //Delete the picture
            $db->query('', "DELETE FROM {db_prefix}gallery_pic WHERE id_picture= $id LIMIT 1");


            // Redirect to the users image page.
            redirectexit('action=gallery;sa=myimages;u=' . $user_info['id']);


        }
        else
            fatal_error($txt['gallery_error_nodelete_permission']);

    }
    function ReportPicture()
    {
        global $context, $txt;

        isAllowedTo('ezgallery_report');

        $id = (int) $_REQUEST['id'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected']);

        $context['gallery_pic_id'] = $id;

        $context['sub_template']  = 'report_picture';

        $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_reportpicture'];

        $context['linktree'][] = array(
                'name' => $txt['gallery_form_reportpicture']
            );

    }

    function ReportPicture2()
    {
        global $txt, $user_info;

        isAllowedTo('ezgallery_report');

        $db = database();

        $comment = Util::htmlspecialchars($_REQUEST['comment'],ENT_QUOTES);
        $id = (int) $_REQUEST['id'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected']);

        if (trim($comment) == '')
            fatal_error($txt['gallery_error_no_comment'],false);

        $commentdate = time();

        $db->query('', "INSERT INTO {db_prefix}gallery_report
                (id_member, comment, date, id_picture)
            VALUES (" . $user_info['id']. ",'$comment', $commentdate,$id)");

        redirectexit('action=gallery;sa=view;pic=' . $id);

    }

    function AddComment()
    {
        global $context, $txt, $modSettings;

        isAllowedTo('ezgallery_comment');
        loadlanguage('Post');

        $id = (int) $_REQUEST['id'];
        if (empty($id) )
            fatal_error($txt['gallery_error_no_pic_selected']);

        $db = database();

        $context['gallery_pic_id'] = $id;

        // Comments allowed check
        $dbresult = $db->query('', "
        SELECT
            p.allowcomments
        FROM {db_prefix}gallery_pic as p
        WHERE id_picture= $id LIMIT 1");
        $row = $db->fetch_assoc($dbresult);
        $db->free_result($dbresult);
        //Checked if comments are allowed
        if ($row['allowcomments'] == 0)
                fatal_error($txt['gallery_error_not_allowcomment']);


        $context['sub_template']  = 'add_comment';

        $context['page_title'] =  $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_addcomment'];

        $context['linktree'][] = array(
                'name' => '<em>' .  $txt['gallery_text_addcomment']. '</em>'
            );

        // Check if spellchecking is both enabled and actually working.
        $context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
        if ($context['show_spellchecking'])
            loadJavascriptFile('spellcheck.js', array('defer' => true));

        $modSettings['disable_wysiwyg'] = !empty($modSettings['disable_wysiwyg']) || empty($modSettings['enableBBC']);


        // Needed for the WYSIWYG editor.
        require_once(SUBSDIR . '/Editor.subs.php');

        // Now create the editor.
        $editorOptions = array(
            'id' => 'message',
            'value' => '',
            'width' => '90%',
            'form' => 'cprofile',
            'labels' => array(
                'post_button' => $txt['gallery_text_addcomment']
            ),
        );
        create_control_richedit($editorOptions);
        $context['post_box_name'] = $editorOptions['id'];

    }

    function AddComment2()
    {
        global $txt, $user_info;

        isAllowedTo('ezgallery_comment');



        $db = database();

        $comment = Util::htmlspecialchars($_REQUEST['message'],ENT_QUOTES);
        $id = (int) $_REQUEST['id'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected']);

        // Check if that picture allows comments.
        $dbresult = $db->query('', "
        SELECT
            p.allowcomments
        FROM {db_prefix}gallery_pic as p
        WHERE id_picture= $id LIMIT 1");
        $row = $db->fetch_assoc($dbresult);
        $db->free_result($dbresult);
        // Checked if comments are allowed
        if ($row['allowcomments'] == 0)
            fatal_error($txt['gallery_error_not_allowcomment']);

        if (trim($comment) == '')
            fatal_error($txt['gallery_error_no_comment'],false);

        $commentdate = time();

        $db->query('', "INSERT INTO {db_prefix}gallery_comment
                (id_member, comment, date, id_picture)
            VALUES (" . $user_info['id'] . ",'$comment', $commentdate,$id)");



        // Badge Awards Mod Check
        GalleryCheckBadgeAwards($user_info['id']);

        //Update Comment total
         $db->query('', "UPDATE {db_prefix}gallery_pic
            SET commenttotal = commenttotal + 1 WHERE id_picture= $id LIMIT 1");


        redirectexit('action=gallery;sa=view;pic=' . $id);

    }

    function DeleteComment()
    {
        global $txt;

        is_not_guest();

        isAllowedTo('ezgallery_manage');

        $id = (int) $_REQUEST['id'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_com_selected']);

        $db = database();

        // Get the picture ID for redirect
        $dbresult = $db->query('', "
        SELECT
            id_picture,ID_COMMENT, id_member
        FROM {db_prefix}gallery_comment
        WHERE ID_COMMENT = $id LIMIT 1");
        $row = $db->fetch_assoc($dbresult);
        $picid = $row['id_picture'];
        $db->free_result($dbresult);
        // Now delete the comment.
        $db->query('', "DELETE FROM {db_prefix}gallery_comment WHERE ID_COMMENT = $id LIMIT 1");


        // Update Comment total
          $db->query('', "UPDATE {db_prefix}gallery_pic
            SET commenttotal = commenttotal - 1 WHERE id_picture= $picid LIMIT 1");



        // Redirect to the picture
        redirectexit('action=gallery;sa=view;pic=' . $picid);
    }



    function CatUp()
    {
        global $txt;
        // Check if they are allowed to manage cats
        isAllowedTo('ezgallery_manage');

        $db = database();
        // Get the cat id
        @$cat = (int) $_REQUEST['cat'];
        ReOrderCats($cat);

        //Check if there is a category above it
        //First get our row order
        $dbresult1 = $db->query('', "
        SELECT
            roworder
        FROM {db_prefix}gallery_cat
        WHERE id_cat = $cat");
        $row = $db->fetch_assoc($dbresult1);
        $oldrow = $row['roworder'];
        $o = $row['roworder'];
        $o--;

        $db->free_result($dbresult1);
        $dbresult = $db->query('', "
        SELECT
            id_cat, roworder
        FROM {db_prefix}gallery_cat
        WHERE roworder = $o");
        if ($db->affected_rows()== 0)
            fatal_error($txt['gallery_nocatabove'],false);
        $row2 = $db->fetch_assoc($dbresult);


        // Swap the order Id's
        $db->query('', "UPDATE {db_prefix}gallery_cat
            SET roworder = $oldrow WHERE id_cat = " .$row2['id_cat']);

        $db->query('', "UPDATE {db_prefix}gallery_cat
            SET roworder = $o WHERE id_cat = $cat");


        $db->free_result($dbresult);

        // Redirect to index to view cats
        redirectexit('action=gallery');
    }

    function CatDown()
    {
        global $txt;

        // Check if they are allowed to manage cats
        isAllowedTo('ezgallery_manage');

        $db = database();
        // Get the cat id
        @$cat = (int) $_REQUEST['cat'];
        ReOrderCats($cat);
        // Check if there is a category below it
        // First get our row order
        $dbresult1 = $db->query('', "
        SELECT
            roworder
        FROM {db_prefix}gallery_cat
        WHERE id_cat = $cat LIMIT 1");
        $row = $db->fetch_assoc($dbresult1);
        $oldrow = $row['roworder'];
        $o = $row['roworder'];
        $o++;

        $db->free_result($dbresult1);
        $dbresult = $db->query('', "
        SELECT
            id_cat, roworder
        FROM {db_prefix}gallery_cat
        WHERE roworder = $o");
        if ($db->affected_rows()== 0)
            fatal_error($txt['gallery_nocatbelow'],false);
        $row2 = $db->fetch_assoc($dbresult);


        //Swap the order Id's
        $db->query('', "UPDATE {db_prefix}gallery_cat
            SET roworder = $oldrow WHERE id_cat = " .$row2['id_cat']);

        $db->query('', "UPDATE {db_prefix}gallery_cat
            SET roworder = $o WHERE id_cat = $cat");


        $db->free_result($dbresult);


        // Redirect to index to view cats
        redirectexit('action=gallery');
    }

    function MyImages()
    {
        global $context, $txt, $modSettings, $user_info;

        isAllowedTo('ezgallery_view');

        $u = (int) $_REQUEST['u'];
        if (empty($u))
            fatal_error($txt['gallery_error_no_user_selected']);

        $db = database();
        // Store the gallery userid
        $context['gallery_userid'] = $u;

        $dbresult = $db->query('', "
        SELECT
            m.member_name, m.real_name
        FROM {db_prefix}members AS m
        WHERE m.id_member = $u  LIMIT 1");
        $row = $db->fetch_assoc($dbresult);
        $context['gallery_usergallery_name'] = $row['real_name'];
        $db->free_result($dbresult);

        $context['start'] = (int) $_REQUEST['start'];

        $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $context['gallery_usergallery_name'];

        $context['sub_template']  = 'myimages';

        $context['linktree'][] = array(
                'name' => $txt['gallery_myimages']
            );


        $userid = $context['gallery_userid'];
            $dbresult = $db->query('', "
            SELECT COUNT(*) AS total
             FROM {db_prefix}gallery_pic as p, {db_prefix}members AS m
            WHERE p.id_member = $userid AND p.id_member = m.id_member " . ($user_info['id'] == $userid ? '' : ' AND p.approved = 1'));
        $row = $db->fetch_assoc($dbresult);
        $context['gallery_totalpic'] = $row['total'];
        $db->free_result($dbresult);

        $dbresult = $db->query('', "
        SELECT
            p.id_picture, p.commenttotal, p.title, p.filesize, p.thumbfilename, p.approved, p.views, p.id_member, m.real_name, p.date, p.filename, p.height, p.width
        FROM {db_prefix}gallery_pic as p, {db_prefix}members AS m
        WHERE p.id_member = $userid AND p.id_member = m.id_member " . ($user_info['id'] == $userid ? '' : ' AND p.approved = 1 ')  . " LIMIT $context[start]," . $modSettings['gallery_set_images_per_page']);
        $context['gallery_my_images'] = array();
        while($row = $db->fetch_assoc($dbresult))
            {
                $context['gallery_my_images'][] = $row;
            }
            $db->free_result($dbresult);

    }


    function ApprovePicture()
    {
        global $txt;

        isAllowedTo('ezgallery_manage');

        $id = (int) $_REQUEST['id'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected']);

        $db = database();

        // Update the approval
        $db->query('', "UPDATE {db_prefix}gallery_pic SET approved = 1 WHERE id_picture= $id LIMIT 1");

        // Redirect to approval list
        redirectexit('action=admin;area=gallery;sa=approvelist');

    }

    function UnApprovePicture()
    {
        global $txt;

        isAllowedTo('ezgallery_manage');

        $id = (int) $_REQUEST['pic'];
        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected']);

        $db = database();

        // Update the approval
         $db->query('', "UPDATE {db_prefix}gallery_pic SET approved = 0 WHERE id_picture= $id LIMIT 1");

        // Redirect to approval list
        redirectexit('action=admin;area=gallery;sa=approvelist');
    }


    function Search()
    {
        global $context, $txt;

        //  the user allowed to view the gallery?
        isAllowedTo('ezgallery_view');

        $context['sub_template']  = 'search';

        $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_search'];

        $context['linktree'][] = array(
                'name' => $txt['gallery_search']
            );
    }

    function Search2()
    {
        global $context, $txt;

        // Is the user allowed to view the gallery?
        isAllowedTo('ezgallery_view');

        $db = database();

        // Check if keyword search was selected
        @$keyword =  Util::htmlspecialchars($_REQUEST['key'],ENT_QUOTES);
        if ($keyword == '')
        {
            //Probably a normal Search
            if (isset($_REQUEST['searchfor']))
                $searchfor =  Util::htmlspecialchars($_REQUEST['searchfor'],ENT_QUOTES);
            else
                $searchfor = '';

            if ($searchfor == '')
                fatal_error($txt['gallery_error_no_search'],false);

            if (Util::strlen($searchfor) <= 3)
                fatal_error($txt['gallery_error_search_small'],false);

            // Check the search options
			$searchkeywords =  isset($_REQUEST['searchkeywords']) ? 1 : 0;
			$searchtitle =  isset($_REQUEST['searchtitle']) ? 1 : 0;
			$searchdescription =  isset($_REQUEST['searchdescription']) ? 1 : 0;
		
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
                    $searchquery .= " OR p.keywords LIKE '%$searchfor%'";
                else
                    $searchquery = "p.keywords LIKE '%$searchfor%'";
            }


            if ($searchquery == '')
                $searchquery = "p.title LIKE '%$searchfor%' ";

            $context['gallery_search_query'] = $searchquery;



            $context['gallery_search'] = $searchfor;
        }
        else
        {
            //Search for the keyword


            //Debating if I should add string length check for keywords...
            //if(strlen($keyword) <= 3)
                //fatal_error($txt['gallery_error_search_small']);

            $context['gallery_search'] = $keyword;

            $context['gallery_search_query'] = "p.keywords LIKE '%$keyword%'";
        }

        $dbresult = $db->query('', "
        SELECT
            p.id_picture, p.commenttotal, p.keywords, p.filesize, p.thumbfilename, p.approved, p.views, p.title, p.id_member, m.real_name, p.date, p.width, p.height, p.filename FROM {db_prefix}gallery_pic as p
        LEFT JOIN {db_prefix}members AS m ON (p.id_member = m.id_member)
        WHERE p.approved = 1 AND (" . $context['gallery_search_query'] . ")");
        $context['gallery_search_results'] = array();
        while($row = $db->fetch_assoc($dbresult))
            {
                $context['gallery_search_results'][] = $row;
            }
            $db->free_result($dbresult);

        $context['sub_template']  = 'search_results';

        $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_searchresults'];


        $context['linktree'][] = array(
                'name' => $txt['gallery_searchresults']
            );

    }


    function ReGenerateThumbnails()
    {
        global $context, $txt;

        $db = database();
		$cat = (int) $_REQUEST['cat'];


        if (empty($cat))
            fatal_error($txt['gallery_error_no_cat']);

        isAllowedTo('ezgallery_manage');


        // Get the category name

            $dbresult1 = $db->query('', "
                SELECT
                    title
                FROM {db_prefix}gallery_cat
                WHERE id_cat = $cat");

            $row = $db->fetch_assoc($dbresult1);
            $context['gallery_cat_name'] = $row['title'];
            $db->free_result($dbresult1);


            $context['catid'] = $cat;



        $context['sub_template']  = 'regenerate';
        $context['page_title'] = $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_regeneratethumbnails2'];

        $context['linktree'][] = array(
                'name' => $txt['gallery_text_regeneratethumbnails2']
            );

    }

    function ReGenerateThumbnails2()
    {
        global $txt, $modSettings, $gd2, $context;

        $id = (int) $_REQUEST['id'];

        if (empty($id))
            return;

        isAllowedTo('ezgallery_manage');

        $db = database();
        $context['catid'] = $id;

        $catWhere = " ID_CAT = $id";

        // Check if gallery path is writable
        if (!is_writable($modSettings['gallery_path']))
            fatal_error($txt['gallery_write_error'] . $modSettings['gallery_path']);

        // Increase the max time to process the images
        @ini_set('max_execution_time', '900');

        $testGD = get_extension_funcs('gd');
        $gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
        unset($testGD);

        require_once(SUBSDIR . '/Graphics.subs.php');



        $context['start'] = empty($_REQUEST['start']) ? 25 : (int) $_REQUEST['start'];

        $request = $db->query('', "
        SELECT
            COUNT(*)
        FROM {db_prefix}gallery_pic
        WHERE $catWhere");
        list($totalProcess) = $db->fetch_row($request);
        $db->free_result($request);

        // Initialize the variables.
        $increment = 25;
        if (empty($_REQUEST['start']))
            $_REQUEST['start'] = 0;

        $_REQUEST['start'] = (int) $_REQUEST['start'];


        $dbresult = $db->query('', "
            SELECT
                filename, id_picture
            FROM {db_prefix}gallery_pic
            WHERE $catWhere LIMIT " . $_REQUEST['start'] . ","  . ($increment));
        $counter = 0;
        $gallery_pics = array();
        while ($row = $db->fetch_assoc($dbresult))
        {
            $gallery_pics[] = $row;
        }
        $db->free_result($dbresult);

        foreach($gallery_pics as $row)
        {
            $filename = $row['filename'];
            $extra_path = '';


            createThumbnail($modSettings['gallery_path'] . $extra_path .  $filename, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
            unlink($modSettings['gallery_path'] . $extra_path . 'thumb_' . $filename);
            rename($modSettings['gallery_path'] . $extra_path .  $filename . '_thumb',  $modSettings['gallery_path']  . $extra_path . 'thumb_' . $filename);
            @chmod($modSettings['gallery_path'] . $extra_path  .  'thumb_' . $filename, 0755);
            $thumbnailPath = $extra_path  .  'thumb_' . $filename;


                $db->query('', "
                UPDATE {db_prefix}gallery_pic SET thumbfilename = '$thumbnailPath'
                WHERE ID_PICTURE = " . $row['id_picture']);


            $counter++;

        }

        $_REQUEST['start'] += $increment;

        $complete = 0;
        if ($_REQUEST['start'] < $totalProcess)
        {

            $context['continue_get_data'] = 'start=' . $_REQUEST['start'];
            $context['continue_percent'] = round(100 * $_REQUEST['start'] / $totalProcess);


        }
        else
            $complete = 1;

        // Redirect back to the category
        if ($complete == 1)
            redirectexit('action=gallery;cat=' .  $id);
        else
        {
            $context['sub_template']  = 'regenerate2';

            $context['page_title'] =  $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_regeneratethumbnails2'];

        }

    }



    function PreviousImage($id = 0, $picCat = 0, $return = false)
    {
        global $txt;

        if (empty($id))
            $id = (int) $_REQUEST['id'];

        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected'],false);

        $db = database();

        // Get the category
        if (empty($picCat))
        {
            $dbresult = $db->query('', "
                SELECT
                    p.id_picture, p.id_cat
                FROM {db_prefix}gallery_pic as p
                LEFT JOIN {db_prefix}gallery_cat as c ON (p.id_cat = c.id_cat)
                WHERE p.id_picture = $id LIMIT 1");

            if ($db->num_rows($dbresult) == 0)
                fatal_error($txt['gallery_error_no_pic_selected'],false);

            $row = $db->fetch_assoc($dbresult);
            $id_cat = $row['id_cat'];


            $db->free_result($dbresult);
        }
        else
        {
            $id_cat = $picCat;

        }

        //if ($sortcat == '')
            $sortcat = 'p.id_picture';

        $ordersign = '>';

        //if ($ordercat == '')
            $ordercat = 'ASC';

        // Get previous image
        $dbresult = $db->query('', "
            SELECT
                p.id_picture
            FROM {db_prefix}gallery_pic as p
            WHERE p.id_cat = $id_cat AND  p.approved = 1 AND p.id_picture $ordersign $id
            ORDER BY $sortcat $ordercat LIMIT 1");
        if ($db->affected_rows() != 0)
        {
            $row = $db->fetch_assoc($dbresult);
            $id_picture = $row['id_picture'];
        }
        else
            $id_picture = $id;

        $db->free_result($dbresult);

        if ($return == false)
            redirectexit('action=gallery;sa=view&id=' . $id_picture);
        else
            return $id_picture;
    }

    function NextImage($id = 0, $picCat = 0, $return = false)
    {
        global $txt;

        if (empty($id))
            $id = (int) $_REQUEST['id'];

        if (empty($id))
            fatal_error($txt['gallery_error_no_pic_selected'],false);

        $db = database();

        // Get the category
        if (empty($picCat))
        {
            $dbresult = $db->query('', "
                SELECT
                    p.id_picture, p.id_cat
                FROM {db_prefix}gallery_pic as p
                LEFT JOIN {db_prefix}gallery_cat as c ON (p.id_cat = c.id_cat)
                WHERE p.id_picture = $id  LIMIT 1");

            if ($db->num_rows($dbresult) == 0)
                fatal_error($txt['gallery_error_no_pic_selected'],false);

            $row = $db->fetch_assoc($dbresult);
            $id_cat = $row['id_cat'];



            $db->free_result($dbresult);
        }
        else
        {
            $id_cat = $picCat;
        }

        //if ($sortcat == '')
            $sortcat = 'p.id_picture';

        //if ($ordercat == '')
            $ordercat = 'DESC';

        $ordersign = '<';


        // Get next image

        $dbresult = $db->query('', "
            SELECT
                p.id_picture
            FROM {db_prefix}gallery_pic as p
            WHERE p.id_cat = $id_cat AND   p.approved = 1 AND p.id_picture $ordersign $id
            ORDER BY $sortcat $ordercat LIMIT 1");

        if ($db->affected_rows() != 0)
        {
            $row = $db->fetch_assoc($dbresult);
            $id_picture = $row['id_picture'];
        }
        else
            $id_picture = $id;
        $db->free_result($dbresult);

        if ($return == false)
            redirectexit('action=gallery;sa=view&id=' . $id_picture);
        else
            return $id_picture;
    }


}


?>