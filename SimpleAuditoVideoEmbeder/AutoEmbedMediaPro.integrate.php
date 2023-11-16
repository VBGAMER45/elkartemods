<?php

/*
Simple Audio Video Embedder
Version 4.0
by:vbgamer45
http://www.elkartemods.com

License Information:
Links to http://www.elkartemods.com must remain unless
branding free option is purchased.
*/


function mediapro_integrate_admin_areas(&$admin_areas, &$menuOptions)
{
	global $txt;

	$temp = $admin_areas;
	$admin_areas = array();

	foreach ($temp as $area => $data)
	{
		$admin_areas[$area] = $data;

		if ($area == 'layout')
		{
			loadLanguage('AutoEmbedMediaPro');
			$admin_areas['mediapro'] = array(
				'title' => $txt['mediapro_admin'],
				'areas' => array(
					'mediapro' => array(
						'label' => $txt['mediapro_settings'],
						'file' => 'AutoEmbedMediaPro.controller.php',
						'controller' => 'AutoEmbedMediaPro_Controller',
						'function' => 'action_index',
						'icon' => 'transparent.png',
						'class' => 'admin_img_corefeatures',
						'permission' => array('admin_forum'),
						'subsections' => array(
						'settings' => array($txt['mediapro_settings']),
						'copyright' => array($txt['mediapro_copyremove']),
						),
					),
				),
				);

		}

	}


}
function mediapro_integrate_post_parsebbc(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	require_once(SUBSDIR. '/AutoEmbedMediaPro.subs.php');
	$message = MediaProProcess($message);


}

function mediapro_integrate_post_parsebbc2(&$message)
{
//1.1x
	require_once(SUBSDIR. '/AutoEmbedMediaPro.subs.php');
	$message = MediaProProcess($message);


}


function mediapro_integrate_buffer($tourniquet)
{
	global $context, $modSettings, $forum_version;


	// Copyright removal check
	$showInfo = MediaProCheckInfo();

	if ($showInfo == false)
		return $tourniquet;


	// Based on @author SimplePortal Team http://www.simpleportal.net

	$fix = '<a href="https://www.elkartemods.com" target="_blank">Simple Audio Video Embedder</a>';

	if ((ELK == 'SSI' && empty($context['standalone'])) || !Template_Layers::getInstance()->hasLayers() || strpos($tourniquet, $fix) !== false)
		return $tourniquet;

	// Don't display copyright for things like SSI.


	if (!isset($forum_version) && !defined("FORUM_VERSION"))
		return '';

	// Append our cp notice at the end of the line
	$finds = array(
		sprintf('powered by %1$s</a> | ', $forum_version),
	);
	$replaces = array(
		sprintf('powered by %1$s</a> | ', $forum_version) . $fix . ' | ',
	);

	$tourniquet = str_replace($finds, $replaces, $tourniquet);

	// Can't find it for some reason so we add it at the end
	if (strpos($tourniquet, $fix) === false)
	{
		$fix = '<div style="text-align: center; width: 100%; font-size: x-small; margin-bottom: 5px;">' . $fix . '</div></body></html>';
		$tourniquet = preg_replace('~</body>\s*</html>~', $fix, $tourniquet);
	}

	return $tourniquet;
}


function MediaProCheckInfo()
{
    global $modSettings, $boardurl;

    if (isset($modSettings['mediapro_copyrightkey']))
    {
        $m = 36;
        if (!empty($modSettings['mediapro_copyrightkey']))
        {
            if ($modSettings['mediapro_copyrightkey'] == sha1($m . '-' . $boardurl))
            {
                return false;
            }
            else
                return true;
        }
    }

    return true;
}






