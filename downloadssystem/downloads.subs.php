<?php

/*
Download System Lite
Version 2.6
by:vbgamer45
http://www.elkartemods.com
Copyright 2017 elkartemods.com

############################################
License Information:

Links to http://www.elkartemods.com must remain unless
branding free option is purchased.
#############################################
*/

function DoDownloadsAdminTabs($overrideSelected = '')
{
	global $context, $txt;

    $db = database();

	$dbresult3 = $db->query('', "
			SELECT
				COUNT(*) AS total
			FROM {db_prefix}down_file
			WHERE approved = 0");
	$totalrow = $db->fetch_assoc($dbresult3);
	$totalappoval = $totalrow['total'];
	$db->free_result($dbresult3);


	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['downloads_admin'],
			'description' => '',
			'tabs' => array(
				'adminset' => array(
					'description' => '',
				),
				'approvelist' => array(
					'description' => '',
					'label' => $txt['downloads_form_approvedownloads']  . ' (' . $totalappoval . ')',
				),
				'reportlist' => array(
					'description' => '',

				),
				'commentlist' => array(
					'description' => '',

				),
				'filespace' => array(
					'description' => '',

				),
				'catpermlist' => array(
					'description' => '',

				),


			),
		);


}

function TopDownloadTabs()
{
	global $context, $txt, $scripturl, $user_info;

	$g_add = allowedTo('downloads_add');


	$catWhere = '';

	if (isset($_REQUEST['cat']))
    {
        $cat = (int) $_REQUEST['cat'];
	    $catWhere = ';cat=' . $cat;
    }

    if ($g_add)
		$context['downloads']['buttons']['add'] =  array(
			'text' => 'downloads_form_adddownload',
			'url' =>$scripturl . '?action=downloads;sa=add'. $catWhere,
			'lang' => true,

		);

	// MyFiles
	if ($g_add && !($user_info['is_guest']))
		$context['downloads']['buttons']['myfiles'] =  array(
			'text' => 'downloads_text_myfiles2',
			'url' =>$scripturl . '?action=downloads;sa=myfiles;u=' . $user_info['id'],
			'lang' => true,

		);

	// Search
	$context['downloads']['buttons']['search'] =  array(
		'text' => 'downloads_text_search2',
		'url' => $scripturl . '?action=downloads;sa=search',
		'lang' => true,

	);

	// Setup Initial Link Tree
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=downloads',
					'name' => $txt['downloads_text_title']
				);
}

function Downloads_GetParentLink($ID_CAT)
{
	global $context, $scripturl;

	if ($ID_CAT == 0)
		return;

	$db = database();

			$dbresult1 = $db->query('', "
		SELECT
			ID_PARENT,title
		FROM {db_prefix}down_cat
		WHERE ID_CAT = $ID_CAT LIMIT 1");
		$row1 = $db->fetch_assoc($dbresult1);

		$db->free_result($dbresult1);

		Downloads_GetParentLink($row1['ID_PARENT']);

		$context['linktree'][] = array(
					'url' => $scripturl . '?action=downloads;cat=' . $ID_CAT ,
					'name' => $row1['title']
				);
}

function Downloads_DoToolBarStrip($button_strip, $direction )
{

		template_button_strip($button_strip, $direction);
}

function Downloads_format_size($size, $round = 0)
{
    //Size must be bytes!
    $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;
    return round($size,$round).$sizes[$i];
}


function ShowTopDownloadBar($title = '&nbsp;')
{
	global $txt, $context;
		echo '
	<div class="cat_bar">
		<h3 class="category_header centertext">
        ', $title, '
        </h3>
</div>
    
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" width="100%">

						', Downloads_DoToolBarStrip($context['downloads']['buttons'], 'top'), '
		
						</td>
						</tr>
					</table>

<br />';
}

function Downloads_ShowUserBox($memCommID, $online_color = '')
{
	global $memberContext, $settings, $modSettings, $txt, $context, $scripturl, $options, $downloadSettings;


	echo '
	<b>', $memberContext[$memCommID]['link'], '</b>
							<div class="smalltext">';

		// Show the member's custom title, if they have one.
		if (isset($memberContext[$memCommID]['title']) && $memberContext[$memCommID]['title'] != '')
			echo '
								', $memberContext[$memCommID]['title'], '<br />';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (isset($memberContext[$memCommID]['group']) && $memberContext[$memCommID]['group'] != '')
			echo '
								', $memberContext[$memCommID]['group'], '<br />';

		// Don't show these things for guests.
		if (!$memberContext[$memCommID]['is_guest'])
		{


			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' ', $memberContext[$memCommID]['karma']['good'] - $memberContext[$memCommID]['karma']['bad'], '<br />';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' +', $memberContext[$memCommID]['karma']['good'], '/-', $memberContext[$memCommID]['karma']['bad'], '<br />';

			// Is this user allowed to modify this member's karma?
			if ($memberContext[$memCommID]['karma']['allow'])
				echo '
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $memberContext[$memCommID]['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $memberContext[$memCommID]['id'],  ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a><br />';

			// Show online and offline buttons?
			if (!empty($modSettings['onlineEnable']) && !$memberContext[$memCommID]['is_guest'])
				echo '
								', $context['can_send_pm'] ? '<a href="' . $memberContext[$memCommID]['online']['href'] . '" title="' . $memberContext[$memCommID]['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $memberContext[$memCommID]['online']['image_href'] . '" alt="' . $memberContext[$memCommID]['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $memberContext[$memCommID]['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $memberContext[$memCommID]['online']['text'] . '</span>' : '', '<br /><br />';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $memberContext[$memCommID]['gender']['image'] != '')
				echo '
								', $txt['downloads_txt_gender'], ': ', $memberContext[$memCommID]['gender']['image'], '<br />';

			// Show how many posts they have made.
			echo '
								', $txt['downloads_txt_posts'], ': ', $memberContext[$memCommID]['posts'], '<br />
								<br />';

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($memberContext[$memCommID]['avatar']['image']))
				echo '
								<div style="overflow: hidden; width: 100%;">', $memberContext[$memCommID]['avatar']['image'], '</div><br />';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $memberContext[$memCommID]['blurb'] != '')
				echo '
								', $memberContext[$memCommID]['blurb'], '<br />
								<br />';


			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{

					echo '
								<a href="', $memberContext[$memCommID]['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.png" alt="' . $txt['downloads_txt_view_profile'] . '" title="' . $txt['downloads_txt_view_profile'] . '" border="0" />' : $txt['downloads_txt_view_profile']), '</a>';

				// Don't show an icon if they haven't specified a website.
				if ($memberContext[$memCommID]['website']['url'] != '')
					echo '
								<a href="', $memberContext[$memCommID]['website']['url'], '" title="' . $memberContext[$memCommID]['website']['title'] . '" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/profile/www_sm.png" alt="' . $txt['downloads_txt_www'] . '" border="0" />' : $txt['downloads_txt_www']), '</a>';



				// Don't show the email address if they want it hidden.
			if (in_array($memberContext[$memCommID]['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					echo '
								<a href="', $scripturl, '?action=emailuser;sa=email;uid=', $memberContext[$memCommID]['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/profile/email_sm.png" alt="' . $txt['downloads_txt_profile_email'] . '" title="' . $txt['downloads_txt_profile_email'] . '" />' : $txt['downloads_txt_profile_email']), '</a></li>';





				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
								<a href="', $scripturl, '?action=pm;sa=send;u=', $memberContext[$memCommID]['id'], '" title="', $memberContext[$memCommID]['online']['label'], '"><img src="', $settings['images_url'], '/profile/im_', $memberContext[$memCommID]['online']['is_online'] ? 'on.png' : 'off.png', '" alt="" class="icon" /></a>';
			}
		}
		// Otherwise, show the guest's email.
		elseif (empty($memberContext[$memCommID]['hide_email']) && $context['can_send_email'])
			echo '
								<br />
								<br />
								<a href="mailto:', $memberContext[$memCommID]['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/profile/email_sm.png" alt="' . $txt['downloads_txt_profile_email'] . '" title="' . $txt['downloads_txt_profile_email'] . '" border="0" />' : $txt['downloads_txt_profile_email']), '</a>';

		// Done with the information about the poster... on to the post itself.
		echo '
							</div>';
}

function Downloads_GetStarsByPrecent($percent)
{
	global $settings, $txt, $context;


        if ($percent == 0)
    		return $txt['downloads_text_catnone'];
    	else if ($percent <= 20)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 1);
    	else if ($percent <= 40)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 2);
    	else if ($percent <= 60)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 3);
    	else if ($percent <= 80)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 4);
    	else if ($percent <= 100)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 5);
}


function Downloads_DeleteFileByID($id)
{
	global $modSettings;

	$db = database();

	require_once(SUBSDIR . '/Topic.subs.php');

    $dbresult = $db->query('', "
    SELECT
    	p.ID_FILE,  p.ID_CAT, p.filesize, p.filename,  p.id_member, p.ID_TOPIC
    FROM {db_prefix}down_file as p
    WHERE ID_FILE = $id LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	$oldfilesize = $row['filesize'];
	$memID = $row['id_member'];
	$db->free_result($dbresult);


	// Delete the download
	if ($row['filename'] != '')
		@unlink($modSettings['down_path'] . $row['filename']);


	// Update the quota
	$oldfilesize = $oldfilesize * -1;

	if ($oldfilesize != 0)
		Downloads_UpdateUserFileSizeTable($memID,$oldfilesize);

	Downloads_UpdateCategoryTotalByFileID($id);

	// Delete all the download related db entries

	$db->query('', "DELETE FROM {db_prefix}down_comment WHERE ID_FILE  = $id");
	$db->query('', "DELETE FROM {db_prefix}down_rating WHERE ID_FILE  = $id");
	$db->query('', "DELETE FROM {db_prefix}down_report WHERE ID_FILE  = $id");
	$db->query('', "DELETE FROM {db_prefix}down_creport WHERE ID_FILE  = $id");
	$db->query('', "DELETE FROM {db_prefix}down_custom_field_data WHERE ID_FILE  = $id");

	// Delete the download
	$db->query('', "DELETE FROM {db_prefix}down_file WHERE ID_FILE = $id LIMIT 1");


 	// Remove the Topic
 	if ($row['ID_TOPIC'] != 0)
		removeTopics($row['ID_TOPIC']);

}

function Downloads_UpdateUserFileSizeTable($memberid, $filesize)
{
	$db = database();

	// Check if a record exits
	$dbresult = $db->query('', "
	SELECT
		id_member,totalfilesize
	FROM {db_prefix}down_userquota
	WHERE id_member = $memberid LIMIT 1");
	$count = $db->affected_rows();
	$db->free_result($dbresult);

	if ($count == 0)
	{
		// Create the record
		$db->query('', "INSERT INTO {db_prefix}down_userquota (id_member, totalfilesize) VALUES ($memberid, $filesize)");
	}
	else
	{
		// Update the record
		if ($filesize >= 0)
			$db->query('', "UPDATE {db_prefix}down_userquota SET totalfilesize = totalfilesize + $filesize WHERE id_member = $memberid LIMIT 1");
		else
			$db->query('', "UPDATE {db_prefix}down_userquota SET totalfilesize = totalfilesize + $filesize WHERE id_member = $memberid LIMIT 1");
	}
}

function Downloads_GetQuotaGroupLimit($memberid)
{
	$db = database();

	$dbresult = $db->query('', "
	SELECT
		m.id_member, q.ID_GROUP, q.totalfilesize
	FROM {db_prefix}down_groupquota as q, {db_prefix}members as m
	WHERE m.id_member = $memberid AND q.ID_GROUP = m.ID_GROUP LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	if ($db->affected_rows() == 0)
	{
		$db->free_result($dbresult);
		return 0;
	}
	else
	{
		$db->free_result($dbresult);

		return $row['totalfilesize'];
	}

}

function Downloads_GetUserSpaceUsed($memberid)
{
	$db = database();

	$dbresult = $db->query('', "
	SELECT
		id_member,totalfilesize
	FROM {db_prefix}down_userquota
	WHERE id_member = $memberid LIMIT 1");
	$row = $db->fetch_assoc($dbresult);
	if ($db->affected_rows()== 0)
	{
		$db->free_result($dbresult);
		return 0;
	}
	else
	{
		$db->free_result($dbresult);

		return $row['totalfilesize'];
	}

}

function Downloads_ApproveFileByID($id)
{
	global $scripturl, $user_info;

	$db = database();

	// Look up the download and get the category
	$dbresult = $db->query('', "
	SELECT
		p.ID_FILE, p.id_member, p.filename, p.title, p.description, c.ID_BOARD,
		p.ID_CAT, c.locktopic
	FROM {db_prefix}down_file AS p
	LEFT JOIN {db_prefix}down_cat AS c ON (c.ID_CAT = p.ID_CAT)
	WHERE p.ID_FILE = $id LIMIT 1");
	$rowcat = $db->fetch_assoc($dbresult);
	$db->free_result($dbresult);

	if ($rowcat['ID_BOARD'] != 0  && $rowcat['ID_BOARD'] != '' )
	{

		$showpostlink = '[url]' . $scripturl . '?action=downloads;sa=view;down=' . $id . '[/url]';


					// Create the post
					require_once(SUBSDIR . '/Post.subs.php');
					$msgOptions = array(
						'id' => 0,
						'subject' => $rowcat['title'],
						'body' => '[b]' . $rowcat['title'] . "[/b]\n\n$showpostlink\n\n" . $rowcat['description'],
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
						'id' => $rowcat['id_member'],
						'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']),
					);


					preparsecode($msgOptions['body']);
					createPost($msgOptions, $topicOptions, $posterOptions);

				}


	// Update the approval
	$db->query('', "UPDATE {db_prefix}down_file SET approved = 1 WHERE ID_FILE = $id LIMIT 1");


	Downloads_UpdateCategoryTotals($rowcat['ID_CAT']);

}

function Downloads_UnApproveFileByID($id)
{

    $db = database();

	// Update the approval
	$db->query('', "UPDATE {db_prefix}down_file SET approved = 0 WHERE ID_FILE = $id LIMIT 1");

	Downloads_UpdateCategoryTotalByFileID($id);
}

function Downloads_UpdateCategoryTotals($ID_CAT)
{


	$db = database();

	if (empty($ID_CAT))
		return;

	$dbresult = $db->query('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}down_file
	WHERE ID_CAT = $ID_CAT AND approved = 1");
	$row = $db->fetch_assoc($dbresult);
	$total = $row['total'];
	$db->free_result($dbresult);

	// Update the count
	$dbresult = $db->query('', "UPDATE {db_prefix}down_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1");

}

function Downloads_UpdateCategoryTotalByFileID($id)
{

	$db = database();

	$dbresult = $db->query('', "
	SELECT
		ID_CAT FROM {db_prefix}down_file
	WHERE ID_FILE = $id");
	$row = $db->fetch_assoc($dbresult);
	$db->free_result($dbresult);

	Downloads_UpdateCategoryTotals($row['ID_CAT']);

}

function Downloads_ReOrderCats($cat)
{

	$db = database();

	$dbresult1 = $db->query('', "
	SELECT
		roworder,ID_PARENT
	FROM {db_prefix}down_cat
	WHERE ID_CAT = $cat");
	$row = $db->fetch_assoc($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$db->free_result($dbresult1);

	$dbresult = $db->query('', "
	SELECT
		ID_CAT, roworder
	FROM {db_prefix}down_cat
	WHERE ID_PARENT = $ID_PARENT
	ORDER BY roworder ASC");
	if ($db->affected_rows() != 0)
	{
		$count = 1;
		while($row2 = $db->fetch_assoc($dbresult))
		{
			$db->query('', "UPDATE {db_prefix}down_cat
			SET roworder = $count WHERE ID_CAT = " . $row2['ID_CAT']);
			$count++;
		}
	}
	$db->free_result($dbresult);
}

function Downloads_ReOrderCustom($id)
{

	$db = database();

	// Get the Category ID by id
	$dbresult = $db->query('', "
	SELECT
		ID_CAT, roworder
	FROM {db_prefix}down_custom_field
	WHERE ID_CUSTOM = $id");
	$row1 = $db->fetch_assoc($dbresult);
	$ID_CAT = $row1['ID_CAT'];
	$db->free_result($dbresult);

	$dbresult = $db->query('', "
	SELECT
		ID_CUSTOM, roworder
	FROM {db_prefix}down_custom_field
	WHERE ID_CAT = $ID_CAT ORDER BY roworder ASC");
	if ($db->affected_rows() != 0)
	{
		$count = 1;
		while($row2 = $db->fetch_assoc($dbresult))
		{
			$db->query('', "UPDATE {db_prefix}down_custom_field
			SET roworder = $count WHERE ID_CUSTOM = " . $row2['ID_CUSTOM']);
			$count++;
		}
	}
	$db->free_result($dbresult);
}


function Downloads_ComputeNextFolderID($ID_FILE)
{
	global $modSettings;

	$folderid = floor($ID_FILE / 1000);

	// If the current folder ID does not match the new folder ID update the settings
	if ($modSettings['down_folder_id'] != $folderid)
		updateSettings(array('down_folder_id' => $folderid));


}

function Downloads_CreateDownloadFolder()
{
	global $modSettings;

	$newfolderpath = $modSettings['down_path'] . $modSettings['down_folder_id'] . '/';

	// Check if the folder exists if it doess just exit
	if  (!file_exists($newfolderpath))
	{
		// If the folder does not exist then create it
		@mkdir ($newfolderpath);
		// Try to make sure that the correct permissions are on the folder
		@chmod ($newfolderpath,0755);
	}

}

function Downloads_GetFileTotals($ID_CAT)
{
	global $modSettings, $subcats_linktree, $scripturl;

	$db = database();

	$total = 0;

	$total += Downloads_GetTotalByCATID($ID_CAT);
	$subcats_linktree = '';

	// Get the child categories to this category
	if ($modSettings['down_set_count_child'])
	{
		$dbresult3 = $db->query('', "
		SELECT
			ID_CAT, total, title
		FROM {db_prefix}down_cat WHERE ID_PARENT = $ID_CAT");
		while($row3 = $db->fetch_assoc($dbresult3))
		{
			$subcats_linktree .= '<a href="' . $scripturl . '?action=downloads;cat=' . $row3['ID_CAT'] . '">' . $row3['title'] . '</a>&nbsp;&nbsp;';

			if ($row3['total'] == -1)
			{
				$dbresult = $db->query('', "
				SELECT
					COUNT(*) AS total
				FROM {db_prefix}down_file
				WHERE ID_CAT = " . $row3['ID_CAT'] . " AND approved = 1");
				$row = $db->fetch_assoc($dbresult);
				$total2 = $row['total'];
				$db->free_result($dbresult);


				$dbresult = $db->query('', "UPDATE {db_prefix}down_cat SET total = $total2 WHERE ID_CAT =  " . $row3['ID_CAT'] . " LIMIT 1");
			}
		}
		$db->free_result($dbresult3);


		$dbresult3 = $db->query('', "
		SELECT
			total, ID_CAT, ID_PARENT
		FROM {db_prefix}down_cat
		WHERE ID_PARENT <> 0");

		$childArray = array();
		while($row3 = $db->fetch_assoc($dbresult3))
		{
			$childArray[] = $row3;
		}

		$total += Downloads_GetFileTotalsByParent($ID_CAT,$childArray);

	}


	return $total;
}

function Downloads_GetFileTotalsByParent($ID_PARENT,$data)
{
	$total = 0;
	foreach($data as $row)
	{
		if ($row['ID_PARENT'] == $ID_PARENT)
		{
			$total += $row['total'];
			$total += Downloads_GetFileTotalsByParent($row['ID_CAT'],$data);
		}
	}

	return $total;
}




function Downloads_GetTotalByCATID($ID_CAT)
{

	$db = database();

	$dbresult = $db->query('', "
	SELECT
		total
	FROM {db_prefix}down_cat
	WHERE ID_CAT = $ID_CAT");
	$row = $db->fetch_assoc($dbresult);
	$db->free_result($dbresult);

	if ($row['total'] != -1)
		return $row['total'];
	else
	{
		$dbresult = $db->query('', "
		SELECT
			COUNT(*) AS total
		FROM {db_prefix}down_file
		WHERE ID_CAT = $ID_CAT AND approved = 1");
		$row = $db->fetch_assoc($dbresult);
		$total = $row['total'];
		$db->free_result($dbresult);

		// Update the count
		$dbresult = $db->query('', "UPDATE {db_prefix}down_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1");

		// Return the total files
		return $total;

	}

}


function Downloads_ShowSubCats($cat,$g_manage)
{
	global $txt, $scripturl, $modSettings, $subcats_linktree, $user_info, $context;

	$db = database();

	if ($user_info['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];


		// List all the categories
		$dbresult = $db->query('', "
		SELECT
			c.ID_CAT, c.title, p.view, c.roworder, c.description, c.image, c.filename
		FROM {db_prefix}down_cat AS c
			LEFT JOIN {db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		WHERE c.ID_PARENT = $cat ORDER BY c.roworder ASC");
		if ($db->affected_rows() != 0)
		{


			echo '<br /><table  class="table_grid">
            <thead>	
            <tr  class="table_head">
            					<th class="lefttext first_th" colspan="2">' . $txt['downloads_text_categoryname'] . '</th>
            					<th  class="centertext" align="center">' . $txt['downloads_text_totalfiles'] . '</th>
            					';
            			if ($g_manage)
            			echo '
            					<th class="lefttext">' . $txt['downloads_text_reorder'] . '</th>
            					<th class="lefttext last_th">' . $txt['downloads_text_options'] . '</th>';

            			echo '</tr>
            			</thead>';





			while($row = $db->fetch_assoc($dbresult))
			{
				// Check permission to show the downloads category
				if ($row['view'] == '0')
					continue;

				$totalfiles = Downloads_GetFileTotals($row['ID_CAT']);

				echo '<tr>';

					if ($row['image'] == '' && $row['filename'] == '')
						echo '<td class="windowbg" width="10%"></td><td  class="windowbg2"><b><a href="' . $scripturl . '?action=downloads;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></b><br />' . parse_bbc($row['description']) . '</td>';
					else
					{
						if ($row['filename'] == '')
							echo '<td class="windowbg" width="10%"><a href="' . $scripturl . '?action=downloads;cat=' . $row['ID_CAT'] . '"><img src="' . $row['image'] . '" /></a></td>';
						else
							echo '<td class="windowbg" width="10%"><a href="' . $scripturl . '?action=downloads;cat=' . $row['ID_CAT'] . '"><img src="' . $modSettings['down_url'] . 'catimgs/' . $row['filename'] . '" /></a></td>';

						echo '<td class="windowbg2"><b><a href="' . $scripturl . '?action=downloads;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></b><br />' . parse_bbc($row['description']) . '</td>';
					}



				// Show total files in the category
				echo '<td align="center" valign="middle" class="windowbg">' . $totalfiles . '</td>';

				// Show Edit Delete and Order category
				if ( $g_manage)
				{
					echo '
					<td class="windowbg2"><a href="' . $scripturl . '?action=downloads;sa=catup;cat=' . $row['ID_CAT'] . '">' . $txt['downloads_text_up'] . '</a>&nbsp;<a href="' . $scripturl . '?action=downloads;sa=catdown;cat=' . $row['ID_CAT'] . '">' . $txt['downloads_text_down'] . '</a></td>
					<td class="windowbg"><a href="' . $scripturl . '?action=downloads;sa=editcat;cat=' . $row['ID_CAT'] . '">' . $txt['downloads_text_edit'] . '</a>&nbsp;<a href="' . $scripturl . '?action=downloads;sa=deletecat;cat=' . $row['ID_CAT'] . '">' . $txt['downloads_text_delete'] . '</a>
					<br /><br />
					<a href="' . $scripturl . '?action=downloads;sa=catperm;cat=' . $row['ID_CAT'] . '">[' . $txt['downloads_text_permissions'] . ']</a>
					</td>';

				}


				echo '</tr>';



                    		if ($subcats_linktree != '')
        					echo '
        					<tr class="windowbg2">
        						<td colspan="',($g_manage ? '6' : '4'), '">&nbsp;<span class="smalltext">',($subcats_linktree != '' ? '<b>' . $txt['downloads_sub_cats'] . '</b>' . $subcats_linktree : ''),'</span></td>
        					</tr>';



			}
			$db->free_result($dbresult);
			echo '</table><br /><br />';
		}
}

function MainPageBlock($title, $type = 'recent')
{
	global $scripturl, $txt, $modSettings, $context, $user_info;

	$db = database();


	if (!$user_info['is_guest'])
		$groupsdata = implode(',',$user_info['groups']);
	else
		$groupsdata = -1;


	$maxrowlevel = 4;
	echo '
    <div class="cat_bar">
		<h3 class="category_header centertext">
        ', $title, '
        </h3>
</div>';


    echo '<table class="table_grid">';

			//Check what type it is
			$query = ' ';
			$query_type = 'p.ID_FILE';
			switch($type)
			{
				case 'recent':
					$query_type = 'p.ID_FILE';
				break;

				case 'viewed':

					$query_type = 'p.views';
				break;

				case 'mostcomments':
					$query_type = 'p.commenttotal';

				break;
				case 'mostdownloaded':
					$qury_type = 'p.totaldownloads';
				break;

				case 'toprated':
					$query_type = 'p.rating';
				break;
			}

				$query = "SELECT p.ID_FILE, p.commenttotal, p.totalratings, p.rating, p.filesize, p.views, p.title, p.id_member, m.real_name, p.date, p.description,
				p.totaldownloads
					FROM {db_prefix}down_file as p
					LEFT JOIN {db_prefix}members AS m  ON (m.id_member = p.id_member)
					LEFT JOIN {db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
					WHERE p.approved = 1 AND (c.view IS NULL || c.view =1) GROUP by p.ID_FILE ORDER BY $query_type DESC LIMIT 4";

			// Execute the SQL query
			$dbresult = $db->query('', $query);
			$rowlevel = 0;
		while($row = $db->fetch_assoc($dbresult))
		{
			if ($rowlevel == 0)
				echo '<tr class="windowbg2">';

			echo '<td align="center"><a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">',$row['title'],'</a><br />';
			echo '<span class="smalltext">';
			if (!empty($modSettings['down_set_t_rating']))
				echo $txt['downloads_form_rating'] . Downloads_GetStarsByPrecent(($row['totalratings'] != 0) ? ($row['rating'] / ($row['totalratings']* 5) * 100) : 0) . '<br />';
			if (!empty($modSettings['down_set_t_downloads']))
				echo $txt['downloads_text_downloads'] . $row['totaldownloads'] . '<br />';
			if (!empty($modSettings['down_set_t_views']))
				echo $txt['downloads_text_views'] . $row['views'] . '<br />';
			if (!empty($modSettings['down_set_t_filesize']))
				echo $txt['downloads_text_filesize'] . Downloads_format_size($row['filesize'], 2) . '<br />';
			if (!empty($modSettings['down_set_t_date']))
				echo $txt['downloads_text_date'] . standardTime($row['date']) . '<br />';
			if (!empty($modSettings['down_set_t_comment']))
				echo $txt['downloads_text_comments'] . ' (<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['commenttotal'] . '</a>)<br />';
			if (!empty($modSettings['down_set_t_username']))
			{
				if ($row['real_name'] != '')
					echo $txt['downloads_text_by'] . ' <a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">'  . $row['real_name'] . '</a><br />';
				else
					echo $txt['downloads_text_by'] . ' ' . $txt['downloads_guest'] . '<br />';
			}
			echo '</span></td>';


			if ($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}
		}
		if ($rowlevel !=0)
		{
			echo '</tr>';
		}

	echo '
	      </table><br />';

	$db->free_result($dbresult);

}

function Downloads_GetCatPermission($cat,$perm)
{
	global $txt, $user_info;

	$db = database();

	$cat = (int) $cat;
	if (!$user_info['is_guest'])
	{
		$dbresult = $db->query('', "
		SELECT
			m.id_member, c.view, c.addfile, c.editfile, c.delfile,c.ratefile, c.addcomment,
			c.editcomment, c.report
		FROM {db_prefix}down_catperm as c, {db_prefix}members as m
		WHERE m.id_member = " . $user_info['id'] . " AND c.ID_GROUP = m.ID_GROUP AND c.ID_CAT = $cat LIMIT 1");
	}
	else
		$dbresult = $db->query('', "
		SELECT
			c.view, c.addfile, c.editfile, c.delfile,c.ratefile, c.addcomment, c.editcomment,
			c.report
		FROM {db_prefix}down_catperm as c
		WHERE c.ID_GROUP = -1 AND c.ID_CAT = $cat LIMIT 1");

	if ($db->affected_rows()== 0)
	{
		$db->free_result($dbresult);
	}
	else
	{
		$row = $db->fetch_assoc($dbresult);

		$db->free_result($dbresult);
		if ($perm == 'view' && $row['view'] == 0)
			fatal_error($txt['downloads_perm_no_view'],false);
		else if ($perm == 'addfile' && $row['addfile'] == 0)
			fatal_error($txt['downloads_perm_no_add'],false);
		else if ($perm == 'editfile' && $row['editfile'] == 0)
			fatal_error($txt['downloads_perm_no_edit'],false);
		else if ($perm == 'delfile' && $row['delfile'] == 0)
			fatal_error($txt['downloads_perm_no_delete'],false);
		else if ($perm == 'ratefile' && $row['ratefile'] == 0)
			fatal_error($txt['downloads_perm_no_ratefile'],false);
		else if ($perm == 'addcomment' && $row['addcomment'] == 0)
			fatal_error($txt['downloads_perm_no_addcomment'],false);
		else if ($perm == 'editcomment' && $row['editcomment'] == 0)
			fatal_error($txt['downloads_perm_no_editcomment'],false);
		else if ($perm == 'report' && $row['report'] == 0)
			fatal_error($txt['downloads_perm_no_report'],false);

	}


}


?>