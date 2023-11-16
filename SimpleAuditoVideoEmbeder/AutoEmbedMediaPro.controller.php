<?php

/*
Simple Audio Video Embedder
Version 6.0
by:vbgamer45
https://www.elkartemods.com

License Information:
Links to https://www.elkartemods.com must remain unless
branding free option is purchased.
*/

if (!defined('ELK'))
	die('No access...');

class AutoEmbedMediaPro_Controller extends Action_Controller
{

	public function action_index()
	{
		global $mediaProVersion;
		// Hold Current Version
		$mediaProVersion = '6.3';

		// Only admins can access MediaPro Settings
		isAllowedTo('admin_forum');
		// Sub Action Array
		$subActions = array(
			'settings' => 'MediaProSettings',
			'settings2' => 'MediaProSettings2',
			'copyright' => 'MediaProCopyright',
		);

		if (isset($_REQUEST['sa']))
			$sa = $_GET['sa'];
		else
			$sa = '';



		if (!empty($subActions[$sa]))
		{
			$funcName = (string) $subActions[$sa];

			return $this->$funcName();
		}
		else
			return $this->MediaProSettings();
	}


	public function MediaProCopyright()
	{
	    global $context, $txt;
		isAllowedTo('admin_forum');


		// Load the language files
		if (loadlanguage('AutoEmbedMediaPro') == false)
			loadLanguage('AutoEmbedMediaPro','english');

		// Load template
		loadtemplate('AutoEmbedMediaPro');

	    if (isset($_REQUEST['save']))
	    {

	        $mediapro_copyrightkey = addslashes($_REQUEST['mediapro_copyrightkey']);

	        updateSettings(
	    	array(
	    	'mediapro_copyrightkey' => $mediapro_copyrightkey,
	    	)

	    	);
	    }

		$context['page_title'] = $txt['mediapro_txt_copyrightremoval'];

		$context['sub_template']  = 'mediapro_copyright';
	}


	public function MediaProSettings()
	{
		global $txt, $context;

		// Load the language files
		if (loadlanguage('AutoEmbedMediaPro') == false)
			loadLanguage('AutoEmbedMediaPro','english');

		// Load template
		loadtemplate('AutoEmbedMediaPro');



		$db = database();

		// Query all the sites
		$context['mediapro_sites'] = array();

		$result = $db->query('', "
		SELECT
			id, title, website, enabled
		FROM {db_prefix}mediapro_sites
		ORDER BY title ASC
		");
		while ($row =  $db->fetch_assoc($result))
		{
			$context['mediapro_sites'][] = $row;
		}


		// Set template
		$context['sub_template'] = 'mediapro_settings';

		// Set page title
		$context['page_title'] = $txt['mediapro_admin'];




	}

	public function MediaProSettings2()
	{

		// Security Check
		checkSession('post');

		require_once(SUBSDIR . '/AutoEmbedMediaPro.subs.php');

		$db = database();

		// Disable all sites
		$db->query('', "
		UPDATE {db_prefix}mediapro_sites SET enabled = 0
		");

		// Check for enabled sites
		if (isset($_REQUEST['site']))
		{
			$sites = $_REQUEST['site'];
			$siteArray = array();
			foreach($sites as $site  => $key)
			{
				$site = (int) $site;
				$siteArray[] = $site;
			}

			if (count($siteArray) != 0)
			{
				$db->query('', "
				UPDATE {db_prefix}mediapro_sites SET enabled = 1 WHERE id IN(" . implode(',',$siteArray) .")");
			}

		}


		// Write the cache
		MediaProWriteCache();

		// Settings
		$mediapro_default_height = (int) $_REQUEST['mediapro_default_height'];
		$mediapro_default_width = (int) $_REQUEST['mediapro_default_width'];
	    $mediapro_disablesig = isset($_REQUEST['mediapro_disablesig']) ? 1 : 0;
	    $mediapro_disablemobile = isset($_REQUEST['mediapro_disablemobile']) ? 1 : 0;


    	$mediapro_max_embeds = (int) $_REQUEST['mediapro_max_embeds'];
    	$mediapro_showlink = isset($_REQUEST['mediapro_showlink']) ? 1 : 0;

			updateSettings(
		array(
		'mediapro_default_height' => $mediapro_default_height,
		'mediapro_default_width' => $mediapro_default_width,
	    'mediapro_disablesig' => $mediapro_disablesig,
	    'mediapro_disablemobile' => $mediapro_disablemobile,
    	'mediapro_max_embeds' => $mediapro_max_embeds,
    	'mediapro_showlink' => $mediapro_showlink,
		));

		// Redirect to the admin area
		redirectexit('action=admin;area=mediapro;sa=settings');
	}


}