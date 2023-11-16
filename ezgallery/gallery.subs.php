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

    function GetTotalPicturesByCATID($ID_CAT)
    {
        $db = database();
        $dbresult2 = $db->query('', "
                SELECT
                    COUNT(*) AS total
                FROM {db_prefix}gallery_pic
                WHERE id_cat = ". $ID_CAT . ' AND approved = 1');
        $rowTotal = $db->fetch_assoc($dbresult2);
        return $rowTotal['total'];
    }

    function ReOrderCats($cat)
    {
        $db = database();

        $dbresult = $db->query('', "
        SELECT
            id_cat, roworder
        FROM {db_prefix}gallery_cat ORDER BY roworder ASC");

        if ($db->affected_rows() != 0)
        {
            $count = 1;
            while($row2 = $db->fetch_assoc($dbresult))
            {
                $db->query('', "UPDATE {db_prefix}gallery_cat
                SET roworder = $count WHERE id_cat = " . $row2['id_cat']);
                $count++;
            }
        }
        $db->free_result($dbresult);
    }


    function gallery_format_size($size, $round = 0)
    {
        // Size must be bytes!
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;
        return round($size,$round).$sizes[$i];
    }

    function ShowTopGalleryBar($title = '')
    {
        global $context;

        if (!empty($title))
            echo '
 
         <div class="cat_bar">
            <h2 class="category_header centertext">
            ', $title, '
            </h2>
    </div>';


            echo '
                    <table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
                            <tr>
                                <td style="padding-right: 1ex;" align="right"  width="100%">
                            <table cellpadding="0" cellspacing="0" align="right" width="100%">
                                        <tr>
                                        <td align="right"  width="100%">
                            ', template_button_strip($context['gallery']['buttons']), '
                            </td>
                            </tr>
                                </table>
                            </td>
                            </tr>
                        </table>
    
    <br />';
    }



    function GalleryCheckInfo()
    {
        global $modSettings, $boardurl;

        if (isset($modSettings['gallery_copyrightkey']))
        {
            $m = 19;
            if (!empty($modSettings['gallery_copyrightkey']))
            {
                if ($modSettings['gallery_copyrightkey'] == sha1($m . '-' . $boardurl))
                {
                    return false;
                }
                else
                    return true;
            }
        }

        return true;
    }

    function GalleryUserTabs($area = '')
    {
        global $context, $scripturl, $txt, $user_info;

        $g_add = allowedTo('ezgallery_add');

        // Add Picture
        if ($g_add)
        $context['gallery']['buttons']['add'] =  array(
            'text' => 'gallery_form_addpicture',
            'url' => $scripturl . '?action=gallery;sa=add',
            'lang' => true,
            'image' => '',

        );

        // MyImages
        if ($g_add && !($context['user']['is_guest']))
        $context['gallery']['buttons']['mylisting'] =  array(
            'text' => 'gallery_myimages',
            'url' =>$scripturl . '?action=gallery;sa=myimages;u=' . $user_info['id'],
            'lang' => true,
            'image' => '',

        );

        // Search
        $context['gallery']['buttons']['search'] =  array(
            'text' => 'gallery_search',
            'url' => $scripturl . '?action=gallery;sa=search',
            'lang' => true,
            'image' => '',

        );







        // Link Tree
        $context['linktree'][] = array(
                        'url' => $scripturl . '?action=gallery',
                        'name' => $txt['gallery_text_title']

                    );
    }

    function CheckGalleryCategoryExists($cat)
    {
        global $txt;

        $db = database();
        $dbresult2 = $db->query('', "
                SELECT
                    COUNT(*) AS total
                FROM {db_prefix}gallery_cat
                WHERE ID_CAT = $cat ");
        $rowTotal =$db->fetch_assoc($dbresult2);
        $db->free_result($dbresult2);

        if ($rowTotal['total'] == 0)
            fatal_error($txt['gallery_error_category'],false);
    }

function DoGalleryAdminTabs($overrideSelected = '')
{
	global $context, $txt;

	$db = database();

	$dbresult3 = $db->query('', "
			SELECT
				COUNT(*) AS total
			FROM {db_prefix}gallery_pic
			WHERE approved = 0");
			$totalrow = $db->fetch_assoc($dbresult3);
			$totalappoval = $totalrow['total'];
			$db->free_result($dbresult3);

	$dbresult4 = $db->query('', "
			SELECT
				COUNT(*) AS total
			FROM {db_prefix}gallery_report");
			$totalrow = $db->fetch_assoc($dbresult4);
	$totalreport = $totalrow['total'];
	$db->free_result($dbresult4);


	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['ezgallery_admin'],
			'description' => '',
			'tabs' => array(
				'setttings' => array(
					'description' => $txt['gallery_set_description'],
				),
				'admincat' => array(
					'description' => '',
				),
				'approvelist' => array(
					'description' => '',
					'label' => $txt['gallery_form_approveimages'] . ' (' . $totalappoval . ')',
				),
				'reportlist' => array(
					'description' => '',
					'label' => $txt['gallery_form_reportimages'] . ' (' . $totalreport . ')',
				),
                'copyright' => array(
					'description' => '',
					'label' => $txt['gallery_txt_copyrightremoval'],
				),
			),
		);



}


    function GalleryCheckBadgeAwards($memID = 0)
    {
        global $modSettings;

        if (!empty($modSettings['badgeawards_enable']))
        {

            //require_once(SOURCEDIR . '/badgeawards2.php');
            Badges_CheckMember($memID);
        }
    }

    function PreviousImageReturn($id = 0, $picCat = 0, $return = false)
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

    function NextImageReturn($id = 0, $picCat = 0, $return = false)
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


?>