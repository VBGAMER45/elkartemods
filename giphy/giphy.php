<?php
/*******************************************************************************
 * Giphy for Elkarte
 * http://www.elkartemods.com
 *
 * Copyright (c) 2020 vbgamer45
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 ******************************************************************************/



function giphy_bbc_buttons(&$bbc_tags)
{
	global $context, $editortxt, $modSettings;

	loadLanguage('giphy');
/*
	$context['bbc_tags'][count($context['bbc_tags']) - 1][] = array(
		'image' => 'giphy10',
		'code' => 'scegiphy',
		'description' => $editortxt['scegiphy'],

	);
	*/

$bbc_tags['row2'][] = array("scegiphy");

	loadJavaScriptFile('scegiphy.js', array(), 'elk_scegiphy');


		addInlineJavaScript('
		window.giphykey = "' . $modSettings['giphyapikey'] . '";');
}

function giphy_credits()
{
	global $context;
	$context['copyrights']['mods'][] = 'Giphy for Elkarte by vbgamer45 &copy; 2020';
}

function giphy_sceditor(&$sce_options)
{
	if (!empty($sce_options['plugins']))
		$sce_options['plugins'] .= ',';


		$sce_options['plugins'] .= 'scegiphy';

}

function giphy_mod_settings(&$config_vars)
{
	global $txt;
		loadLanguage('giphy');

		if (!empty($config_vars))
			$config_vars[] = '';

		$config_vars[] = array('text', 'giphyapikey', 'subtext' => $txt['giphyapikey_extra']);
}