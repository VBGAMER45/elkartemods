<?php
/*
RSS Feed Poster
Version 4.2
by:vbgamer45
http://www.elkartemods.com
*/

function feedposter_integrate_admin_areas(&$admin_areas, &$menuOptions)
{
	global $txt;

	loadlanguage('FeedPoster');

    $admin_areas['config']['areas']['feedsadmin'] = array(
        'label' => $txt['rssposter_admin'],
        'file' => 'FeedPoster.controller.php',
		'controller' => 'FeedPosterAdmin_Controller',
        'permission' => array('admin_forum'),
        'function' => 'action_menu',
        'icon' => 'transparent.png',
        'class' => 'admin_img_corefeatures',
        'subsections' => array(
        ),
    );

					
}

function feedposter_integrate_actions(&$actions)
{
    global $modSettings;
    // RSS Feed Poster run fake cron job
		if (!empty($modSettings['rss_fakecron']))
		{

			if (empty($modSettings['rss_lastcron']) || $modSettings['rss_lastcron'] + (1 * 60) > time())
			{
				require_once(SUBSDIR . '/rss.subs.php');
				UpdateRSSFeedBots();
				updateSettings(array('rss_lastcron' => time()));
			}


		}
}
