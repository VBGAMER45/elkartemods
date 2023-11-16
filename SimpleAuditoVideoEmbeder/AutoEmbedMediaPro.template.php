<?php
/*
Simple Audio Video Embedder
Version 6.0
by:vbgamer45
http://www.elkartemods.com

License Information:
Links to http://www.elkartemods.com must remain unless
branding free option is purchased.
*/

function template_mediapro_settings()
{
	global $context, $txt, $scripturl, $mediaProVersion, $modSettings;

	echo '
 <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['mediapro_admin'] , '
        </h3>
  </div>
	<form method="post" name="frmsettings" id="frmsettings" action="', $scripturl, '?action=admin;area=mediapro;sa=settings2">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	';


	echo '<tr>
	    <td width="50%" colspan="2"  align="center" class="windowbg2">',$txt['mediapro_txt_yourversion'] , $mediaProVersion, '&nbsp;',$txt['mediapro_txt_latestversion'],'<span id="lastmediapro" name="lastmediapro"></span>
	    </td>
	    </tr>';

	// Check if cache folder is writable
	if (!is_writable(BOARDDIR . "/cache/"))
	{
		echo '<tr>
	    <td width="50%" colspan="2"  align="center" class="windowbg2">
	    ' . $txt['mediapro_err_cache'] . ' ' . BOARDDIR . '/cache/mediaprocache.php
	    </td>
	    </tr>';

	}
	
		echo '<tr>
	    <td width="50%" colspan="2"  align="center" class="windowbg2"><strong>' . $txt['mediapro_txt_settings'] . '</strong>
	    </td>
	    </tr>
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['mediapro_txt_default_height'] .'</td>
	    	<td class="windowbg2"><input type="text" size="5" name="mediapro_default_height" value="' . $modSettings['mediapro_default_height'] . '" />
	    	<br /><span class="smalltext">' .$txt['mediapro_txt_default_info'] . '</span>
	    	</td>
	    </tr>
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['mediapro_txt_default_width'] .'</td>
	    	<td class="windowbg2"><input type="text" size="5" name="mediapro_default_width" value="' . $modSettings['mediapro_default_width'] . '" />
	    	<br /><span class="smalltext">' .$txt['mediapro_txt_default_info'] . '</span>
	    	</td>
	    </tr>
	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['mediapro_max_embeds'] .'</td>
	    	<td class="windowbg2"><input type="text" size="5" name="mediapro_max_embeds" value="' . $modSettings['mediapro_max_embeds'] . '" />
	    	</td>
	    </tr>    
	    
        
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['mediapro_disablemobile'] .'</td>
	    	<td class="windowbg2"><input type="checkbox" name="mediapro_disablemobile" ' . ($modSettings['mediapro_disablemobile'] ? ' checked="checked"' : '')  . ' />
	    	</td>
	    </tr>  
        
  	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['mediapro_showlink'] .'</td>
	    	<td class="windowbg2"><input type="checkbox" name="mediapro_showlink" ' . ($modSettings['mediapro_showlink'] ? ' checked="checked"' : '')  . ' />
	    	</td>
	    </tr>  


	    ';

	// Show all the sites
	echo '<tr>
	<td colspan="2" class="windowbg2" align="center">
	<input type="checkbox" onclick="invertAll(this, this.form, \'site\');" class="check" /> ' . $txt['mediapro_txt_checkall'] . '
	</td>
	</tr>
	<tr>
	
	    <td  colspan="2" class="windowbg2" align="center">
	    <table align="center">';

		$siteLevel = 0;
		foreach($context['mediapro_sites'] as $site)
		{
			if ($siteLevel == 0)
				echo '<tr>';

			echo '<td><input type="checkbox" name="site[' . $site['id'] . ']" ' . ($site['enabled'] ? ' checked="checked" ' : '')  . ' />' . $site['title'] . '</td>';

			if ($siteLevel == 0 || $siteLevel == 1)
				$siteLevel++;
			else
			{
				echo '</tr>';
				$siteLevel = 0;
			}
		}

		if ($siteLevel == 1)
		{
			echo '
			<td></td>
			<td></td>
			</tr>';
			$siteLevel = 0;
		}

		if ($siteLevel == 2)
		{
			echo '<td></td>
			</tr>';
			$siteLevel = 0;
		}

	echo '
	    </table>
	    </td>
	  </tr>
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />
	    <input type="submit" name="addpage" value="',$txt['mediapro_save_settings'],'" /></td>
	  </tr>
	  
	  <tr>
	  	<td colspan="2" class="windowbg2" align="center">' . $txt['mediapro_customembed'] . '
		 </td>
	  </tr>
	  
	  </table>
  	</form>

<script language="JavaScript" type="text/javascript" src="https://www.elkartemods.com/versions/autoembedmediapro.js?t=' . time() . '"></script>
			<script language="JavaScript" type="text/javascript">

			function MediaProCurrentVersion()
			{
				if (!window.autoemdedmediaproVersion)
					return;

				mediaprospan = document.getElementById("lastmediapro");

				if (window.autoemdedmediaproVersion != "' . $mediaProVersion . '")
				{
					setInnerHTML(mediaprospan, "<b><font color=\"red\">" + window.autoemdedmediaproVersion + "</font>&nbsp;' . $txt['mediapro_txt_version_outofdate'] . '</b>");
				}
				else
				{
					setInnerHTML(mediaprospan, "' . $mediaProVersion . '");
				}
			}

			// Override on the onload function
			window.onload = function ()
			{
				MediaProCurrentVersion();
			}
			</script>

  ';

}


function template_mediapro_copyright()
{
	global $txt, $scripturl, $context, $boardurl, $modSettings;
                    
    $modID = 36;
    
    $urlBoardurl = urlencode(base64_encode($boardurl));                
                    
    	echo '
	<form method="post" action="',$scripturl,'?action=admin;area=mediapro;sa=copyright;save=1">
    <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['mediapro_txt_copyrightremoval'], '
        </h3>
  </div>
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
	<tr class="windowbg2">
		<td valign="top" align="right">',$txt['mediapro_txt_copyrightkey'],'</td>
		<td><input type="text" name="mediapro_copyrightkey" size="50" value="' . $modSettings['mediapro_copyrightkey'] . '" />
        <br />
        <a href="https://www.elkartemods.com/copyright_removal.php?mod=' . $modID .  '&board=' . $urlBoardurl . '" target="_blank">' . $txt['mediapro_txt_ordercopyright'] . '</a>
        </td>
	</tr>
    <tr class="windowbg2">
        <td colspan="2">' . $txt['mediapro_txt_copyremovalnote'] . '</td>
    </tr>
	<tr class="windowbg2">
		<td valign="top" colspan="2" align="center"><input type="submit" value="' . $txt['mediapro_save_settings'] . '" />
		</td>
		</tr>
	</table>
	</form>
    ';   
    
        
                    
}

?>