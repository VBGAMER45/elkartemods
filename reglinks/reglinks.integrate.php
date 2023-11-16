<?php

/*
Registered Links
Version 3.0
by:vbgamer45
http://www.elkartemods.com


*/


function reglinks_integrate_post_parsebbc(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	global $user_info, $txt, $scripturl;

	loadlanguage('reglinks');

	if ($user_info['is_guest'])
	{
		$message = preg_replace('#<a href="(.*?)</a>#i', $txt['no_view_links'] . "\n" . ' <a href="' . $scripturl . '?action=register">' . $txt['txt_reg_links_register'] . '</a>&nbsp;' . $txt['txt_reg_links_or'] . '&nbsp;<a href="' . $scripturl . '?action=login">' . $txt['txt_reg_links_login'] . '</a>', $message);
	}

}



function reglinks_integrate_post_parsebbc2(&$message)
{
	global $user_info, $txt, $scripturl;

	loadlanguage('reglinks');

	if ($user_info['is_guest'])
	{
		$message = preg_replace('#<a href="(.*?)</a>#i', $txt['no_view_links'] . "\n" . ' <a href="' . $scripturl . '?action=register">' . $txt['txt_reg_links_register'] . '</a>&nbsp;' . $txt['txt_reg_links_or'] . '&nbsp;<a href="' . $scripturl . '?action=login">' . $txt['txt_reg_links_login'] . '</a>', $message);
	}

}






