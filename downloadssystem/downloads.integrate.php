<?php

/*
Download System
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


function downloads_integrate_admin_areas(&$admin_areas, &$menuOptions)
{
	global $txt;
		
	$temp = $admin_areas;
	$admin_areas = array();

	foreach ($temp as $area => $data)
	{
		$admin_areas[$area] = $data;
		
		if ($area == 'layout')
		{
			loadlanguage('Downloads');
			

			$admin_areas['downloads'] = array(
				'title' => $txt['downloads_admin'],
				'permission' => array('downloads_manage'),
				'areas' => array(
				'downloads' => array(
									'label' => $txt['downloads_admin'],
									'file' => 'downloadsadmin.controller.php',
									'controller' => 'DownloadsAdmin_Controller',
									'function' => 'action_index',
									'icon' => 'transparent.png',
									'class' => 'admin_img_corefeatures',
									'permission' => array('downloads_manage'),
									'subsections' => array(
										'adminset' => array($txt['downloads_text_settings']),
										'approvelist' => array($txt['downloads_form_approvedownloads']),
										'reportlist' => array($txt['downloads_form_reportdownloads']),
										'commentlist' => array($txt['downloads_form_approvecomments']),
										'filespace' => array($txt['downloads_filespace']),
										'catpermlist' => array($txt['downloads_text_catpermlist2']),
									),
				),),
				);
			
		}
		
	}

					
}



function downloads_integrate_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionList['membergroup']['downloads_view'] = array(false, 'downloads', 'downloads');
	$permissionList['membergroup']['downloads_add'] = array(false, 'downloads', 'downloads');
	$permissionList['membergroup']['downloads_edit'] = array(false, 'downloads', 'downloads');
	$permissionList['membergroup']['downloads_delete'] = array(false, 'downloads', 'downloads');
	$permissionList['membergroup']['downloads_comment'] = array(false, 'downloads', 'downloads');
	$permissionList['membergroup']['downloads_editcomment'] = array(false, 'downloads', 'downloads');
	$permissionList['membergroup']['downloads_report'] = array(false, 'downloads', 'downloads');
	$permissionList['membergroup']['downloads_autocomment'] = array(false, 'downloads', 'downloads');
	$permissionList['membergroup']['downloads_autoapprove'] = array(false, 'downloads', 'downloads');
	$permissionList['membergroup']['downloads_manage'] = array(false, 'downloads', 'downloads');

	$permissionGroups['membergroup'][] = 'downloads';

	$leftPermissionGroups[] = 'downloads';
}

function downloads_integrate_whos_online($actions)
{

	$data = null;

	loadlanguage('Downloads');


	return $data;
}
	
function downloads_integrate_menu_buttons(&$buttons)
{
	global $txt, $scripturl;

	loadlanguage('Downloads');

	$buttons = elk_array_insert($buttons, 'home', array(
		'downloads' => array(
			'title' => $txt['downloads_menu'],
		//	'data-icon' => '&#xf0c0;',
			'href' => $scripturl . '?action=downloads',
			'show' => allowedTo('downloads_view'),
			'sub_buttons' => array(),
		),
	), 'after');
}


function downloads_integrate_actions(&$actions)
{
	$actions['downloads'] = array('downloads.controller.php', 'Downloads_Controller', 'action_index');
}

function downloads_integrate_actions2(&$actionArray, &$adminAction)
{

        $actionArray = array_merge(
            array (
                'downloads'     => array('downloads.controller.php', 'Downloads_Controller', 'action_index')
            ),
            $actionArray
        );

}
