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

if (!defined('ELK'))
	die('No access...');


function MediaProProcess($message)
{
	global $modSettings, $context, $boardurl;
	static $playerCount = 0;

 	if (isset($context['save_embed_disable']) && $context['save_embed_disable'] == 1)
		return $message;

	// If it is short don't do anything
	if (strlen($message) < 7)
		return $message;

    if (isset($_REQUEST['action']))
    {
        if ($_REQUEST['action'] == 'post' || $_REQUEST['action'] == 'post2')
            return $message;
    }
    
    // Max embed settings
	if (!empty($modSettings['mediapro_max_embeds']))
	{
		 if ($playerCount >= $modSettings['mediapro_max_embeds'])
		 	return $message;
	}
    

    // Check disable mobile
    if (!empty($modSettings['mediapro_disablemobile']))
    {
        if (MediaProisMobileDevice() == true)
            return $message;
    }

	// Load the cache file
	if (file_exists(BOARDDIR . "/cache/mediaprocache.php"))
	{
		global $mediaProCache;
		require_once(BOARDDIR. "/cache/mediaprocache.php");


		$mediaProItems =  unserialize($mediaProCache);


	}
	else
		$mediaProItems = MediaProWriteCache();
		
	
	$parsed_url = parse_url($boardurl);	

	// Loop though main array of enabled sites to process
	if (count($mediaProItems) > 0)
	foreach($mediaProItems as $mediaSite)
	{

		if (!empty($modSettings['mediapro_default_width']))
			$movie_width = $modSettings['mediapro_default_width'];
		else
			$movie_width  = $mediaSite['width'];

		if (!empty($modSettings['mediapro_default_height']))
			$movie_height = $modSettings['mediapro_default_height'];
		else
			$movie_height = $mediaSite['height'];


			$mediaSite['embedcode'] = str_replace('#playercount#', $playerCount, $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('#parent#', $parsed_url['host'], $mediaSite['embedcode']);

			$mediaSite['embedcode'] = str_replace('width="480"','width="' . $movie_width  .'"', $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('width:480','width="' . $movie_width  .'px', $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('width=480','width=' . $movie_width , $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('data-width="480"','data-width="' . $movie_width  .'"', $mediaSite['embedcode']);


			 $mediaSite['embedcode'] = str_replace('height="600"','height="' . $movie_height .'"', $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('height:600','height:' . $movie_height.'px', $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('height=600','height=' . $movie_height, $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('data-height="640"','data-height="' . $movie_height .'"', $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('data-height="600"','data-height="' . $movie_height .'"', $mediaSite['embedcode']);


			if (!empty($modSettings['mediapro_showlink']))
				$mediaSite['embedcode'] .= '<br />#MYLINKMEDIA#';


		$medialinks = explode("ZSPLITMZ",$mediaSite['regexmatch']);

		foreach($medialinks as $medialink)
		{


			/// Old replace call
//			$message = preg_replace('#<a href="' . $medialink . '"[^>]*>([^<]+)</a>#i', $mediaSite['embedcode'], $message,-1,$count);

			$message = preg_replace_callback('#<a href="' . $medialink . '"[^>]*>([^<]+)</a>#i', function( $matches ) use ( $mediaSite, &$playerCount)
			{
				$mediaSite['embedcode'] = str_replace("#MYLINKMEDIA#",$matches[0],$mediaSite['embedcode']);

				for ($m = 1;$m < count($matches);$m++)
				{
					$mediaSite['embedcode'] = str_replace('$' . $m,$matches[$m],$mediaSite['embedcode']);
				}

				$playerCount++;

				return $mediaSite['embedcode'];


            }

            , $message,-1);


		}


        // 2.0
		// $message = preg_replace('#<a href="' . $mediaSite['regexmatch'] . '"(.*?)</a>#i', $mediaSite['embedcode'], $message);
	}


	// Return the updated message content
	return $message;
}

function MediaProWriteCache()
{
		
	$db = database();

	$mediaProItems = array();

	// Get list of sites that are enabled
	$result = $db->query('', "
	SELECT
		id, title, website, regexmatch,
		embedcode, height,  width
	FROM {db_prefix}mediapro_sites
	WHERE enabled = 1");
	while ($row =  $db->fetch_assoc($result))
	{
		$mediaProItems[] = $row;
	}

	// Data to write
	$data = '<?php
$mediaProCache = \'' . serialize($mediaProItems)  . '\';
?>';

	// Write the cache to the file
	$fp = fopen(BOARDDIR . "/cache/mediaprocache.php", 'w');
	if ($fp)
	{
		fwrite($fp, $data);
	}

	fclose($fp);


	// Return the items in the array
	return $mediaProItems;

}

function MediaProisMobileDevice()
{
	$user_agents = array(
		array('iPhone', 'iphone'),
		array('iPod', 'ipod'),
		array('iPad', 'ipad'),
		array('PocketIE', 'iemobile'),
		array('Opera Mini', isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ?  'operamini' : ''),
		array('Opera Mobile', 'Opera Mobi'),
		array('Android', 'android'),
		array('Symbian', 'symbian'),
		array('BlackBerry', 'blackberry'),
		array('BlackBerry Storm', 'blackberry05'),
		array('Palm', 'palm'),
		array('Web OS', 'webos'),
	);

	foreach ($user_agents as $ua)
	{
			$string = (string) $ua[1];

			if (!empty($string))
			if ((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $string)))
				return true;
	}

        return false;

}

?>