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


function ezgallery_integrate_admin_areas(&$admin_areas, &$menuOptions)
{
	global $txt;
		
	$temp = $admin_areas;
	$admin_areas = array();

	foreach ($temp as $area => $data)
	{
		$admin_areas[$area] = $data;
		
		if ($area == 'layout')
		{
			loadlanguage('Gallery');
			

			$admin_areas['gallery'] = array(
				'title' => $txt['ezgallery_admin'],
				'permission' => array('ezgallery_manage'),
				'areas' => array(
				'gallery' => array(
									'label' => $txt['ezgallery_admin'],
									'file' => 'galleryadmin.controller.php',
									'controller' => 'ezGalleryAdmin_Controller',
									'function' => 'action_index',
									'icon' => 'transparent.png',
									'class' => 'admin_img_corefeatures',
									'permission' => array('ezgallery_manage'),
									'subsections' => array(
										'settings' => array($txt['gallery_text_settings']),
										'admincat' => array($txt['gallery_form_managecats']),
										'reportlist' => array($txt['gallery_form_reportimages']),
										'approvelist' => array($txt['gallery_form_approveimages']),
										'copyright' => array($txt['gallery_txt_copyrightremoval']),
									),
				),),
				);
			
		}
		
	}

					
}



function ezgallery_integrate_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionList['membergroup']['ezgallery_view'] = array(false, 'ezgallery', 'ezgallery');
	$permissionList['membergroup']['ezgallery_add'] = array(false, 'ezgallery', 'ezgallery');
	$permissionList['membergroup']['ezgallery_edit'] = array(false, 'ezgallery', 'ezgallery');
	$permissionList['membergroup']['ezgallery_delete'] = array(false, 'ezgallery', 'ezgallery');
	$permissionList['membergroup']['ezgallery_comment'] = array(false, 'ezgallery', 'ezgallery');
	$permissionList['membergroup']['ezgallery_report'] = array(false, 'ezgallery', 'ezgallery');
	$permissionList['membergroup']['ezgallery_autoapprove'] = array(false, 'ezgallery', 'ezgallery');
	$permissionList['membergroup']['ezgallery_manage'] = array(false, 'ezgallery', 'ezgallery');

	$permissionGroups['membergroup'][] = 'ezgallery';

	$leftPermissionGroups[] = 'ezgallery';
}

function ezgallery_integrate_whos_online($actions)
{

	$data = null;

	loadlanguage('Gallery');


	return $data;
}
	
function ezgallery_integrate_menu_buttons(&$buttons)
{
	global $txt, $scripturl;

	loadlanguage('Gallery');

	$buttons = elk_array_insert($buttons, 'home', array(
		'gallery' => array(
			'title' => $txt['ezgallery_menu'],
		//	'data-icon' => '&#xf0c0;',
			'href' => $scripturl . '?action=gallery',
			'show' => allowedTo('ezgallery_view'),
			'sub_buttons' => array(),
		),
	), 'after');
}


function ezgallery_integrate_actions(&$actions)
{
	$actions['gallery'] = array('gallery.controller.php', 'Gallery_Controller', 'action_index');
}

function ezgallery_integrate_actions2(&$actionArray, &$adminAction)
{

        $actionArray = array_merge(
            array (
                'gallery'     => array('gallery.controller.php', 'Gallery_Controller', 'action_index')
            ),
            $actionArray
        );
//print_r($actionArray);
      //  die("disable");
}