<?php
/*
RSS Feed Poster
Version 4.2
by:vbgamer45
http://www.elkartemods.com
*/
global $ssi_guest_access;
$ssi_guest_access = true;

ini_set("display_errors",1);
// SSI needed to get ELK functions
require('SSI.php');

// For the rss functions
require_once(SUBSDIR . '/rss.subs.php');

UpdateRSSFeedBots();
UpdateJSONFeedBots();
die('Feed Cron Finished');
?>