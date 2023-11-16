<?php
/**
 * @name      ElkArte Forum
 * @copyright ElkArte Forum contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * This software is a derived product, based on:
 *
 * Simple Machines Forum (SMF)
 * copyright:	2011 Simple Machines (http://www.simplemachines.org)
 * license:  	BSD, See included LICENSE.TXT for terms and conditions.
 *
 * @version 1.0
 *
 */

class HTMLBBC
{
	public static function bbc_codes(&$bbcodes)
	{
		
		if (defined('FORUM_VERSION'))
		{
		$bbcodes[] = array(
			BBC\Codes::ATTR_TAG => 'html',
			BBC\Codes::ATTR_TYPE => BBC\Codes::TYPE_UNPARSED_EQUALS,
			BBC\Codes::ATTR_CONTENT => '$1',
			BBC\Codes::ATTR_BLOCK_LEVEL => true,
			BBC\Codes::ATTR_DISABLED_CONTENT  => '$1',
		);	
		}
		else
		$bbcodes[] = array(
			'tag' => 'html',
			'type' => 'unparsed_content',
			'content' => '$1',
			'block_level' => true,
			'disabled_content' => '$1',
		);
		
		

		
	}

	public static function preparse_code(&$part, $i, $previewing)
	{
		if ($i % 4 == 0)
		{
			if (!$previewing && strpos($part, '[html]') !== false)
			{
				if (allowedTo('admin_forum'))
					$part = preg_replace_callback('~\[html\](.+?)\[/html\]~is', 'preparsecode_html_callback', $part);
				// We should edit them out, or else if an admin edits the message they will get shown...
				else
				{
					while (strpos($part, '[html]') !== false)
						$part = preg_replace('~\[[/]?html\]~i', '', $part);
				}
			}
		}
	}

	public static function unpreparse_code(&$message, &$parts, &$i)
	{
		// If $i is a multiple of four (0, 4, 8, ...) then it's not a code section...
		if ($i % 4 == 0)
			$parts[$i] = preg_replace_callback('~\[html\](.+?)\[/html\]~i', 'preparsecode_unhtml_callback', $parts[$i]);
	}
	
	public static function unpreparse_code2(&$message, &$parts, $i)
	{
		// If $i is a multiple of four (0, 4, 8, ...) then it's not a code section...
		if ($i % 4 == 0)
			$parts[$i] = preg_replace_callback('~\[html\](.+?)\[/html\]~i', 'preparsecode_unhtml_callback', $parts[$i]);
	}
	
}

/**
 * Prepares text inside of html tags to make them safe for display and prevent bbc rendering
 *
 * @package Posts
 * @param string[] $matches
 */
function preparsecode_html_callback($matches)
{
	if (defined('FORUM_VERSION')&& !isset($_REQUEST['action']) && $_REQUEST['action'] != "post")
	return strtr(un_htmlspecialchars($matches[1]), array("\n" => '&#13;', '  ' => ' &#32;', '[' => '&#91;', ']' => '&#93;'));
	else
		return '[html]' . strtr(un_htmlspecialchars($matches[1]), array("\n" => '&#13;', '  ' => ' &#32;', '[' => '&#91;', ']' => '&#93;')) . '[/html]';
}

/**
 * Reverses what was done by preparsecode to html tags
 *
 * @package Posts
 * @param string[] $matches
 */
function preparsecode_unhtml_callback($matches)
{
		return '[html]' . strtr(htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8'), array('\\&quot;' => '&quot;', '&amp;#13;' => '<br />', '&amp;#32;' => ' ', '&amp;#91;' => '[', '&amp;#93;' => ']')) . '[/html]';

}