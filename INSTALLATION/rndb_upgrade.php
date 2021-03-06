<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>RavenNuke&trade; Data Base Upgrade Script</title>
<style type="text/css">
h1 {
	color: blue;
	font-weight: bold;
	text-align: center;
	font-size: large;
}

span.c1 {
	color: blue
}

span.c2 {
	color: red
}

span.c3 {
	color: red
}

div.c2 {
	text-align: center
}

td.c1 {
	color: blue;
	font-weight: bold;
}
</style>
</head>
<body>
<br />
<br />
<div class="c2"><a href="http://www.ravenphpscripts.com"
	title="Raven Web Service, LLC: Quality Web Hosting And Web Support"><img
	src="../images/RavenWebServices_Banner.gif" alt="" border="0" /></a>&trade;<br />
<br />
</div>
<br />
<?php
define('INSIDE_INSTALL', true);
if (!defined('INCLUDE_PATH')) define('INCLUDE_PATH', '../');
$the_include = INCLUDE_PATH; // Required to work on RavenNuke 2.02.02 and below
// RN0001010 - added following if to get rid of notice in mysql.php
if(!defined('END_TRANSACTION')) define('END_TRANSACTION', 2);

/* like topsy it just grew google tells me comes from Uncle Tom's Cabin but it describes this program (and so many others). February 2008 I am trying to put some order to it.  Borrowing Rob Herder's idea, the current (i.e., being upgraded to) versions for both RavenNuke&trade; and NukeSentinel&trade are stored in constants at the top.  If someone is already at the current version or if they are trying to upgrade from a version not supported here, I am just stopping them now with a nice message.If someone has a valid version to upgrade from, first they see a screen with messages appropriate to their upgrade and they have one last chance before they hit the update button.  This screen is in the function init.  Once they choose to update we go to the function doupdate.  */

/**
 * INSTRUCTIONS FOR NEW VERSION UPDATES:
 *
 * 1. Put the versions of NS, phpBB and RN that are being upgraded to in the file ravenstallerConfigFile.php.
 * 2. Add the version number key and function name to the end of the below array => moved to ravenstallerConfigFile.php.
 * 3. Add a corresponding function (pattern off existing) for the given version update towards the bottom
 *    of this script.
 */
require_once 'ravenstallerConfigFile.php';
// Below array is in config value, upgrade function name sets => moved to ravenstallerConfigFile.php.
/*
 * End of script configuration settings
 */
require_once INCLUDE_PATH.'config.php';
require_once INCLUDE_PATH.'/db/mysql.php';
$db = new sql_db($dbhost, $dbuname, $dbpass, $dbname, false);
if(!$db->db_connect_id) {
	die('<br />Unfortunately there is something wrong with your config.php settings or database setup as the connection has failed<br />');
}
$dbErrors = 0;
$config_value = '';
$update = 0;
$config_valid_flag = false;
$sql = "SELECT Version_Num FROM `${prefix}_config` WHERE 1";
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
if (!$result) {
	die ('<br /> An error occurred reading your configuration table; please check this since the program cannot continue without a value here.<br />');
}
$config_value = $row['Version_Num'];
if ($config_value == RAVENNUKE_VERSION_CONFIG) {
	echo '<h2>You are already updated to '.RAVENNUKE_VERSION_CONFIG.'</h2>';
	echo 'Congratulations!  It appears that your RavenNuke&trade; version has already been updated to ' .RAVENNUKE_VERSION_CONFIG . '.  If you are at NukeSentinel&trade; version '.NUKESENTINEL_VERSION.' or higher, you can skip the NukeSentinal&trade; step and continue the installation process following the instructions in the How To Install documentation.<br /><br />';
	echo '<a href="rndb_upgrade_nukesentinel.php" >Continue on to NukeSentinel&trade; Upgrade</a><br /><br />';
	echo '<a href="http://rnwiki.ravennuke.com/wiki/RavenNuke2:Installation">Return to the How to Install Documentation</a><p>&nbsp;</p>';
	die();
}
// is this a valid config value ... going to do the test once, modify the array above as new versions come out.
if (array_key_exists($config_value, $rndb_valid_versions)) {
	$config_valid_flag = true;
}
else {
	echo '<h2>Error in Configuration Value</h2><br />The valid values of the configuration field for updating by this program are ';
	$version_cnt = count($rndb_valid_versions);
	$i = 0;
	foreach($rndb_valid_versions as $key => $values) {
		if ($i == $version_cnt - 1) echo ' and ';
		echo $key;
		if ($i != $version_cnt - 1) echo ', ';
		$i++;
	}
	echo '. Your system does not have one of those values.  Please check this out - but this program cannot be used to upgrade other versions.  The configuration field is stored in the config table in your MYSQL database in a field called Version_Num. Your system is reporting a value of '.$config_value.'.<br /><br />';
	echo 'Please direct any questions to the forums at <a href="http://www.ravenphpscripts.com">RavenPHPScripts</a><br />';
	die();
}
if (isset($_POST['update'])) {
	$update = $_POST['update'];
}
if ($update == 1 && $config_valid_flag){
	echo '<span class="c1">Do not forget to capture or print these messages before you dismiss the screen </span><br /><br />';
	echo 'Updating based on config value of '.$config_value .'<br />';

	$b = false;
	foreach($rndb_valid_versions as $key => $value) {
		if ($key == $config_value) $b = true;
		if ($b) {
			$return = $value();
		}
	}
	/*
	 * Always perform these checks and/or updates - appropriate for things like ended version numbers, since
	 * interim updates really do not make sense, plus updates that are self-checking/updating.
	 */
	doAlways();
	/*
	 * Finish up the script
	 */
	echo "<br /><br /><div class=\"c2\"><table align=\"center\" width=\"75%\" border=\"1\"><tr><td align=\"center\" style=\"color:blue;font-weight:bold;\"><b>RavenNuke&trade; Update to version ".RAVENNUKE_VERSION_CONFIG." Update finished!</b><br /><br />";
	if ($dbErrors) echo "There were $dbErrors error(s). These errors must be checked out and/or fixed to ensure that the update worked properly.  Sometimes the cause of the error may be trying to insert a record that already exists.  In that event you can just disregard the error.  Likewise, we may attempt to rename a table that may have been left over from older systems.  If these tables are not found an error may be generated but there is nothing you need to do. It is up to you to review the changes and determine if you need to make further changes to get your system to function correctly. That is one reason we urge you to save all messages.<br /><br />";
	echo '<a href="rndb_upgrade_nukesentinel.php" >Continue on to NukeSentinel&trade; Upgrade</a><br /><br />';
	echo '<a href="http://rnwiki.ravennuke.com/wiki/RavenNuke2:Installation">Return to the How to Install Documentation</a><p>&nbsp;</p>';
}
else {
	init($config_value);
}
/*
 * ONLY FUNCTIONS AFTER THIS COMMENT
 */
function init($config_value) {
	global $rndb_valid_versions;
	echo
	'<h1>MUST READ!!</h1>
	<span class="c1">This program updates your RavenNuke&trade; core tables from versions ';
	$version_cnt = count($rndb_valid_versions);
	$i = 0;
	foreach($rndb_valid_versions as $key => $values) {
		if ($i == $version_cnt - 1) echo ' and ';
		echo $key;
		if ($i != $version_cnt - 1) echo ', ';
		$i++;
	}
	echo ' to version ';
	echo RAVENNUKE_VERSION_CONFIG;
	echo ' &nbsp;If you are not already updated to the latest NukeSentinel&trade; version ';
	echo NUKESENTINEL_VERSION;
	echo ' you will also need to run the separate NukeSentinel&trade; database upgrade (but if you are on that release or higher, you do not need to). &nbsp;Support for this program is available thru the forums at <a href="http://www.ravenphpscripts.com">RavenPHPScripts</a>.</span>
	<br /><br />';
	if ($config_value == 'rn2.40.00') {
		echo
		'<h2>Upgrading from 2.40.00</h2>
		<span class="c1">Great!  You were already at the immediate previous version of RavenNuke&trade;.  The only table update that will be done will be to change your RavenNuke&trade; configuration value in the config table. We will also check your Content Plus tables and update them if necessary.</span>';
	}
	else {
		echo
		'<h2>Updating from a version prior to 2.40.00</h2>
		<span class="c1">If you are upgrading from any version <b>prior to 2.20.00</b>, be aware that, starting with version 2.20.00  we made several internal table changes and suggested deletions. First, we have removed the need for table &quot;nuke_cities&quot; from core RavenNuke&trade;.  Its functions have been replaced by another table.  If you have no other modules, hacks, blocks, etc. that use this table, we highly recommend dropping it (you can use phpMyAdmin to do this).
		<br /><br />
		Also starting in version 2.20.00, we substantially revised the table nuke_headlines.  In case you have used headlines administration to modify this table for RSS feeds of your own, we are renaming it and saving it in your database as $prefix_pre220_headlines.  If you have no use for the data in the table you can safely drop it.  Otherwise you can use a tool such as phpMyAdmin to merge your data with that in the new nuke_headlines.  However, realize that much of the data in the previous standard version of nuke_headlines was incorrect.
		<br /><br />
		In addition, starting with 2.10.00 the nuke_bannerclient table was replaced with a nuke_banner_clients table.  Since some sites may have data in the old bannerclient table, we are leaving that table in place, but you will need to move any data over to banner_clients in order to use it.
		<br /><br />
		And, last but not least, the modules HTML Newsletter, GCalendar have become core to RavenNuke&trade;, so this upgrade scripts attempts to add the necessary tables, but ONLY if they do not already exist.  If you already have one of these great modules installed on your current version of RavenNuke&trade;, you will need to run their separate installers within the their respective /AddOnFiles directory to perform any needed upgrades.  This is also explained within the on-line wiki manual on the &quot;Upgrading&quot; page.
		<br /><br />
		You are strongly urged to print the output from this program or capture it to a file to assist in diagnosing any problems that occur when you try to run the distribution.  Also, review the detailed <a href="http://rnwiki.ravennuke.com/wiki/RavenNuke2:Release_Notes">Release Notes</a> FIRST to gain an understanding of the impacts of upgrading.
		<br /><br /></span>';
	}
	echo '<form name="rndb" action ="rndb_upgrade.php" method = "post">';
	echo '<input name="config_value" type="hidden" value="'.$config_value.'" />';
	echo '<br />Your RavenNuke&trade; configuration value was detected as ' . $config_value . '<br />(This is the value that is in the "Version_Num" field of your config table.)<br /><br />';
	echo 'Please use the update button to complete the updating process. <br />';
	echo '<input type="hidden" name = "update" value = "1" /><br />';
	echo '<input type="submit" name = "submit" value = "Update" /><br /><br />';
	echo '<a href="rndb_upgrade_nukesentinel.php" >Continue on to NukeSentinel&trade; Upgrade</a><br /><br />';
	echo '<a href="http://rnwiki.ravennuke.com/wiki/RavenNuke2:Installation">Return to the How to Install Documentation</a><p>&nbsp;</p>';
	echo '</form>';
}
/**
 * Performs the physical SQL calls and collection of errors encountered
 */
function sqlexec($sql) {
	global $prefix, $db, $user_prefix, $dbErrors;
	$db->sql_query($sql);
	if (mysql_error()) {
		echo "<br /><span class=\"c2\"><b>$sql FAILED</b></span>.  &nbsp;&nbsp;&nbsp;MySQL reported: ".mysql_error();
		$dbErrors++;
	}
	else {
		echo  "<br /><span class=\"c1\"><b>$sql Succeeded</b></span>.";
		echo "<br />";
	}
	return;
}
/**
 * Performs the adding of indexes for the stats tables per Mantis 0001086.
 * Since we are attempting to add Primary Keys the indexes MUST be unique.
 * Older versions of these tables usually have at least one convoluted table with duplicate keys (the combination we are adding) and will fail.
 * This function will test the table for the duplicate keys and if duplicates are found then the table is sorted by the key, `hits` DESC.
 * Then the ALTER IGNORE TABLE syntax is used instead of just ALTER TABLE to accept the first (max) record and disregard the rest.
 ******
 ** Per Mantis issue 1254 we decided to pull this from v2.30.00 release.
 ** Since we haven't identified what the root cause is, we can't be sure that this will fix ALL situations.
 ** Regardless, the virgin install routine DOES create unique indexes.
 ******
 function fixStatsTables($table, $selectVars) {
 global $db, $prefix;
 $table = '`' . $prefix . '_' . $table . '`';
 $sql = "DESCRIBE $table;";
 $result = sqlexec($sql);
 if ($db->sql_numrows($result) > 0) {
 $cnt = 0;
 while ( $row = $db->sql_fetchrow($result) ) {
 if ($row['Key']=='PRI') {
 echo 'Primary key already exists for ' . $table . ' => ' . $row['Field'] . '<br />';
 $cnt++;
 }
 }
 if ($cnt > 0) {
 $cnt = 0;
 return; // There is already a Primary index on this table
 die(); // Just in case
 }
 }
 $sql = 'SELECT ' . $selectVars . ', MAX(`hits`) AS `hits`'
 . ' FROM ' . $table
 . ' WHERE `hits` > 0'
 . ' GROUP BY ' . $selectVars;
 $result = sqlexec($sql);
 $ignoreKey = ' ';
 $sql  = '';
 if ($db->sql_numrows($result) > 0) {
 $ignoreKey = ' IGNORE ';
 $updsqlArray = array();
 while ( $row = $db->sql_fetchrow($result) ) {
 $whereClause = '';
 $updsql = '';
 // Create UPDATE statements from result set to be run AFTER the ALTER IGNORE statement
 // MySQL does not allow you to chain statements so the array is needed
 $updsql .= 'UPDATE ' . $table . ' ';
 $updsql .= "SET `hits`=${row['hits']} ";
 if (stristr($selectVars, 'hour')) $whereClause = "`year`=${row['year']} AND `month`=${row['month']} AND `date`=${row['date']} AND `hour`=${row['date']}";
 else if (stristr($selectVars, 'date')) $whereClause = "`year`=${row['year']} AND `month`=${row['month']} AND `date`=${row['date']}";
 else if (stristr($selectVars, 'month')) $whereClause = "`year`=${row['year']} AND `month`=${row['month']}";
 else if (stristr($selectVars, 'year')) $whereClause = "`year`=${row['year']}";
 $updsql .= 'WHERE ' . $whereClause . ';';
 $updsqlArray[] = $updsql;
 $updsql .= "\r\n";
 //echo '$whereClause = ' . nl2br($whereClause) . '<br />';
 }
 }
 // If ALTER IGNORE is called then only the first instance is kept - removes dupes.
 $sql = 'ALTER' . $ignoreKey . 'TABLE ' . $table . ' ADD PRIMARY KEY (' . $selectVars . ');';
 sqlexec($sql);
 // If dupes were found then UPDATE them with the correct values using the $updsqlArray
 if (isset($updsqlArray) && is_array($updsqlArray)) {
 $updsqlCount = count($updsqlArray);
 for ($i=0; $i<$updsqlCount; $i++) {
 sqlexec($updsqlArray[$i]);
 }
 }
 }
 */


/**
 * Performs updates to try and do each time
 */
function doAlways() {
	global $prefix, $db, $user_prefix, $dbErrors;
	$sql = 'UPDATE '.$prefix.'_config SET
	Version_Num=\''.RAVENNUKE_VERSION_CONFIG.'\'';
	sqlexec($sql);
	$sql = "UPDATE      ".$prefix."_bbconfig SET config_value='".PHPBB_VERSION."' WHERE config_name='version'";
	sqlexec($sql);
	// these lines is being added back in January 2008
	/*
	 * montego - made adjustment to just always check for it and fix if needed
	 */
	if (!$db->sql_query('SELECT requestor FROM '.$user_prefix.'_users_temp LIMIT 0')) {;
	$sql = 'ALTER TABLE '.$user_prefix.'_users_temp ADD `requestor` varchar(25)';
	sqlexec($sql);
	}
	/*
	 * montego - added on 3/28/2008 for 2.30.00 - looks like we may have missed inserting this value into the
	 * bbconfig table, so check if that value is there and it not, add it.
	 */
	$result = $db->sql_query('SELECT config_value FROM '.$prefix.'_bbconfig WHERE config_name = \'search_min_chars\' LIMIT 1');
	if ($db->sql_numrows($result) < 1) {
		$sql = 'INSERT INTO `'.$prefix.'_bbconfig` VALUES (\'search_min_chars\', \'3\')';
		sqlexec($sql);
	}
	/*
	 * NSN Groups - montego - decided that we need to always check for the NSN Groups properties as there have
	 * been too many instances in the forums where folks have forgotten to run that step along the way.
	 */
	// First check the NSN GR Tables - if they already exist, we'll just assume that they are the latest
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_nsngr_config LIMIT 0')) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsngr_config` (`config_name` varchar(255) NOT NULL default \'\', `config_value` text NOT NULL, PRIMARY KEY (`config_name`)) TYPE=MyISAM';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsngr_config` VALUES (\'perpage\', \'50\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsngr_config` VALUES (\'date_format\', \'Y-m-d\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsngr_config` VALUES (\'send_notice\', \'1\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsngr_config` VALUES (\'version_number\', \'1.7.1\')';
		sqlexec($sql);
	}
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_nsngr_groups LIMIT 0')) {
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_nsngr_groups` ( `gid` int(11) NOT NULL auto_increment, `gname` varchar(32) NOT NULL default \'\', `gdesc` text NOT NULL, `gpublic` tinyint(1) NOT NULL default \'0\', `glimit` int(11) NOT NULL default \'0\', `phpBB` int(11) NOT NULL default \'0\', `muid` int(11) NOT NULL default \'0\', PRIMARY KEY  (`gid`) ) TYPE=MyISAM';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsngr_groups` VALUES (1, \'Moderators\', \'Moderators of this Forum\', 0, 0, 3, 2)';
		sqlexec($sql);
	}
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_nsngr_users LIMIT 0')) {
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_nsngr_users` (`gid` int(11) NOT NULL default \'0\', `uid` int(11) NOT NULL default \'0\', `uname` varchar(25) NOT NULL default \'\', `trial` tinyint(1) NOT NULL default \'0\', `notice` tinyint(1) NOT NULL default \'0\', `sdate` int(14) NOT NULL default \'0\', `edate` int(14) NOT NULL default \'0\', PRIMARY KEY (`gid`,`uid`,`uname`) ) TYPE=MyISAM';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsngr_users` VALUES (1, 2, \'\', 0, 0, 2005, 0)';
		sqlexec($sql);
	}
	// Next check for presence of the required "groups" properties.
	if (!$db->sql_query('SELECT groups FROM '.$prefix.'_blocks LIMIT 0')) {;
	$sql = 'ALTER TABLE `'.$prefix.'_blocks` ADD `groups` TEXT NOT NULL AFTER `view`';
	sqlexec($sql);
	}
	if (!$db->sql_query('SELECT groups FROM '.$prefix.'_message LIMIT 0')) {;
	$sql = 'ALTER TABLE `'.$prefix.'_message` ADD `groups` TEXT NOT NULL AFTER `view`';
	sqlexec($sql);
	}
	if (!$db->sql_query('SELECT groups FROM '.$prefix.'_modules LIMIT 0')) {;
	$sql = 'ALTER TABLE `'.$prefix.'_modules` ADD `groups` TEXT NOT NULL AFTER `view`';
	sqlexec($sql);
	}
	// Make sure the Groups module is in the _modules table
	if ($db->sql_numrows($db->sql_query('SELECT mid FROM '.$prefix.'_modules WHERE title = \'Groups\'')) == 0) {
		$sql = 'INSERT INTO `'.$prefix.'_modules` VALUES(NULL, \'Groups\', \'Groups\', 1, 0, \'\', 1, 0, \'\')';
		sqlexec($sql);
	}
	/*
	 * montego - since HTML Newsletter, GCalendar, nukeFEED, and Mailer are truly a part of "core",
	 * given that the files for these modules are already integrated into the core directory structure,
	 * we should just let the upgrade script ensure that these tables are here, especially if
	 * they would be new.  Since it is possible, too, that an installation already has a prior
	 * version of these two modules installed, and that both modules provide upgrade installers,
	 * on-line wiki how to install documentation will just have to point to these separate installers IMO for
	 * any upgrades.
	 */
	/*
	 * TegoNuke(tm) Mailer
	 */
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_mail_config LIMIT 0')) {
		$sql = 'CREATE TABLE '.$prefix.'_mail_config (active TINYINT(1) NOT NULL default \'0\', '
		. 'mailer TINYINT(1) NOT NULL default \'1\', smtp_host varchar(255) NOT NULL default \'\', '
		. 'smtp_helo varchar(255) NOT NULL default \'\', smtp_port INT(10) NOT NULL default \'25\', '
		. 'smtp_auth TINYINT(1) NOT NULL default \'0\', smtp_uname varchar(255) NOT NULL default \'\', '
		. 'smtp_passw varchar(255) NOT NULL default \'\', sendmail_path varchar(255) NOT NULL default \'/usr/sbin/sendmail\', '
		. 'qmail_path varchar(255) NOT NULL default \'/var/qmail/bin/sendmail\', PRIMARY KEY  (mailer)) TYPE=MyISAM'
		;
		sqlexec($sql);
		$sql = 'INSERT INTO '.$prefix.'_mail_config VALUES (\'0\', \'1\', \'smtp.yourdomain.tld\', '
		. '\'smtp.yourdomain.tld\', \'25\', \'0\', \'user@yourdomain.tld\', \'userpassword\', \'/usr/sbin/sendmail\', '
		. '\'/var/qmail/bin/sendmail\')'
		;
		sqlexec($sql);
	}
	/*
	 * GCalendar
	 */
	if ($db->sql_query('SELECT 1 FROM '.$prefix.'_gcal_config LIMIT 0')) {
		echo '<br /><span class=\"c2\"><b>GCalendar Already Installed:</b></span> You may need to run the GCalendar installer to upgrade - see on-line wiki manual for details.<br />';
	} else {
		$sql = "CREATE TABLE IF NOT EXISTS `".$prefix."_gcal_category` (`id` int(11) NOT NULL auto_increment,`name` varchar(128) NOT NULL default '', PRIMARY KEY  (`id`)) TYPE=MyISAM";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_category` VALUES (1, 'Unfiled')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_category` VALUES (2, 'Show')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_category` VALUES (3, 'Birthday')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_category` VALUES (4, 'Release Date')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_category` VALUES (5, 'Anniversary')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_category` VALUES (6, 'Site Event')";
		sqlexec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `".$prefix."_gcal_config` (`id` int(11) NOT NULL auto_increment,`title` varchar(128) NOT NULL default 'Calendar of Events',`image` varchar(255) NOT NULL default '',`min_year` int(10) unsigned NOT NULL default '2006',`max_year` int(10) unsigned NOT NULL default '2037',`user_submit` enum('off','members','anyone','groups') NOT NULL default 'off',`req_approval` tinyint(1) NOT NULL default '1',`allowed_tags` text NOT NULL,`allowed_attrs` text NOT NULL,`version` varchar(16) NOT NULL default '',`time_in_24` tinyint(1) NOT NULL default '0',`short_date_format` varchar(16) NOT NULL default '',`reg_date_format` varchar(16) NOT NULL default '',`long_date_format` varchar(16) NOT NULL default '', `first_day_of_week` tinyint(1) NOT NULL default '0',`auto_link` tinyint(1) NOT NULL default '0',`location_required` tinyint(1) NOT NULL default '0',`details_required` tinyint(1) NOT NULL default '0',`email_notify` tinyint(1) NOT NULL default '0',`email_to` varchar(255) NOT NULL default '',`email_subject` varchar(255) NOT NULL default '',`email_msg` varchar(255) NOT NULL default '',`email_from` varchar(255) NOT NULL default '',`show_cat_legend` tinyint(1) NOT NULL default '1',`wysiwyg` tinyint(1) NOT NULL default '0',`user_update` tinyint(1) NOT NULL default '0',`weekends` SET( '0', '1', '2', '3', '4', '5', '6' ) NOT NULL DEFAULT '0,6',`rsvp` ENUM( 'off', 'on', 'email' ) NOT NULL DEFAULT 'off',`rsvp_email_subject` VARCHAR( 255 ) NOT NULL DEFAULT 'Event RSVP Notification', `groups_submit` TEXT NOT NULL , `groups_no_approval` TEXT NOT NULL, PRIMARY KEY  (`id`)) TYPE=MyISAM";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_config` VALUES (1, 'Calendar of Events', 'images/admin/gcalendar.gif', 2006, 2037,'members', 1, 'a,b,i,img','href,src,border,alt,title', '1.7.0', 0, '%m/%d', '%B %d, %Y', '%A, %B %d, %Y', 0, 1, 0,0, 0, 'admin@yoursite.com', 'New GCalendar Event', 'A new GCalendar event was submitted.', 'admin@yoursite.com', 1, 1, 1, '0,6', 'off', 'Event RSVP Notification', '', '')";
		sqlexec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `".$prefix."_gcal_event` (`id` int(11) NOT NULL auto_increment,`title` varchar(255) NOT NULL default '',`no_time` tinyint(1) NOT NULL default '1',`start_time` time NOT NULL default '00:00:00',`end_time` time NOT NULL default '00:00:00',`location` text NOT NULL,`category` int(11) NOT NULL default '0',`repeat_type` enum('none','daily','weekly','monthly','yearly') NOT NULL default 'none',`details` text NOT NULL,`interval_val` int(11) NOT NULL default '0',`no_end_date` tinyint(1) NOT NULL default '1',`start_date` date NOT NULL default '0000-00-00',`end_date` date NOT NULL default '0000-00-00',`weekly_days` set('0','1','2','3','4','5','6') NOT NULL default '',`monthly_by_day` tinyint(1) NOT NULL default '0',`submitted_by` varchar(25) NOT NULL default '',`approved` tinyint(1) NOT NULL default '0',`rsvp` ENUM( 'off', 'on', 'email' ) NOT NULL DEFAULT 'off',PRIMARY KEY  (`id`),KEY `approved` (`approved`),KEY `start_date` (`start_date`),KEY `repeat_type` (`repeat_type`)) TYPE=MyISAM";
		sqlexec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `".$prefix."_gcal_rsvp` (`id` int(11) NOT NULL auto_increment,`event_id` int(11) NOT NULL,`user_id` int(11) NOT NULL,PRIMARY KEY  (`id`), KEY `event_id` (`event_id`,`user_id`)) TYPE=MyISAM";
		sqlexec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `".$prefix."_gcal_exception` (`id` int(11) NOT NULL auto_increment, `event_id` int(11) NOT NULL, `date` date NOT NULL default '0000-00-00', PRIMARY KEY (`id`), KEY `event_id` (`event_id`), KEY `date` (`date`)) TYPE=MyISAM";
		sqlexec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `".$prefix."_gcal_cat_group` (`id` int(11) NOT NULL auto_increment, `cat_id` int(11) NOT NULL, `group_id` int(11) NOT NULL, PRIMARY KEY (`id`), KEY `cat_id` (`cat_id`), KEY `group_id` (`group_id`)) TYPE=MyISAM";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_cat_group` VALUES (NULL, 1, -1)";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_cat_group` VALUES (NULL, 2, -1)";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_cat_group` VALUES (NULL, 3, -1)";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_cat_group` VALUES (NULL, 4, -1)";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_cat_group` VALUES (NULL, 5, -1)";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_gcal_cat_group` VALUES (NULL, 6, -1)";
		sqlexec($sql);
		// Make sure the GCalendar module is in the _modules table
		if ($db->sql_numrows($db->sql_query('SELECT mid FROM '.$prefix.'_modules WHERE title = \'GCalendar\'')) == 0) {
			$sql = 'INSERT INTO `'.$prefix.'_modules` VALUES(NULL, \'GCalendar\', \'GCalendar\', 1, 0, \'\', 1, 0, \'\')';
			sqlexec($sql);
		}
	}
	/*
	 * HTML Newsletter
	 */
	if ($db->sql_query('SELECT 1 FROM '.$prefix.'_hnl_cfg LIMIT 0')) {
		echo '<br /><span class=\"c2\"><b>HTML Newsletter Already Installed:</b></span> You may need to run the HTML Newsletter installer to upgrade - see on-line wiki manual for details.<br />';
	} else {
		$sql = "CREATE TABLE IF NOT EXISTS `".$prefix."_hnl_categories` (cid int(11) NOT NULL auto_increment,ctitle varchar(50) NOT NULL default '',cdescription text NOT NULL,cblocklimit int(4) NOT NULL default '10',PRIMARY KEY (cid)) TYPE=MyISAM";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_categories` (cid, ctitle, cdescription, cblocklimit) VALUES (1, '*Unassigned*', 'This is a catch-all category where newsletters can default to or if all other categories are removed.  Do NOT remove this category!  This category of newsletters are only shown to the Admins.  ', 5)";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_categories` (cid, ctitle, cdescription, cblocklimit) VALUES (2, 'Archived Newsletters', 'This category is for newsletter subscribers.', 5)";
		$sql = "INSERT INTO `".$prefix."_hnl_categories` (cid, ctitle, cdescription, cblocklimit) VALUES (3, 'Archived Mass Mails', 'This category is used for mass mails.', 5)";
		sqlexec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `".$prefix."_hnl_newsletters` (nid int(11) NOT NULL auto_increment,cid int(11) NOT NULL default '1',topic varchar(100) NOT NULL default '',sender varchar(20) NOT NULL default '',filename varchar(32) NOT NULL default '',datesent date default NULL,view int(1) NOT NULL default '0',groups text NOT NULL,hits int(11) NOT NULL default '0',PRIMARY KEY  (nid),KEY cid (cid)) TYPE=MyISAM";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_newsletters` (nid, cid, topic, sender, filename, datesent, view, groups, hits) VALUES (1, 1, 'PREVIEW Newsletter File', 'Admin', 'tmp.php', '0000-00-00', 99, '', 0)";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_newsletters` (nid, cid, topic, sender, filename, datesent, view, groups, hits) VALUES (2, 1, 'Tested Email Temporary File', 'Admin', 'testemail.php', '0000-00-00', 99, '', 0)";
		sqlexec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `".$prefix."_hnl_cfg` (`cfg_nm` varchar(255) NOT NULL default '', `cfg_val` longtext NOT NULL, PRIMARY KEY (`cfg_nm`)) TYPE=MyISAM";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('blk_lmt', '10')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('debug_mode', 'ERROR')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('debug_output', 'DISPLAY')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('dl_module', 'downloads')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('latest_downloads', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('latest_forums', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('latest_links', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('latest_news', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('latest_reviews', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('nsn_groups', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('scroll', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('scroll_amt', '2')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('scroll_delay', '100')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('scroll_height', '180')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('show_blocks', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('show_categories', '1')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('show_dates', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('show_hits', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('show_sender', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('version', '01.03.02')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('wysiwyg_on', '0')";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_hnl_cfg` VALUES ('wysiwyg_rows', '30')";
		sqlexec($sql);
		// Make sure the HTML Newsletter module is in the _modules table
		if ($db->sql_numrows($db->sql_query('SELECT mid FROM '.$prefix.'_modules WHERE title = \'HTML_Newsletter\'')) == 0) {
			$sql = 'INSERT INTO `'.$prefix.'_modules` VALUES(NULL, \'HTML_Newsletter\', \'Newsletters\', 1, 0, \'\', 1, 0, \'\')';
			sqlexec($sql);
		}
	}
	/*
	 * nukeFEED
	 */
	$numrows = $db->sql_numrows($db->sql_query('SELECT 1 FROM '.$prefix.'_modules WHERE title=\'Feeds\''));
	if ($numrows == 0) {
		$sql = "INSERT INTO `".$prefix."_modules` (`mid`, `title`, `custom_title`, `active`, `view`, `inmenu`, `mod_group`, `admins`) VALUES (NULL, 'Feeds', 'Feeds', 1, 0, 0, 0, '')";
		sqlexec($sql);
	}
	if ($db->sql_query('SELECT 1 FROM '.$prefix.'_seo_config LIMIT 0')) {
		echo '<br /><span class=\"c2\"><b>nukeFEED already installed:</b></span><br />';
	} else {
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_seo_config` (`config_type` varchar(150) NOT NULL, `config_name` varchar(150) NOT NULL, `config_value` text NOT NULL, PRIMARY KEY  (`config_type`,`config_name`) ) TYPE=MyISAM';
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'use_fb', '1');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'feedburner_url', 'http://feeds.feedburner.com');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'version_check', '0');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'version_newest', '1.1.1');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'version_number', '1.1.1');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'version_url', 'http://nukeseo.com/modules.php?name=Downloads');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'version_notes', '');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'show_circgraph', '1');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'show_feedcount', '1');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'feedcount_body', 'A6A6A6');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_config` VALUES ('Feeds', 'feedcount_text', '000000');";
		sqlexec($sql);
		// seo disabled modules
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_seo_disabled_modules` (`title` varchar(100) NOT NULL, `seo_module` varchar(100) NOT NULL, PRIMARY KEY  (`title`,`seo_module`)) TYPE=MyISAM';
		sqlexec($sql);
		// Feeds
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_seo_feed` (`fid` int(6) NOT NULL auto_increment, `content` varchar(20) NOT NULL, `name` varchar(20) NOT NULL, `level` varchar(20) NOT NULL, `lid` int(6) NOT NULL, `title` varchar(50) NOT NULL, `desc` text NOT NULL, `order` varchar(20) NOT NULL, `howmany` char(3) NOT NULL, `active` int(1) NOT NULL, `desclimit` varchar(5) NOT NULL, `securitycode` varchar(50) NOT NULL, `cachetime` varchar(6) NOT NULL, `feedburner_address` varchar(100) NOT NULL, PRIMARY KEY  (`fid`), KEY `content` (`content`,`title`)) TYPE=MyISAM';
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_feed` VALUES (1, 'News', 'News', 'module', 0, '".$sitename." Forums', '', 'recent', '10', 1, '', '', '', '');";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_feed` VALUES (2, 'Forums', 'Forums', 'module', 0, '".$sitename." News', '', 'recent', '10', 1, '', '', '', '');";
		sqlexec($sql);
		// Subscriptions
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_seo_subscriptions` ( `sid` int(6) NOT NULL auto_increment, `type` varchar(255) NOT NULL, `name` varchar(60) NOT NULL, `tagline` varchar(60) NOT NULL, `image` varchar(255) NOT NULL, `icon` varchar(255) NOT NULL, `url` varchar(255) NOT NULL, `active` int(1) NOT NULL, PRIMARY KEY  (`sid`)) TYPE=MyISAM';
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (1, 'aggregator', '01 Google Reader', 'Add to Google', 'images/nukeFEED/subscribe/add-to-google-plus.gif', '', 'http://fusion.google.com/add?feedurl={URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (2, 'aggregator', '02 My Yahoo!', 'Add to My Yahoo!', 'images/nukeFEED/subscribe/myYahoo.gif', '', 'http://add.my.yahoo.com/rss?url={URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (3, 'aggregator', '03 My AOL', 'Add to My AOL', 'images/nukeFEED/subscribe/myAOL.gif', '', 'http://feeds.my.aol.com/add.jsp?url={URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (4, 'aggregator', '04 My MSN', 'Add to My MSN', 'images/nukeFEED/subscribe/myMSN.gif', '', 'http://my.msn.com/addtomymsn.armx?id=rss&ut={URL}&ru={NUKEURL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (5, 'aggregator', '05 BlogLines', 'Subscribe with Bloglines', 'images/nukeFEED/subscribe/bloglines.gif', '', 'http://www.bloglines.com/sub/{URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (6, 'aggregator', '06 netvibes', 'Add to netvibes', 'images/nukeFEED/subscribe/netvibes.gif', '', 'http://www.netvibes.com/subscribe.php?url={URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (7, 'aggregator', '07 NewsGator', 'Subscribe in NewsGator Online', 'images/nukeFEED/subscribe/newsgator.gif', '', 'http://www.newsgator.com/ngs/subscriber/subext.aspx?url={URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (8, 'aggregator', '08 Pageflakes', 'Subscribe with PageFlakes', 'images/nukeFEED/subscribe/pageflakes.gif', '', 'http://www.pageflakes.com/subscribe.aspx?url={URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (9, 'aggregator', '09 Rojo', 'Subscribe in Rojo', 'images/nukeFEED/subscribe/addtorojo.gif', '', 'http://www.rojo.com/add-subscription?resource={URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (10, 'aggregator', '10 Protopage', 'Add this site to your Protopage', 'images/nukeFEED/subscribe/protopage.gif', '', 'http://www.protopage.com/add-button-site?url={URL}&label={TITLE}&type=feed', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (11, 'aggregator', '11 Newsburst', 'Add to Newsburst', 'images/nukeFEED/subscribe/newsburst.gif', '', 'http://www.newsburst.com/Source/?add={URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (12, 'aggregator', '12 NewsAlloy', 'Subscribe in NewsAlloy', 'images/nukeFEED/subscribe/newsalloy.gif', '', 'http://www.newsalloy.com/?rss={URL}', 1);";
		sqlexec($sql);
		$sql = "INSERT INTO `".$prefix."_seo_subscriptions` VALUES (13, 'aggregator', '13 Blogarithm', 'Add to Blogarithm', 'images/nukeFEED/subscribe/blogarithm.gif', '', 'http://www.blogarithm.com/subrequest.php?BlogURL={URL}', 1);";
		sqlexec($sql);
	}

	return;
}
/**
 * Performs updates needed to go from 2.02.00 to 2.02.02
 * NOTE: validated against core SQL files on 3/27/2008
 */
function rn20200() {
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;

	$sql = "ALTER TABLE ".$prefix."_bbsearch_results ADD COLUMN search_time int(11) DEFAULT '0' NOT NULL";
	sqlexec($sql);
	$sql = "ALTER TABLE ".$prefix."_subscriptions CHANGE `userid` `userid` INT(10) NOT NULL DEFAULT '0'";
	sqlexec($sql);
	$sql = "INSERT INTO ".$prefix."_bbconfig  (config_name, config_value) VALUES ('search_flood_interval', '15')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$prefix."_bbconfig  (config_name, config_value) VALUES ('rand_seed', '0')";
	sqlexec($sql);
	$sql = "UPDATE      ".$prefix."_bbconfig SET config_value='0' WHERE config_name='record_online_users'";
	sqlexec($sql);
	$sql = "UPDATE      ".$prefix."_bbconfig SET config_value='0' WHERE config_name='record_online_date'";
	sqlexec($sql);
	$sql = "UPDATE      ".$prefix."_modules  SET custom_title='Authors and Articles' WHERE title='Articles and Authors List'";
	sqlexec($sql);

	$already_ran = true;
	return;
}
/**
 * Performs updates needed to go from 2.02.02 to 2.10.00
 * NOTE: validated against core SQL files on 3/28/2008
 */
function rn20202() {
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;
	/*
	 * Changes related to the Patch Series
	 */
	$sql = "ALTER TABLE ".$user_prefix."_users CHANGE bio bio TINYTEXT NULL DEFAULT NULL";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_bbsessions DROP PRIMARY KEY";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_bbsessions DROP INDEX session_id_ip_user_id";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_bbsessions ADD INDEX session_ip (session_ip)";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_bbsessions ADD INDEX session_id (session_id)";
	sqlexec($sql);

	$sql = "DELETE FROM ".$prefix."_bbsessions";
	sqlexec($sql);

	$sql = "DELETE FROM ".$prefix."_bbsessions_keys";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$user_prefix."_users ADD `user_login_tries` smallint(5) unsigned NOT NULL default '0' AFTER `last_ip`; ";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$user_prefix."_users ADD `user_last_login_try` int(11) NOT NULL default '0' AFTER `user_login_tries`; ";
	sqlexec($sql);
	/*
	 * Changes to add the newer Advertising module
	 */
	$sql = "ALTER TABLE ".$prefix."_banner ADD `name` varchar(50) NOT NULL default '' AFTER `cid`";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_banner ADD `position` int(10) NOT NULL default '0' AFTER `dateend`";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_banner ADD `ad_class` varchar(5) NOT NULL default '' AFTER `active`";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_banner ADD `ad_code` text NOT NULL AFTER `ad_class`";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_banner ADD `ad_width` int(4) default '0' AFTER `ad_code`";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_banner ADD `ad_height` int(4) default '0' AFTER `ad_width`";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_banner DROP `type`";
	sqlexec($sql);

	$sql = "ALTER TABLE ".$prefix."_banner ADD INDEX `bid` (bid);";
	sqlexec($sql);

	$sql = "CREATE TABLE ".$prefix."_banner_clients (`cid` int(11) NOT NULL auto_increment, `name` varchar(60) NOT NULL default '', `contact` varchar(60) NOT NULL default '', `email` varchar(60) NOT NULL default '', `login` varchar(10) NOT NULL default '', `passwd` varchar(10) NOT NULL default '', `extrainfo` text NOT NULL default '', PRIMARY KEY  (`cid`), KEY `cid` (`cid`)) TYPE=MyISAM AUTO_INCREMENT=1";
	sqlexec($sql);

	$sql = "CREATE TABLE ".$prefix."_banner_plans (`pid` int(10) NOT NULL auto_increment, `active` tinyint(1) NOT NULL default '0', `name` varchar(255) NOT NULL default '', `description` text NOT NULL default '', `delivery` varchar(10) NOT NULL default '', `delivery_type` varchar(25) NOT NULL default '', `price` varchar(25) NOT NULL default '0', `buy_links` text NOT NULL default '', PRIMARY KEY  (`pid`)) TYPE=MyISAM AUTO_INCREMENT=1";
	sqlexec($sql);

	$sql = "CREATE TABLE ".$prefix."_banner_positions (`apid` int(10) NOT NULL auto_increment, `position_number` int(5) NOT NULL default '0', `position_name` varchar(255) NOT NULL default '', PRIMARY KEY  (`apid`), KEY `position_number` (`position_number`)) TYPE=MyISAM AUTO_INCREMENT=3";
	sqlexec($sql);

	$sql = "INSERT INTO ".$prefix."_banner_positions VALUES (1, 0, 'Page Top');";
	sqlexec($sql);

	$sql = "INSERT INTO ".$prefix."_banner_positions VALUES (2, 1, 'Page Header')";
	sqlexec($sql);

	$sql = "CREATE TABLE ".$prefix."_banner_terms (`terms_body` text NOT NULL default '', `country` varchar(255) NOT NULL default '') TYPE=MyISAM";
	sqlexec($sql);

	$sql = 'INSERT INTO '. $prefix.'_banner_terms  VALUES ('. '\'<div align="justify"><strong>Introduction:</strong> This Agreement between you and [sitename] consists of these Terms and Conditions. &quot;You&quot; or &quot;Advertiser&quot; means the entity identified in this enrollment form, and/or any agency acting on its behalf, which shall also be bound by the terms of this Agreement. Please read very carefully these Terms and Conditions.<br /><strong><br />Uses:</strong> You agree that your ads may be placed on (i) [sitename] web site and (ii) Any ads may be modified without your consent to comply with any policy of [sitename]. [sitename] reserves the right to, and in its sole discretion may, at any time review, reject, modify, or remove any ad. No liability of [sitename] and/or its owner(s) shall result from any such decision.<br /><br /></div><div align="justify"><strong>Parties&rsquot; Responsibilities:</strong> You are responsible of your own site and/or service advertised in [sitename] web site. You are solely responsible for the advertising image creation, advertising text and for the content of your ads, including URL links. [sitename] is not responsible for anything regarding your Web site(s) including, but not limited to, maintenance of your Web site(s), order entry, customer service, payment processing, shipping, cancellations or returns.<br /><br /></div><div align="justify"><strong>Impressions Count:</strong> Any hit to [sitename] web site is counted as an impression. Due to our advertising price we don&rsquot;t discriminate from users or automated robots. Even if your access to [sitename] web site and see your own banner ad it will be counted as a valid impression. Only in the case of [sitename] web site administrator, the impressions will not be counted.<br /><br /></div><div align="justify"><strong>Termination, Cancellation:</strong> [sitename] may at any time, in its sole discretion, terminate the Campaign, terminate this Agreement, or cancel any ad(s) or your use of any Target. [sitename] will notify you via email of any such termination or cancellation, which shall be effective immediately. No refund will be made for any reason. Remaining impressions will be stored in a database and you&rsquot;ll be able to request another campaign to complete your inventory. You may cancel any ad and/or terminate this Agreement with or without cause at any time. Termination of your account shall be effective when [sitename] receives your notice via email. No refund will be made for any reason. Remaining impressions will be stored in a database for future uses by you and/or your company.<br /><br /></div><div align="justify"><strong>Content:</strong> [sitename] web site doesn&rsquo;t accepts advertising that contains: (i) pornography, (ii) explicit adult content, (iii) moral questionable content, (iv) illegal content of any kind, (v) illegal drugs promotion, (vi) racism, (vii) politics content, (viii) religious content, and/or (ix) fraudulent suspicious content. If your advertising and/or target web site has any of this content and you purchased an advertising package, you&rsquo;ll not receive refund of any kind but your banners ads impressions will be stored for future use.<br /><br /></div><div align="justify"><strong>Confidentiality:</strong> Each party agrees not to disclose Confidential Information of the other party without prior written consent except as provided herein. &quot;Confidential  Information&quot; includes (i) ads, prior to publication, (ii) submissions or modifications relating to any advertising campaign, (iii) clickthrough rates or other statistics (except in an aggregated form that includes no identifiable information about you), and (iv) any other information designated in writing as &quot;Confidential&quot;. It does not include information that has become publicly known through no breach by a party, or has been (i) independently developed without access to the other party&rsquo;s Confidential Information; (ii) rightfully received from a third party; or (iii) required to be disclosed by law or by a governmental authority.<br /><br /></div><div align="justify"><strong>No Guarantee:</strong> [sitename] makes no guarantee regarding the levels of clicks for any ad on its site. [sitename] may offer the same Target to more than one advertiser. You may not receive exclusivity unless special private contract between [sitename] and you.<br /><br /></div><div align="justify"><strong>No Warranty:</strong> [sitename] MAKES NO WARRANTY, EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION WITH RESPECT TO ADVERTISING AND OTHER SERVICES, AND EXPRESSLY DISCLAIMS THE WARRANTIES OR CONDITIONS OF NONINFRINGEMENT, MERCHANTABILITY AND FITNESS FOR ANY PARTICULAR PURPOSE.<br /><br /></div><div align="justify"><strong>Limitations of Liability:</strong> In no event shall [sitename] be liable for any act or omission, or any event directly or indirectly resulting from any act or omission of Advertiser, Partner, or any third parties (if any). EXCEPT FOR THE PARTIES&rsquo; INDEMNIFICATION AND CONFIDENTIALITY OBLIGATIONS HEREUNDER, (i) IN NO EVENT SHALL EITHER PARTY BE LIABLE UNDER THIS AGREEMENT FOR ANY CONSEQUENTIAL, SPECIAL, INDIRECT, EXEMPLARY, PUNITIVE, OR OTHER DAMAGES WHETHER IN CONTRACT, TORT OR ANY OTHER LEGAL THEORY, EVEN IF SUCH PARTY HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES AND NOTWITHSTANDING ANY FAILURE OF ESSENTIAL PURPOSE OF ANY LIMITED REMEDY AND (ii) [sitename] AGGREGATE LIABILITY TO ADVERTISER UNDER THIS AGREEMENT FOR ANY CLAIM IS LIMITED TO THE AMOUNT PAID TO [sitename] BY ADVERTISER FOR THE AD GIVING RISE TO THE CLAIM. Each party acknowledges that the other party has entered into this Agreement relying on the limitations of liability stated herein and that those limitations are an essential basis of the bargain between the parties. Without limiting the foregoing and except for payment obligations, neither party shall have any liability for any failure or delay resulting from any condition beyond the reasonable control of such party, including but not limited to governmental action or acts of terrorism, earthquake or other acts of God, labor conditions, and power failures.<br /><br /></div><div align="justify"><strong>Payment:</strong> You agree to pay in advance the cost of the advertising. [sitename] will not setup any banner ads campaign(s) unless the payment process is complete. [sitename] may change its pricing at any time without prior notice. If you have an advertising campaign running and/or impressions stored for future use for any mentioned cause and [sitename] changes its pricing, you&rsquo;ll not need to pay any difference. Your purchased banners fee will remain the same. Charges shall be calculated solely based on records maintained by [sitename]. No other measurements or statistics of any kind shall be accepted by [sitename] or have any effect under this Agreement.<br /><br /></div><div align="justify"><strong>Representations and Warranties:</strong> You represent and warrant that (a) all of the information provided by you to [sitename] to enroll in the Advertising Campaign is correct and current; (b) you hold all rights to permit [sitename] and any Partner(s) to use, reproduce, display, transmit and distribute your ad(s); and (c) [sitename] and any Partner(s) Use, your Target(s), and any site(s) linked to, and products or services to which users are directed, will not, in any state or country where the ad is displayed (i) violate any criminal laws or third party rights giving rise to civil liability, including but not limited to trademark rights or rights relating to the performance of music; or (ii) encourage conduct that would violate any criminal or civil law. You further represent and warrant that any Web site linked to your ad(s) (i) complies with all laws and regulations in any state or country where the ad is displayed; (ii) does not breach and has not breached any duty toward or rights of any person or entity including, without limitation, rights of publicity or privacy, or rights or duties under consumer protection, product liability, tort, or contract theories; and (iii) is not false, misleading, defamatory, libelous, slanderous or threatening.<br /><br /></div><div align="justify"><strong>Your Obligation to Indemnify:</strong> You agree to indemnify, defend and hold [sitename], its agents, affiliates, subsidiaries, directors, officers, employees, and applicable third parties (e.g., all relevant Partner(s), licensors, licensees, consultants and contractors) (&quot;Indemnified Person(s)&quot;) harmless from and against any and all third party claims, liability, loss, and expense (including damage awards, settlement amounts, and reasonable legal fees), brought against any Indemnified Person(s), arising out of, related to or which may arise from your use of the Advertising Program, your Web site, and/or your breach of any term of this Agreement. Customer understands and agrees that each Partner, as defined herein, has the right to assert and enforce its rights under this Section directly on its own behalf as a third party beneficiary.<br /><br /></div><div align="justify"><strong>Information Rights:</strong> [sitename] may retain and use for its own purposes all information you provide, including but not limited to Targets, URLs, the content of ads, and contact and billing information. [sitename] may share this information about you with business partners and/or sponsors. [sitename] will not sell your information. Your name, web site&rsquo;s URL and related graphics shall be used by [sitename] in its own web site at any time as a sample to the public, even if your Advertising Campaign has been finished.<br /><br /></div><div align="justify"><strong>Miscellaneous:</strong> Any decision made by [sitename] under this Agreement shall be final. [sitename] shall have no liability for any such decision. You will be responsible for all reasonable expenses (including attorneys&rsquo; fees) incurred by [sitename] in collecting unpaid amounts under this Agreement. This Agreement shall be governed by the laws of [country]. Any dispute or claim arising out of or in connection with this Agreement shall be adjudicated in [country]. This constitutes the entire agreement between the parties with respect to the subject matter hereof. Advertiser may not resell, assign, or transfer any of its rights hereunder. Any such attempt may result in termination of this Agreement, without liability to [sitename] and without any refund. The relationship(s) between [sitename] and the &quot;Partners&quot;" is not one of a legal partnership relationship, but is one of independent contractors. This Agreement shall be construed as if both parties jointly wrote it.</div>\', "")';

	$db->sql_query($sql);
	if (mysql_error()) {
		echo "<br /><span class=\"c2\"><b>term insert FAILED</b></span>.  &nbsp;&nbsp;&nbsp;MySQL reported: ".mysql_error() . '<br />';
		echo mysql_errno() . '<br />';
		$dbErrors++;
	}
	else{
		echo "<br /><span class=\"c1\"><b>term insert succeeded</b></span>.";
		echo "<br />"; }
		// Make sure the Advertising module is in the _modules table
		if ($db->sql_numrows($db->sql_query('SELECT mid FROM '.$prefix.'_modules WHERE title = \'Advertising\'')) == 0) {
			$sql = 'INSERT INTO `'.$prefix.'_modules` VALUES(NULL, \'Advertising\', \'Advertising\', 0, 0, \'\', 1, 0, \'\')';
			sqlexec($sql);
		}

		$already_ran = true;
		return;
}
/**
 * Performs updates needed to go from 2.10.00 to 2.10.01
 * NOTE: validated against core SQL files on 3/28/2008
 */
function rn21000() {
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;
	$already_ran = true;
	return;  // Nothing changed between these two versions in the database
}
/**
 * Performs updates needed to go from 2.10.01 to 2.20.00
 * NOTE: validated against core SQL files on 3/28/2008
 */
function rn21001() {
	echo "In rn21001<br />";
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;
	/*
	 * The following table is renamed to retain the original headlines data in case you need/want it.
	 * But, there were many of these that were no longer valid, so we replaced them.
	 */
	$sql = "ALTER TABLE      ".$prefix."_headlines RENAME ".$prefix."_pre220_headlines";
	sqlexec($sql);
	// Ok, now re-create the headlines table
	$sql = "
	 CREATE TABLE IF NOT EXISTS `".$prefix."_headlines` ( `hid` int(11) NOT NULL auto_increment, `sitename` varchar(30) NOT NULL default '', `headlinesurl` varchar(200) NOT NULL default '', PRIMARY KEY  (`hid`) ) TYPE=MyISAM";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'RavenPHPScripts', 'http://www.ravenphpscripts.com/backend.php')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'Montego Scripts', 'http://montegoscripts.com/modules.php?name=Feeds&fid=1&type=RSS20')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'Code Authors', 'http://www.code-authors.com/modules.php?name=Feeds&fid=1&type=RSS20')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'Star Wars Rebellion', 'http://www.swrebellion.com/backend.php')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'nukeSEO', 'http://feeds.nukeseo.com/nukeSEO')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'AbsoluteGames', 'http://files.gameaholic.com/agfa.rdf')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'DigitalTheatre', 'http://www.dtheatre.com/backend.php3?xml=yes')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'Freshmeat', 'http://feeds.pheedo.com/freshmeatnet_announcements_global')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'Gnome Desktop', 'http://www.gnomedesktop.org/backend.php')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'HappyPenguin', 'http://happypenguin.org/html/news.rdf')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'HollywoodBitchslap', 'http://hollywoodbitchslap.com/hbs.rdf')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'Learning Linux', 'http://www.learninglinux.com/backend.php')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'Linux.com', 'http://www.linux.com/feature/?theme=rss')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'LinuxCentral', 'http://linuxcentral.com/backend/lcnew.rdf')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'LinuxJournal', 'http://feeds.feedburner.com/linuxjournalcom')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'LinuxWeelyNews', 'http://lwn.net/headlines/rss')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'mozillaZine', 'http://www.mozillazine.org/atom.xml')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'NukeResources', 'http://www.nukeresources.com/backend.php')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'NukeScripts', 'http://www.nukescripts.net/backend.php')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'PDABuzz', 'http://www.pdabuzz.com/tags/feeds/news')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'PHP-Nuke', 'http://phpnuke.org/backend.php')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'PHP.net', 'http://www.php.net/news.rss')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'PHPBuilder', 'http://phpbuilder.com/rss_feed.php')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'PerlMonks', 'http://www.perlmonks.org/headlines.rdf')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'WebReference', 'http://webreference.com/webreference.rdf')";
	sqlexec($sql);
	$sql = "
	INSERT INTO `".$prefix."_headlines` VALUES (NULL, 'Wikipedia Recent Changes', 'http://en.wikipedia.org/w/index.php?title=Special:Recentchanges&feed=atom')";
	sqlexec($sql);
	$already_ran = true;
	return;
	/*
	 * Additional modules were added so make sure they are in the _modules table
	 */
	if ($db->sql_numrows($db->sql_query('SELECT mid FROM '.$prefix.'_modules WHERE title = \'Comments\'')) == 0) {
		$sql = 'INSERT INTO `'.$prefix.'_modules` VALUES(NULL, \'Comments\', \'Comments\', 1, 0, \'\', 0, 0, \'\')';
		sqlexec($sql);
	}
	if ($db->sql_numrows($db->sql_query('SELECT mid FROM '.$prefix.'_modules WHERE title = \'RWS_WhoIsWhere\'')) == 0) {
		$sql = 'INSERT INTO `'.$prefix.'_modules` VALUES(NULL, \'RWS_WhoIsWhere\', \'RWS WhoIsWhere\', 0, 0, \'\', 0, 1, \'\')';
		sqlexec($sql);
	}
}
/**
 * Performs updates needed to go from 2.20.00 to 2.20.01
 * NOTE: validated against core SQL files on 3/28/2008
 */
function rn22000() {
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;
	$already_ran = true;
	return;
}
/**
 * Performs updates needed to go from 2.20.01 to 2.30.00
 */
function rn22001() {
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;
	/*
	 * Legal Module was added
	 * NOTE: $legal_exists was added to avoid false INSERT errors and duplicate config values
	 * It was not used around the CREATE just yet as not sure yet if we need to try and upgrade
	 * an installation from an older version to a newer version, so may need additional code.
	 */
	$legal_old_exists = false;
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_legal LIMIT 0')) {
		echo '<br /><span class=\"c2\"><b>Different Legal Module Already Installed:</b></span> While the new RavenNuke(tm) Legal module will be installed, the old Legal module tables will be retained in case you wish to compare the legal text.  Once you no longer need the old data, you may drop the &lt;prefix&gt;_legal and &lt;prefix&gt;_legal_config tables using phpMyAdmin.<br />';
		$legal_old_exists = true;
	}
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_legal_cfg LIMIT 0')) $legal_exists = true; else $legal_exists = false;
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_legal_cfg` (`contact_email` varchar(255) NOT NULL default \'legal@MySite.com\', `contact_subject` varchar(255) NOT NULL default \'Legal Notice Inquiry\', `country` varchar(255) NOT NULL default \'United States of America\') TYPE=MyISAM';
	sqlexec($sql);
	if (!$legal_exists) {
		$sql = 'INSERT INTO `'.$prefix.'_legal_cfg` VALUES(\'legal@MySite.com\', \'Legal Notice Inquiry\', \'United States of America\')';
		sqlexec($sql);
	}
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_legal_docs` (`did` int(11) NOT NULL auto_increment, `doc_name` varchar(32) NOT NULL, `doc_status` tinyint(4) NOT NULL default \'0\', PRIMARY KEY  (`did`)) TYPE=MyISAM';
	sqlexec($sql);
	if (!$legal_exists) {
		$sql = 'INSERT INTO `'.$prefix.'_legal_docs` VALUES(1, \'notice\', 1)';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_docs` VALUES(2, \'privacy\', 1)';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_docs` VALUES(3, \'terms\', 1)';
		sqlexec($sql);
	}
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_legal_text` (`tid` int(11) NOT NULL auto_increment, `doc_text` text NOT NULL,   PRIMARY KEY  (`tid`)) TYPE=MyISAM';
	sqlexec($sql);
	if (!$legal_exists) {
		$sql = 'INSERT INTO `'.$prefix.'_legal_text` VALUES(1, \'<p>[sitename] authorizes you to view, download, and interact with materials, services, and forums on this website. Unless otherwise specified, the services and downloads provided by [sitename] are for your personal and or commercial use, provided that you retain all copyright and other proprietary notices contained in the original materials.</p>\r\n<p>The materials at [sitename] are copyrighted and any unauthorized use of these materials may violate copyrights and or trademarks of [country], headquarters of the owner of [sitename].</p>\r\n<p>These legal notices are for our protection and yours as well. Please read them carefully.</p>\r\n<p align="right">[date]</p>\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text` VALUES(2, \'Legal Notice\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text` VALUES(3, \'<p style="TEXT-ALIGN: left" align="left"><span style="FONT-WEIGHT: bold">Introduction<br />\r\n<br />\r\n</span>The site editor takes your right to privacy seriously, and wants you to feel comfortable using [sitename]. This privacy policy deals with personally-identifiable information (referred to as &quot;data&quot; below) that may be collected by this site. This policy does not apply to other entities that are not owned or controlled by the site editor, nor to persons that are not employees or agents of the site editor, or that are not under the site editor\'\'s control. Please take time to read this site\'\'s <a href="modules.php?name=Legal&amp;op=terms">terms of use</a>.<br />\r\n<br />\r\n</p>\r\n<div align="left"></div>\r\n<div align="left"></div>\r\n<p style="TEXT-ALIGN: left" align="left"><span style="FONT-WEIGHT: bold">1. Collection of data<br />\r\n<br />\r\n</span>Registration of a user account on this site requires only a valid e-mail address and a user name that has not been used already. You are not required to provide any other information if do not want to. Please be aware that the user name you choose, the e-mail address you provide and other information you provide may render you personally identifiable, and may possibly be displayed on [sitename] intentionally (depending on choices you make during the registration process, or on the way in which the site is configured) or unintentionally (such as, but not limited to, subsequent to a successful act of intrusion by a third party). As on many web sites, the site editor may also automatically receive general information that is contained in server log files, such as your IP address, and cookie information. Information about how advertising may be served on this site (if it is the site editor\'\'s policy to display advertising) is set forth below.<br />\r\n<br />\r\n</p>\r\n<div align="left"></div>\r\n<p style="TEXT-ALIGN: left" align="left"><span style="FONT-WEIGHT: bold">2. Use of data<br />\r\n<br />\r\n</span>Data may be used to customize and improve your user experience on this site. Efforts will be made to prevent your data being made available to third parties unless (ID 1033 0 1) provided for otherwise in this Privacy Policy; (ii) your consent is obtained, such as when you choose to opt-in or opt-out for the sharing of data; (iii) a service provided on our site requires interaction a third party with or is provided by a third party, such as an application service provider; (iv) pursuant to legal action or law enforcement; (v) it is found that your use of this site violates this policy, terms of service, or other usage guidelines, or if it is deemed reasonably necessary by the site editor to protect the site editor\'\'s legal rights and or property; or (vi) this site is purchased by a third party, in which case that third party will be able to use the data in the same manner as set forth in this Policy. In the event you choose to use links that appear on [sitename] to visit other web sites, you are advised to read the privacy policies that appear on those sites.<br />\r\n<br />\r\n</p>\r\n<div align="left"></div>\r\n<p style="TEXT-ALIGN: left" align="left"><span style="FONT-WEIGHT: bold">3. Cookies<br />\r\n<br />\r\n</span>Like many web sites, [sitename] sets and uses cookies to enhance your user experience, such as retaining your personal settings. Advertisements may appear on [sitename] and, if so, may set and access cookies on your computer; such cookies are subject to the privacy policy of the parties providing the advertisement. However, the parties serving the advertising do not have access to this site\'\'s cookies. These parties usually use non-personally-identifiable or anonymous codes to obtain information about your visits to this site. You can visit the <a href="http://www.networkadvertising.org/optout_nonppii.asp">Network Advertising Initiative</a> if you want to find out more information about this practice, and to learn about your options, including your options with regard to the following companies that may serve advertising on this site:<br />\r\n<br />\r\n[<a href="http://www.associateprograms.com/"> AssociatePrograms.com</a> ] [<a title="AdBrite" target="_blank" href="http://www.adbrite.com"> AdBrite</a> ] [ <a href="http://www.cj.com/">Commission Junction</a> ] [ <a href="http://www.doubleclick.net/">DoubleClick</a> ] [ <a href="http://www.linkshare.com/">Linkshare</a> ]<br />\r\n<br />\r\n</p>\r\n<div align="left"></div>\r\n<p style="TEXT-ALIGN: left" align="left"><span style="FONT-WEIGHT: bold">4. Minors<br />\r\n<br />\r\n</span>People aged thirteen or younger are not allowed to become registered users of this site. For more information, please contact <a href="modules.php?name=Legal&amp;op=contact">the webmaster</a>.<br />\r\n<br />\r\n</p>\r\n<div align="left"></div>\r\n<p style="TEXT-ALIGN: left" align="left"><span style="FONT-WEIGHT: bold">5. Editing or deleting your account information<br />\r\n<br />\r\n</span>This site provides you with the ability to edit the information in your user account that you provided to during the registration process, by visiting <a href="modules.php?name=Your_Account">your personal home page configuration page</a>. You may request deletion of your user account by contacting <a href="modules.php?name=Legal&amp;op=contact">the webmaster</a>. Content or other information that you may have provided, and that is not contained within your user account, such as posts that may appear within site forums, may continue to remain on the site at the site editor\'\'s discretion, even though your user account is deleted. Please see the site\'\'s <a href="modules.php?name=Legal&amp;op=terms">Terms of Use</a> for more information.<br />\r\n<br />\r\n</p>\r\n<div align="left"></div>\r\n<p style="TEXT-ALIGN: left" align="left"><span style="FONT-WEIGHT: bold">6. Changes to this privacy policy<br />\r\n<br />\r\n</span>Changes may be made to this policy from time to time. You will be notified of substantial changes to this policy either by through the posting of a prominent announcement on the site, and or by a message being sent to the e-mail address you have provided, which is contained in your user settings.<br />\r\n<br />\r\n</p>\r\n<div align="left"></div>\r\n<p style="TEXT-ALIGN: left" align="left"><span style="FONT-WEIGHT: bold">7. NO GUARANTEES<br />\r\n<br />\r\n</span>While this privacy policy states standards for maintenance of data, and while efforts will be made to meet the said standards, the site editor is not in a position to guarantee compliance with these standards. There may be factors beyond the site editor\'\'s control that may result in disclosure of data. Consequently, the site editor offers no warranties or representations as regards maintenance or nondisclosure of data.<br />\r\n<br />\r\n</p>\r\n<div align="left"></div>\r\n<p style="TEXT-ALIGN: left" align="left"><span style="FONT-WEIGHT: bold">8. Contact information<br />\r\n<br />\r\n</span>If you have any questions about this policy or [sitename], please feel free to contact <a href="modules.php?name=Legal&amp;op=contact">the webmaster</a>.</p>\r\n<p align="right">[date]</p>\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text` VALUES(4, \'Privacy Policy\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text` VALUES(5, \'<p style="text-align: left;"><span style="font-weight: bold;">1. Acceptance of terms of use and amendments<br />\r\n<br />\r\n</span>Each time you use or cause access to [sitename], you agree to be bound by these <a href="modules.php?name=Legal&amp;op=terms">terms of use</a>, as amended from time to time with or without notice to you. In addition, if you are using a particular service hosted on or accessed via [sitename], you will be subject to any rules or guidelines applicable to the said services, and they will be incorporated by reference within these <a href="modules.php?name=Legal&amp;op=terms">terms of use</a>. Please refer to this site\'\'s <a href="modules.php?name=Legal&amp;op=privacy">privacy policy</a>, which is incorporated within these <a href="modules.php?name=Legal&amp;op=terms">terms of use</a> by reference.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">2. The site editor\'\'s service<br />\r\n<br />\r\n</span>This web site and services provided to you on and through [sitename] are provided on an &quot;AS IS&quot; basis.You agree that the site editor exclusively reserves the right to modify or discontinue provision of [sitename] and its services, and to delete the data you provide, either temporarily or permanently; the site and may, at any time and without notice and any liability to you, The site editor shall have no responsibility or liability for the timeliness, deletion, failure to store, inaccuracy, or improper delivery of any data or information.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">3. Your responsibilities and registration obligations<br />\r\n<br />\r\n</span>In order to use [sitename] or certain parts of it, you may be required to <a href="modules.php?name=Your_Account&amp;op=new_user">register a user account</a> on this web site; in this case, you agree to provide truthful information when requested, and undertake that you are aged at least the thirteen (13) or more.&nbsp;&nbsp; In addition, you are required to register a unique user account to you and that is not shared.&nbsp; <font color="#ff0000">Sharing of user accounts is expressly prohibited</font>.<br />\r\n</p>\r\n<p style="text-align: left;">By registering, you explicitly agree to this site\'\'s <a href="modules.php?name=Legal&amp;op=terms">terms of use</a>, including any amendments made by the site editor from time to time and available here.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">4. Privacy policy</span>.<br />\r\n</p>\r\n<p style="text-align: left;">Registration data and other personally-identifiable information that may be collected on this site is subject to the terms of the site\'\'s <a href="modules.php?name=Legal&amp;op=privacy">privacy policy</a>.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">5. Registration and password<br />\r\n<br />\r\n</span>You are responsible for maintaining the confidentiality of your password, and shall be responsible for all usage of your user account and or user name, whether authorized or unauthorized by you. You agree to immediately notify the site editor of any unauthorized use or your user account, user name or password.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">6. Your conduct.<br />\r\n<br />\r\n</span>You agree that all information or data of any kind, whether text, software, code, music or sound, photographs or graphics, video or other materials (&quot;content&quot;), made available publicly or privately, shall be under the sole responsibility of the person providing the content or the person whose user account is used. You agree that [sitename] may expose you to content that may be objectionable or offensive. The site editor shall not be responsible to you in any way for the content that appears on [sitename], nor for any error or omission.</p>\r\n<p style="text-align: left;">By using [sitename] or any service provided, you explicitly agree that you shall not:<br />\r\n(a) provide any content or conduct yourself in any way that may be construed as: unlawful; illegal; threatening; harmful; abusive; harassing; stalking; tortuous; defamatory; libelous; vulgar; obscene; offensive; objectionable; pornographic; designed to interfere or interrupt [sitename] or any service provided, infected with a virus or other destructive or deleterious programming routine; giving rise to civil or criminal liability; or in violation of [country], applicable local, national or international law;<br />\r\n(b) impersonate or misrepresent your association with any person or entity; forge or otherwise seek to conceal or misrepresent the origin of any content provided by you;<br />\r\n(c) collect or harvest any data about other users;<br />\r\n(d) provide or use [sitename] for the provision of any content or service in any commercial manner, or in any manner that would involve junk mail, spam, chain letters, pyramid schemes, or any other form of unauthorized advertising, without the site editor\'\'s prior written consent; <br />\r\n(e) provide any content that may give rise to civil or criminal liability of the site editor, or that may constitute or be considered a violation of [country], any local, national or international law, including -- but not limited to -- laws relating to copyright, trademark, patent, or trade secrets.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">7. Submission of content on [sitename]<br />\r\n<br />\r\n</span>By providing any content to [sitename]:<br />\r\n(a) you agree to grant the site editor a worldwide, royalty-free, perpetual, non-exclusive right and license (including any moral rights or other necessary rights.) to use, display, reproduce, modify, adapt, publish, distribute, perform, promote, archive, translate, and to create derivative works and compilations, in whole or in part. Such license will apply with respect to any form, media, technology already known or developed subsequently;<br />\r\n(b) you warrant and represent that you have all legal, moral, and other rights that may be necessary to grant us the license specified in this section 7;<br />\r\n(c) you acknowledge and agree that the site editor shall have the right (but not obligation), at the site editor\'\'s entire discretion, to refuse to publish, or to remove, or to block access to any content you provide, at any time and for any reason, with or without notice.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">8. Third-party services<br />\r\n<br />\r\n</span>Goods and services of third parties may be advertised and or made available on or through [sitename]. Representations made regarding products and services provided by third parties are governed by the policies and representations made by these third parties. The site editor shall not be liable for or responsible in any manner for any of your dealings or interaction with third parties.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">9. Indemnification<br />\r\n<br />\r\n</span>You agree to indemnify and hold harmless the site editor and the site editor\'\'s subsidiaries, affiliates, related parties, officers, directors, employees, agents, independent contractors, advertisers, partners, and co-branders, from any claim or demand, including reasonable attorney\'\'s fees, that may be made by any third party, due to or arising out of your conduct or connection with [sitename] or service, your provision of content, your violation of these <a href="modules.php?name=Legal&amp;op=terms">terms of use</a>, or any other violation of the rights of another person or party.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">10. DISCLAIMER OF WARRANTIES<br />\r\n<br />\r\n</span>YOU UNDERSTAND AND AGREE THAT YOUR USE OF THIS WEB SITE AND ANY SERVICES OR CONTENT PROVIDED (THE &quot;SERVICE&quot;) IS MADE AVAILABLE AND PROVIDED TO YOU AT YOUR OWN RISK. IT IS PROVIDED TO YOU &quot;AS IS&quot; AND THE SITE EDITOR EXPRESSLY DISCLAIMS ALL WARRANTIES OF ANY KIND, EITHER IMPLIED OR EXPRESS, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.&nbsp;</p>\r\n<p style="text-align: left;">THE SITE EDITOR MAKES NO WARRANTY, IMPLIED OR EXPRESS, THAT ANY PART OF THE SERVICE WILL BE UNINTERRUPTED, ERROR-FREE, VIRUS-FREE, TIMELY, SECURE, ACCURATE, RELIABLE, OR OF ANY QUALITY, NOR IS IT WARRANTED EITHER IMPLICITLY OR EXPRESSLY THAT ANY CONTENT IS SAFE IN ANY MANNER FOR DOWNLOAD. YOU UNDERSTAND AND AGREE THAT NEITHER THE SITE EDITOR NOR ANY PARTICIPANT IN THE SERVICE PROVIDES PROFESSIONAL ADVICE OF ANY KIND AND THAT USE OF ANY ADVICE OR ANY OTHER INFORMATION OBTAINED VIA THIS WEB SITE IS SOLELY AT YOUR OWN RISK, AND THAT THE SITE EDITOR MAY NOT BE HELD LIABLE IN ANY WAY. <br />\r\n</p>\r\n<p style="text-align: left;">Some jurisdictions may not allow disclaimers of implied warranties, and certain statements in the above disclaimer may not apply to you as regards implied warranties; the other terms and conditions remain enforceable notwithstanding.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">11. LIMITATION OF LIABILITY<br />\r\n<br />\r\n</span>YOU EXPRESSLY UNDERSTAND AND AGREE THAT THE SITE EDTIOR SHALL NOT BE LIABLE FOR ANY DIRECT, INDIRECT, SPECIAL, INDICENTAL, CONSEQUENTIAL OR EXEMPLARY DAMAGES; THIS INCLUDES, BUT IS NOT LIMITED TO, DAMAGES FOR LOSS OF PROFITS, GOODWILL, USE, DATA OR OTHER INTANGIBLE LOSS (EVEN IF THE SITE EDITOR HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES), RESULTING FROM OR ARISING OUT OF (I) THE USE OF OR THE INABILITY TO USE THE SERVICE, (II) THE COST OF OBTAINING SUBSTITUTE GOODS AND OR SERVICES RESULTING FROM ANY TRANSACTION ENTERED INTO ON THROUGH THE SERVICE, (III) UNAUTHORIZED ACCESS TO OR ALTERATION OF YOUR DATA TRANSMISSIONS, (IV) STATEMENTS BY ANY THIRD PARTY OR CONDUCT OF ANY THIRD PARTY USING THE SERVICE, OR (V) ANY OTHER MATTER RELATING TO THE SERVICE.</p>\r\n<p style="text-align: left;">In some jurisdictions, it is not permitted to limit liability and, therefore, such limitations may not apply to you.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">12. Reservation of rights<br />\r\n<br />\r\n</span>The site editor reserves all of the site editor\'\'s rights, including but not limited to any and all copyrights, trademarks, patents, trade secrets, and any other proprietary right that the site editor may have for [sitename], its content, and the goods and services that may be provided. The use of the site editor\'\'s rights. and property requires the site editor\'\'s prior written consent. By making services available to you, the site editor is not providing you with any implied or express licenses or rights, and you will have no rights. to make any commercial uses of [sitename] or service without the site editor\'\'s prior written consent.</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">13. Notification of copyright infringement<br />\r\n<br />\r\n</span>If you believe that your property has been used in any way that would be considered a copyright infringement or a violation of your intellectual property rights, the site editor\'\'s copyright agent may be contacted at the following address:<br />\r\n<br />\r\n<a href="modules.php?name=Legal&amp;op=contact">Click here to contact the webmaster</a><br />\r\n<br />\r\n<span style="font-weight: bold;">14. Applicable law<br />\r\n<br />\r\n</span>You agree that these <a href="modules.php?name=Legal&amp;op=terms">terms of use</a> and any dispute arising out of your use of [sitename] or the site editor\'\'s products or services shall be governed by and construed in accordance with local laws in force of [country], headquarters of the owner of [sitename], without regard to its conflict of law provisions. By registering or using this web site and service, you consent and submit to the exclusive jurisdiction and venue of [country], headquarters of the owner of [sitename].&nbsp;</p>\r\n<p style="text-align: left;"><span style="font-weight: bold;">15. Miscellaneous information<br />\r\n<br />\r\n</span>(ID 1033 0 1) In the event that these <a href="modules.php?name=Legal&amp;op=terms">terms of use</a> conflict with any law under which any provision may be held invalid by a court with jurisdiction over the parties, such provision will be interpreted to reflect the original intentions of the parties in accordance with applicable law, and the remainder of these <a href="modules.php?name=Legal&amp;op=terms">terms of use</a> will remain valid and intact; (ii) The failure of either party to assert any right under these <a href="modules.php?name=Legal&amp;op=terms">terms of use</a> shall not be considered a waiver of that party\'\'s right, and that right will remain in full force and effect; (iii) You agree that, without regard to any statute or contrary law, that any claim or cause arising out of [sitename] or its services must be filed within one (1) year after such claim or cause arose, or else the claim shall be forever barred; (iv) The site editor may assign the site editor\'\'s rights and obligations under these <a href="modules.php?name=Legal&amp;op=terms">terms of use</a>; in this case, the site editor shall be relieved of any further obligation.</p>\r\n<p align="right">[country], [date]</p>\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text` VALUES(6, \'Terms of Use\')';
		sqlexec($sql);
	}
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$prefix.'_legal_text_map` (`mid` mediumint(9) NOT NULL, `did` int(11) NOT NULL, `tid` int(11) NOT NULL, `language` varchar(32) NOT NULL default \'english\', UNIQUE KEY `mid` (`mid`,`did`,`tid`)) TYPE=MyISAM';
	sqlexec($sql);
	if (!$legal_exists) {
		$sql = 'INSERT INTO `'.$prefix.'_legal_text_map` VALUES(1, 1, 1, \'english\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text_map` VALUES(2, 1, 2, \'english\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text_map` VALUES(1, 2, 3, \'english\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text_map` VALUES(2, 2, 4, \'english\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text_map` VALUES(1, 3, 5, \'english\')';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_legal_text_map` VALUES(2, 3, 6, \'english\')';
		sqlexec($sql);
	}
	// Per Mantis 0001282 - needed to tighten up the check so duplicate Legal would not show in modules list
	if ($db->sql_numrows($db->sql_query('SELECT mid FROM '.$prefix.'_modules WHERE title = \'Legal\'')) == 0) {
		$sql = 'INSERT INTO `'.$prefix.'_modules` VALUES(NULL, \'Legal\', \'Legal\', 1, 0, \'\', 0, 0, \'\')';
		sqlexec($sql);
	}
	/*
	 * Added max_rss_items field to blocks table fkelly
	 */
	$sql = 'ALTER TABLE `'.$prefix.'_blocks` ADD `max_rss_items` INT( 5 ) NOT NULL DEFAULT \'0\'';
	sqlexec($sql);
	/*
	 * RNYA added
	 */
	$sql = 'ALTER TABLE `'.$user_prefix.'_users` ADD `agreedtos` TINYINT( 1 ) NOT NULL DEFAULT \'0\'';
	sqlexec($sql);
	$sql = 'ALTER TABLE `'.$user_prefix.'_users` ADD `lastsitevisit` INT( 11 ) NOT NULL DEFAULT \'0\'';
	sqlexec($sql);
	$sql = 'UPDATE `'.$user_prefix.'_users` SET lastsitevisit = user_lastvisit';
	sqlexec($sql);
	$sql = 'ALTER TABLE `'.$user_prefix.'_users_temp` ADD `name` VARCHAR(255) NOT NULL AFTER username';
	sqlexec($sql);
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$user_prefix.'_users_config` (`config_name` varchar(255) NOT NULL default \'\', `config_value` longtext, UNIQUE KEY config_name (config_name)) TYPE=MyISAM';
	sqlexec($sql);

	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('allowmailchange', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('allowuserdelete', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('allowuserreg', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('allowusertheme', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('autosuspend', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('autosuspendmain', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('bad_mail', 'aravensoft.com\r\nbk.ru\r\nlist.ru\r\nmail.ru\r\nmysite.com\r\nya.ru\r\nyoursite.com')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('bad_nick', 'adm\r\nadmin\r\nanonimo\r\nanonymous\r\nan=nimo\r\ngod\r\nlinux\r\nnobody\r\noperator\r\nroot\r\nstaff\r\nwebmaster')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('codesize', '8')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('cookiecheck', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('cookiecleaner', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('cookieinactivity', '-')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('cookiepath', '')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('cookietimelife', '2592000')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('coppa', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('doublecheckemail', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('emailvalidate', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('expiring', '86400')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('legal_did_TOS', '3')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('nick_max', '25')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('nick_min', '4')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('pass_max', '20')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('pass_min', '4')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('perpage', '100')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('requireadmin', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('sendaddmail', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('senddeletemail', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('servermail', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('tos', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('tosall', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useactivate', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useasreguser', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('usebirthdate', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useextrainfo', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('usefakeemail', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useforumnotifyoptions', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('usegender', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('usegfxcheck', '0')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('usehideonline', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useinstantmessaim', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useinstantmessicq', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useinstantmessmsn', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useinstantmessyim', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useinterests', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('uselocation', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('usenewsletter', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useoccupation', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('usepoints', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('userealname', '3')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('usesignature', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('useviewemail', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('usewebsite', '1')";
	sqlexec($sql);
	$sql = "INSERT INTO ".$user_prefix."_users_config VALUES ('version', '2.30.00')";
	sqlexec($sql);

	$sql = "CREATE TABLE ".$user_prefix."_users_fields (fid int(10) NOT NULL auto_increment, name varchar(255) NOT NULL default 'field', value varchar(255), size int(3), need int(1) NOT NULL default '1', pos int(3), public int(1) NOT NULL default '1', PRIMARY KEY  (fid) ) TYPE=MyISAM AUTO_INCREMENT=1";
	sqlexec($sql);
	$sql = "CREATE TABLE ".$user_prefix."_users_field_values (vid int(10) NOT NULL auto_increment, uid int(10) NOT NULL, fid int(10) NOT NULL, value varchar(255), PRIMARY KEY  (vid)) TYPE=MyISAM AUTO_INCREMENT=1";
	sqlexec($sql);
	$sql = "CREATE TABLE ".$user_prefix."_users_temp_field_values (vid int(10) NOT NULL auto_increment, uid int(10) NOT NULL, fid int(10) NOT NULL, value varchar(255), PRIMARY KEY (vid)) TYPE=MyISAM AUTO_INCREMENT=1";
	sqlexec($sql);
	/*
	 * Add/Delete indexes per Mantis 0001086
	 * Table to add indexes to:
		* nuke_counter : Add PRIMARY INDEX (type(20), var(20))
		* nuke_nsngr_users : Add INDEX (gid, uid, uname)
		* nuke_poll_check : Add PRIMARY INDEX (ip, pollID)
		* nuke_stats_date : ADD PRIMARY KEY (year, month, `date`)
		* nuke_stats_hour : ADD PRIMARY KEY (year, month, `date`, hour)
		* nuke_stats_month : ADD PRIMARY KEY (year, month)
		* nuke_stats_year : ADD PRIMARY KEY (year)
	 * Tables with duplicate indexes defined:
		* nuke_banner : Remove regular INDEX bid
		* nuke_banner_clients : Remove regular INDEX cid
		* nuke_mail_config : Remove unique INDEX mailer
	 */
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_banner LIMIT 0')) {
		$sql = 'ALTER TABLE `' . $prefix . '_banner` DROP INDEX `bid`';
		sqlexec($sql);
	}
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_banner_clients LIMIT 0')) {
		$sql = 'ALTER TABLE `' . $prefix . '_banner_clients` DROP INDEX `cid`';
		sqlexec($sql);
	}
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_mail_config LIMIT 0')) {
		$sql = 'ALTER TABLE `' . $prefix . '_mail_config` DROP INDEX `mailer`';
		sqlexec($sql);
	}
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_counter LIMIT 0')) {
		$sql = 'ALTER TABLE `' . $prefix . '_counter` ADD PRIMARY KEY (`type`(20),`var`(20))';
		sqlexec($sql);
	}
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_nsngr_users LIMIT 0')) {
		$sql = 'ALTER TABLE `' . $prefix . '_nsngr_users` ADD PRIMARY KEY (`gid`,`uid`,`uname`)';
		sqlexec($sql);
	}
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_poll_check LIMIT 0')) {
		$sql = 'ALTER TABLE `' . $prefix . '_poll_check` ADD PRIMARY KEY (`pollID`,`ip`)';
		sqlexec($sql);
	}

	/****
	 ** Removed per Mantis issue 1254.
	 ** See further information up above in the fixStatsTables() function.
	 **
	 fixStatsTables('stats_year',' `year`');
	 fixStatsTables('stats_month','`year`, `month`');
	 fixStatsTables('stats_date','`year`, `month`, `date`');
	 fixStatsTables('stats_hour','`year`, `month`, `date`, `hour`');
	 ****/

	/*
	 * Additional modules added, so need to insert them into the _modules table
	 */
	if ($db->sql_numrows($db->sql_query('SELECT mid FROM '.$prefix.'_modules WHERE title = \'ErrorDocuments\'')) == 0) {
		$sql = 'INSERT INTO `'.$prefix.'_modules` VALUES(NULL, \'ErrorDocuments\', \'ErrorDocuments\', 0, 0, \'\', 0, 0, \'\')';
		sqlexec($sql);
	}
	/*
	 * Got rid of HTTP Referrers settings from the Preferences as these are done better through NukeSentinel(tm)
	 */
	$sql = 'DROP TABLE ' . $prefix . '_referer';
	sqlexec($sql);
	$sql = 'ALTER TABLE ' . $prefix . '_config DROP httpref, DROP httprefmax';
	sqlexec($sql);
	/*
	 * Fix a couple of nukeSEO/FEED table columns
	 */
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_seo_config LIMIT 0')) {
		$sql = 'ALTER TABLE `' . $prefix . '_seo_config` MODIFY config_type varchar(150) NOT NULL DEFAULT \'\', ';
		$sql .= 'MODIFY config_name varchar(150) NOT NULL DEFAULT \'\'';
		sqlexec($sql);
	}
	if ($db->sql_query('SELECT 1 FROM ' . $prefix . '_seo_disabled_modules LIMIT 0')) {
		$sql = 'ALTER TABLE `' . $prefix . '_seo_disabled_modules` MODIFY title varchar(100) NOT NULL DEFAULT \'\', ';
		$sql .= 'MODIFY seo_module varchar(100) NOT NULL DEFAULT \'\'';
		sqlexec($sql);
	}
	/*
	 * Add counter for Chrome and Safari browsers Mantis 0001208 - Contributed by jestrella
	 */
	$sql = 'INSERT INTO `' . $prefix . '_counter` (`type`, `var`, `count`) VALUES (\'browser\', \'Chrome\', \'0\');';
	sqlexec($sql);
	$sql = 'INSERT INTO `' . $prefix . '_counter` (`type`, `var`, `count`) VALUES (\'browser\', \'Safari\', \'0\');';
	sqlexec($sql);
	/*
	 * phpBB Attachment Mod has now become core, so add the necessary tables and data here
	 */
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_bbattachments_config LIMIT 0')) {
		$sql = 'CREATE TABLE `' . $prefix . '_bbattachments_config` (`config_name` varchar(255) NOT NULL, `config_value` varchar(255) NOT NULL, PRIMARY KEY (`config_name`)) TYPE=MyISAM';
		sqlexec($sql);
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'upload_dir\',\'modules/Forums/files\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'upload_img\',\'modules/Forums/images/icon_clip.gif\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'topic_icon\',\'modules/Forums/images/icon_clip.gif\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'display_order\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'max_filesize\',\'262144\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'attachment_quota\',\'52428800\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'max_filesize_pm\',\'262144\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'max_attachments\',\'3\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'max_attachments_pm\',\'1\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'disable_mod\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'allow_pm_attach\',\'1\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'attachment_topic_review\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'allow_ftp_upload\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'show_apcp\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'attach_version\',\'2.4.5\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'default_upload_quota\', \'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'default_pm_quota\', \'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'ftp_server\',\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'ftp_path\',\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'download_path\',\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'ftp_user\',\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'ftp_pass\',\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'ftp_pasv_mode\',\'1\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'img_display_inlined\',\'1\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'img_max_width\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'img_max_height\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'img_link_width\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'img_link_height\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'img_create_thumbnail\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'img_min_thumb_filesize\',\'12000\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'img_imagick\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'use_gd2\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'wma_autoplay\',\'0\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbattachments_config` (`config_name`, `config_value`) VALUES (\'flash_autoplay\',\'0\')');
	}
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_bbforbidden_extensions LIMIT 0')) {
		$sql = 'CREATE TABLE `' . $prefix . '_bbforbidden_extensions` (`ext_id` mediumint(8) UNSIGNED NOT NULL auto_increment, extension varchar(100) NOT NULL, PRIMARY KEY (`ext_id`)) TYPE=MyISAM';
		sqlexec($sql);
		sqlexec('INSERT INTO `' . $prefix . '_bbforbidden_extensions` (`ext_id`, `extension`) VALUES (1,\'php\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbforbidden_extensions` (`ext_id`, `extension`) VALUES (2,\'php3\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbforbidden_extensions` (`ext_id`, `extension`) VALUES (3,\'php4\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbforbidden_extensions` (`ext_id`, `extension`) VALUES (4,\'phtml\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbforbidden_extensions` (`ext_id`, `extension`) VALUES (5,\'pl\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbforbidden_extensions` (`ext_id`, `extension`) VALUES (6,\'asp\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbforbidden_extensions` (`ext_id`, `extension`) VALUES (7,\'cgi\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbforbidden_extensions` (`ext_id`, `extension`) VALUES (8,\'php5\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbforbidden_extensions` (`ext_id`, `extension`) VALUES (9,\'php6\')');
	}
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_bbextension_groups LIMIT 0')) {
		$sql = 'CREATE TABLE `' . $prefix . '_bbextension_groups` (`group_id` mediumint(8) NOT NULL auto_increment, `group_name` char(20) NOT NULL, `cat_id` tinyint(2) DEFAULT \'0\' NOT NULL, `allow_group` tinyint(1) DEFAULT \'0\' NOT NULL,  `download_mode` tinyint(1) UNSIGNED DEFAULT \'1\' NOT NULL, `upload_icon` varchar(100) DEFAULT \'\', `max_filesize` int(20) DEFAULT \'0\' NOT NULL, `forum_permissions` varchar(255) default \'\' NOT NULL, PRIMARY KEY `group_id` (`group_id`)) TYPE=MyISAM';
		sqlexec($sql);
		sqlexec('INSERT INTO `' . $prefix . '_bbextension_groups` (`group_id`, `group_name`, `cat_id`, `allow_group`, `download_mode`, `upload_icon`, `max_filesize`, `forum_permissions`) VALUES (1,\'Images\',1,1,1,\'\',0,\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextension_groups` (`group_id`, `group_name`, `cat_id`, `allow_group`, `download_mode`, `upload_icon`, `max_filesize`, `forum_permissions`) VALUES (2,\'Archives\',0,1,1,\'\',0,\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextension_groups` (`group_id`, `group_name`, `cat_id`, `allow_group`, `download_mode`, `upload_icon`, `max_filesize`, `forum_permissions`) VALUES (3,\'Plain Text\',0,0,1,\'\',0,\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextension_groups` (`group_id`, `group_name`, `cat_id`, `allow_group`, `download_mode`, `upload_icon`, `max_filesize`, `forum_permissions`) VALUES (4,\'Documents\',0,0,1,\'\',0,\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextension_groups` (`group_id`, `group_name`, `cat_id`, `allow_group`, `download_mode`, `upload_icon`, `max_filesize`, `forum_permissions`) VALUES (5,\'Real Media\',0,0,2,\'\',0,\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextension_groups` (`group_id`, `group_name`, `cat_id`, `allow_group`, `download_mode`, `upload_icon`, `max_filesize`, `forum_permissions`) VALUES (6,\'Streams\',2,0,1,\'\',0,\'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextension_groups` (`group_id`, `group_name`, `cat_id`, `allow_group`, `download_mode`, `upload_icon`, `max_filesize`, `forum_permissions`) VALUES (7,\'Flash Files\',3,0,1,\'\',0,\'\')');
	}
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_bbextensions LIMIT 0')) {
		$sql = 'CREATE TABLE `' . $prefix . '_bbextensions` ( `ext_id` mediumint(8) UNSIGNED NOT NULL auto_increment,  `group_id` mediumint(8) UNSIGNED DEFAULT \'0\' NOT NULL,  `extension` varchar(100) NOT NULL,  `comment` varchar(100),  PRIMARY KEY `ext_id` (`ext_id`)) TYPE=MyISAM';
		sqlexec($sql);
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (1, 1,\'gif\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (2, 1,\'png\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (3, 1,\'jpeg\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (4, 1,\'jpg\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (5, 1,\'tif\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (6, 1,\'tga\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (7, 2,\'gtar\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (8, 2,\'gz\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (9, 2,\'tar\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (10, 2,\'zip\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (11, 2,\'rar\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (12, 2,\'ace\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (13, 3,\'txt\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (14, 3,\'c\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (15, 3,\'h\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (16, 3,\'cpp\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (17, 3,\'hpp\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (18, 3,\'diz\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (19, 4,\'xls\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (20, 4,\'doc\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (21, 4,\'dot\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (22, 4,\'pdf\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (23, 4,\'ai\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (24, 4,\'ps\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (25, 4,\'ppt\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (26, 5,\'rm\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (27, 6,\'wma\', \'\')');
		sqlexec('INSERT INTO `' . $prefix . '_bbextensions` (`ext_id`, `group_id`, `extension`, `comment`) VALUES (28, 7,\'swf\', \'\')');
	}
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_bbattachments_desc LIMIT 0')) {
		$sql = 'CREATE TABLE `' . $prefix . '_bbattachments_desc` ( `attach_id` mediumint(8) UNSIGNED NOT NULL auto_increment,  `physical_filename` varchar(255) NOT NULL,  `real_filename` varchar(255) NOT NULL,  `download_count` mediumint(8) UNSIGNED DEFAULT \'0\' NOT NULL,  `comment` varchar(255),  `extension` varchar(100),  `mimetype` varchar(100),  `filesize` int(20) NOT NULL,  `filetime` int(11) DEFAULT \'0\' NOT NULL,  `thumbnail` tinyint(1) DEFAULT \'0\' NOT NULL,  PRIMARY KEY (`attach_id`),  KEY `filetime` (`filetime`),  KEY `physical_filename` (`physical_filename`(10)),  KEY `filesize` (`filesize`)) TYPE=MyISAM';
		sqlexec($sql);
	}
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_bbattachments LIMIT 0')) {
		$sql = 'CREATE TABLE `' . $prefix . '_bbattachments` (`attach_id` mediumint(8) UNSIGNED DEFAULT \'0\' NOT NULL, `post_id` mediumint(8) UNSIGNED DEFAULT \'0\' NOT NULL, `privmsgs_id` mediumint(8) UNSIGNED DEFAULT \'0\' NOT NULL, `user_id_1` mediumint(8) NOT NULL, `user_id_2` mediumint(8) NOT NULL, KEY `attach_id_post_id` (`attach_id`, `post_id`), KEY `attach_id_privmsgs_id` (`attach_id`, `privmsgs_id`), KEY `post_id` (`post_id`), KEY `privmsgs_id` (`privmsgs_id`)) TYPE=MyISAM';
		sqlexec($sql);
	}
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_bbquota_limits LIMIT 0')) {
		$sql = 'CREATE TABLE `' . $prefix . '_bbquota_limits` (`quota_limit_id` mediumint(8) unsigned NOT NULL auto_increment, `quota_desc` varchar(20) NOT NULL default \'\', `quota_limit` bigint(20) unsigned NOT NULL default \'0\', PRIMARY KEY (`quota_limit_id`)) TYPE=MyISAM';
		sqlexec($sql);
		sqlexec('INSERT INTO `' . $prefix . '_bbquota_limits` (`quota_limit_id`, `quota_desc`, `quota_limit`) VALUES (1, \'Low\', 262144)');
		sqlexec('INSERT INTO `' . $prefix . '_bbquota_limits` (`quota_limit_id`, `quota_desc`, `quota_limit`) VALUES (2, \'Medium\', 2097152)');
		sqlexec('INSERT INTO `' . $prefix . '_bbquota_limits` (`quota_limit_id`, `quota_desc`, `quota_limit`) VALUES (3, \'High\', 5242880)');
	}
	if (!$db->sql_query('SELECT 1 FROM ' . $prefix . '_bbattach_quota LIMIT 0')) {
		$sql = 'CREATE TABLE `' . $prefix . '_bbattach_quota` (`user_id` mediumint(8) unsigned NOT NULL default \'0\', `group_id` mediumint(8) unsigned NOT NULL default \'0\', `quota_type` smallint(2) NOT NULL default \'0\', `quota_limit_id` mediumint(8) unsigned NOT NULL default \'0\', KEY `quota_type` (`quota_type`)) TYPE=MyISAM';
		sqlexec($sql);
	}
	$sql = 'ALTER TABLE `' . $prefix . '_bbforums` ADD `auth_download` TINYINT(2) DEFAULT \'0\' NOT NULL';
	sqlexec($sql);
	$sql = 'ALTER TABLE `' . $prefix . '_bbauth_access` ADD `auth_download` TINYINT(1) DEFAULT \'0\' NOT NULL';
	sqlexec($sql);
	$sql = 'ALTER TABLE `' . $prefix . '_bbposts` ADD `post_attachment` TINYINT(1) DEFAULT \'0\' NOT NULL';
	sqlexec($sql);
	$sql = 'ALTER TABLE `' . $prefix . '_bbtopics` ADD `topic_attachment` TINYINT(1) DEFAULT \'0\' NOT NULL';
	sqlexec($sql);
	$sql = 'ALTER TABLE `' . $prefix . '_bbprivmsgs` ADD `privmsgs_attachment` TINYINT(1) DEFAULT \'0\' NOT NULL';
	sqlexec($sql);
	/*
	 * Continue to add this versions database changes here, or, if none, remove these comment lines
	 */
	$already_ran = true;
	return;
}
/**
 * Performs updates needed to go from 2.30.00 to 2.30.01
 */
function rn23000() {
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;
	$sql = 'UPDATE '. $user_prefix.'_users_config SET `config_value` = \'2.30.01\' WHERE `config_name` = \'version\'  LIMIT 1' ;
	sqlexec($sql);
	$already_ran = true;
	return;
}
/**
 * Performs updates needed to go from 2.30.01 to 2.30.02
 */
function rn23001() {
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;
	$sql = 'DELETE FROM '. $user_prefix.'_users_config WHERE `config_name` = \'version\' LIMIT 1';
	sqlexec($sql);
	$already_ran = true;
	return;
}
/**
 * Performs updates needed to go from 2.30.02 to 2.40.00
 */
function rn23002() {
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;
	// CONTENT MODULE UPDATE ...  modeled after cpsetup.php but only does required changes
	$sql = 'SHOW TABLES LIKE \''. $prefix . '_pages_feat\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = "CREATE TABLE ".$prefix."_pages_feat (cid INT( 10 ) NOT NULL default '0', pid INT( 10 ) NOT NULL default '0')";
		sqlexec($sql);
	}
	$sql = 'SHOW TABLES LIKE \''. $prefix . '_newpages\'';
	$result1 = sqlexec($sql);
	if ( $db->sql_numrows($result1) == 0) {
		$sql = "CREATE TABLE ".$prefix."_newpages (pid int(10) NOT NULL auto_increment, cid int(10) NOT NULL default '0', title varchar(255) NOT NULL default '', subtitle varchar(255) NOT NULL default '', tags varchar(255) NOT NULL default '', page_header text NOT NULL, text text NOT NULL, page_footer text NOT NULL, signature text NOT NULL, uname varchar(40) NOT NULL default '', date datetime NOT NULL default '0000-00-00 00:00:00', clanguage varchar(30) NOT NULL default '', PRIMARY KEY  (pid), KEY pid (pid), KEY cid (cid))";
		sqlexec($sql);
	}
	$sql = 'SHOW COLUMNS FROM '.$prefix.'_pages LIKE \'uname\'';
	$result = sqlexec($sql);
	if ($db->sql_numrows($result) == 0) {
		$sql = "ALTER TABLE ".$prefix."_pages ADD uname VARCHAR(40) NOT NULL default ''";
		sqlexec($sql);
	}
	$sql = 'SHOW COLUMNS FROM '.$prefix.'_pages LIKE \'tags\'';
	$result = sqlexec($sql);
	if ($db->sql_numrows($result) == 0) {
		$sql = "ALTER TABLE ".$prefix."_pages ADD tags VARCHAR(255) NOT NULL AFTER subtitle";
		sqlexec($sql);
	}
	$sql = 'SHOW COLUMNS FROM '.$prefix.'_pages_categories LIKE \'cimg\'';
	$result = sqlexec($sql);
	if ($db->sql_numrows($result) == 0) {
		$sql = "ALTER TABLE ".$prefix."_pages_categories ADD cimg VARCHAR(255) NOT NULL AFTER cid";
		sqlexec($sql);
	}
	$sql = 'SHOW COLUMNS FROM '.$prefix.'_newpages LIKE \'tags\'';
	$result = sqlexec($sql);
	if ($db->sql_numrows($result) == 0) {
		$sql = "ALTER TABLE ".$prefix."_newpages ADD tags VARCHAR(255) NOT NULL AFTER subtitle";
		sqlexec($sql);
	}
	/*  commented out by fkelly per Mantis issue 1398
	 $sql = 'SELECT * FROM '.$prefix.'_groups_points WHERE id = \'23\'';
	 $result = sqlexec($sql);
	 if ($db->sql_numrows($result) == 0) {
		$sql = 'INSERT INTO '.$prefix.'_groups_points VALUES (23, 0)';
		sqlexec($sql);
		}
	
	sqlexec($sql);
	*/
	$sql = 'SHOW COLUMNS FROM '.$prefix.'_nsngr_users LIKE \'uname\'';
	$result = sqlexec($sql);
	if ($db->sql_numrows($result) > 0) {
		$sql = "ALTER TABLE ".$prefix."_nsngr_users DROP uname";
		sqlexec($sql);
	}
	// PROJECT TRACKING INSTALL ...  the same structure from NukeProjects other than there is no version number or location
	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_config\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_config` (`config_name` varchar(255) NOT NULL default \'\', `config_value` text NOT NULL) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_config` (`config_name`, `config_value`) VALUES (\'admin_report_email\', \'webmaster@mysite.com\'), '
		.'(\'admin_request_email\', \'webmaster@mysite.com\'), (\'new_project_position\', \'1\'), (\'new_project_priority\', \'3\'), (\'new_project_status\', \'1\'), '
		.'(\'new_report_position\', \'2\'), (\'new_report_status\', \'5\'), (\'new_report_type\', \'-1\'), (\'new_request_position\', \'2\'), (\'new_request_status\', \'6\'), '
		.'(\'new_request_type\', \'-1\'), (\'new_task_position\', \'2\'), (\'new_task_priority\', \'3\'), (\'new_task_status\', \'4\'), (\'notify_report_admin\', \'0\'), '
		.'(\'notify_report_submitter\', \'0\'), (\'notify_request_admin\', \'0\'), (\'notify_request_submitter\', \'0\'), (\'project_date_format\', \'Y-m-d H:i:s\'), '
		.'(\'report_date_format\', \'Y-m-d H:i:s\'), (\'request_date_format\', \'Y-m-d H:i:s\'), (\'task_date_format\', \'Y-m-d H:i:s\')';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_members\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_members` (`member_id` int(11) NOT NULL auto_increment, `member_name` varchar(255) NOT NULL default \'\', '
		.'`member_email` varchar(255) NOT NULL default \'\', PRIMARY KEY (`member_id`), KEY `member_id` (`member_id`)) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_members_positions\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_members_positions` (`position_id` int(11) NOT NULL auto_increment, `position_name` varchar(255) NOT NULL default \'\', '
		.'`position_weight` int(11) NOT NULL default \'0\', PRIMARY KEY (`position_id`), KEY `position_id` (`position_id`)) TYPE=MyISAM AUTO_INCREMENT=5';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_members_positions` (`position_id`, `position_name`, `position_weight`) VALUES (-1, \'N/A\', 0), (1, \'Manager\', 1), (2, \'Developer\', 2), '
		.'(3, \'Tester\', 3), (4, \'Sponsor\', 4)';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_projects\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_projects` (`project_id` int(11) NOT NULL auto_increment, `project_name` varchar(255) NOT NULL default \'\', `project_description` text NOT NULL, '
		.'`project_site` varchar(255) NOT NULL default \'\', `priority_id` int(11) NOT NULL default \'0\', `status_id` int(11) NOT NULL default \'0\', '
		.'`project_percent` float NOT NULL default \'0\', `weight` int(11) NOT NULL default \'0\', `featured` tinyint(2) NOT NULL default \'0\', '
		.'`allowreports` tinyint(2) NOT NULL default \'0\', `allowrequests` tinyint(2) NOT NULL default \'0\', `date_created` int(14) NOT NULL default \'0\', '
		.'`date_started` int(14) NOT NULL default \'0\', `date_finished` int(14) NOT NULL default \'0\', PRIMARY KEY (`project_id`)) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_projects_members\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_projects_members` (`project_id` int(11) NOT NULL default \'0\', `member_id` int(11) NOT NULL default \'0\', '
		.'`position_id` int(11) NOT NULL default \'0\', KEY `project_id` (`project_id`), KEY `member_id` (`member_id`)) TYPE=MyISAM';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_projects_priorities\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_projects_priorities` (`priority_id` int(11) NOT NULL auto_increment, `priority_name` varchar(30) NOT NULL default \'\', '
		.'`priority_weight` int(11) NOT NULL default \'1\', PRIMARY KEY (`priority_id`)) TYPE=MyISAM AUTO_INCREMENT=6';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_projects_priorities` (`priority_id`, `priority_name`, `priority_weight`) '
		.'VALUES (-1, \'N/A\', 0), (1, \'Low\', 1), (2, \'Low-Med\', 2), (3, \'Medium\', 3), (4, \'High-Med\', 4), (5, \'High\', 5)';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_projects_status\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_projects_status` (`status_id` int(11) NOT NULL auto_increment, `status_name` varchar(255) NOT NULL default \'\', '
		.'`status_weight` int(11) NOT NULL default \'0\', PRIMARY KEY (`status_id`)) TYPE=MyISAM AUTO_INCREMENT=6';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_projects_status` (`status_id`, `status_name`, `status_weight`) '
		.'VALUES (-1, \'N/A\', 0), (1, \'Pending\', 1), (2, \'Completed\', 2), (3, \'Active\', 3), (4, \'Inactive\', 4), (5, \'Released\', 5)';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_reports\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_reports` (`report_id` int(11) NOT NULL auto_increment, `project_id` int(11) NOT NULL default \'0\', '
		.'`type_id` int(11) NOT NULL default \'0\', `status_id` int(11) NOT NULL default \'0\', `report_name` varchar(255) NOT NULL default \'\', '
		.'`report_description` text NOT NULL, `submitter_name` varchar(32) NOT NULL default \'\', `submitter_email` varchar(255) NOT NULL default \'\', '
		.'`submitter_ip` varchar(20) NOT NULL default \'0.0.0.0\', `date_submitted` int(14) NOT NULL default \'0\', `date_commented` int(14) NOT NULL default \'0\', '
		.'`date_modified` int(14) NOT NULL default \'0\', PRIMARY KEY (`report_id`)) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_reports_comments\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_reports_comments` (`comment_id` int(11) NOT NULL auto_increment, `report_id` int(11) NOT NULL default \'0\', '
		.'`commenter_name` varchar(32) NOT NULL default \'\', `commenter_email` varchar(255) NOT NULL default \'\', `commenter_ip` varchar(20) NOT NULL default \'0.0.0.0\', '
		.'`comment_description` text NOT NULL, `date_commented` int(14) NOT NULL default \'0\', PRIMARY KEY (`comment_id`)) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_reports_members\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_reports_members` (`report_id` int(11) NOT NULL default \'0\', `member_id` int(11) NOT NULL default \'0\', '
		.'`position_id` int(11) NOT NULL default \'0\', KEY `report_id` (`report_id`), KEY `member_id` (`member_id`)) TYPE=MyISAM';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_reports_status\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_reports_status` (`status_id` int(11) NOT NULL auto_increment, `status_name` varchar(255) NOT NULL default \'\', '
		.'`status_weight` int(11) NOT NULL default \'0\', PRIMARY KEY  (`status_id`)) TYPE=MyISAM AUTO_INCREMENT=10';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_reports_status` (`status_id`, `status_name`, `status_weight`) VALUES (-1, \'N/A\', 0), (1, \'Open\', 1), (2, \'Closed\', 2), (3, \'Duplicate\', 3), '
		.'(4, \'Feedback\', 4), (5, \'Submitted\', 5), (6, \'Suspended\', 6), (7, \'Assigned\', 7), (8, \'Info Needed\', 8), (9, \'Unverifiable\', 9)';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_reports_types\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_reports_types` (`type_id` int(11) NOT NULL auto_increment, `type_name` varchar(255) NOT NULL default \'\', '
		.'`type_weight` int(11) NOT NULL default \'0\', PRIMARY KEY (`type_id`)) TYPE=MyISAM AUTO_INCREMENT=2';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_reports_types` (`type_id`, `type_name`, `type_weight`) VALUES (-1, \'N/A\', 0), (1, \'General\', 1)';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_requests\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_requests` (`request_id` int(11) NOT NULL auto_increment, `project_id` int(11) NOT NULL default \'0\', `type_id` int(11) NOT NULL default \'0\', '
		.'`status_id` int(11) NOT NULL default \'0\', `request_name` varchar(255) NOT NULL default \'\', `request_description` text NOT NULL, '
		.'`submitter_name` varchar(32) NOT NULL default \'\', `submitter_email` varchar(255) NOT NULL default \'\', `submitter_ip` varchar(20) NOT NULL default \'0.0.0.0\', '
		.'`date_submitted` int(14) NOT NULL default \'0\', `date_commented` int(14) NOT NULL default \'0\', `date_modified` int(14) NOT NULL default \'0\', '
		.'PRIMARY KEY (`request_id`)) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_requests_comments\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_requests_comments` (`comment_id` int(11) NOT NULL auto_increment, `request_id` int(11) NOT NULL default \'0\', '
		.'`commenter_name` varchar(32) NOT NULL default \'\', `commenter_email` varchar(255) NOT NULL default \'\', `commenter_ip` varchar(20) NOT NULL default \'0.0.0.0\', '
		.'`comment_description` text NOT NULL, `date_commented` int(14) NOT NULL default \'0\', PRIMARY KEY (`comment_id`)) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_requests_members\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_requests_members` (`request_id` int(11) NOT NULL default \'0\', `member_id` int(11) NOT NULL default \'0\', '
		.'`position_id` int(11) NOT NULL default \'0\', KEY `request_id` (`request_id`), KEY `member_id` (`member_id`)) TYPE=MyISAM';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_requests_status\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_requests_status` (`status_id` int(11) NOT NULL auto_increment, `status_name` varchar(255) NOT NULL default \'\', '
		.'`status_weight` int(11) NOT NULL default \'0\', PRIMARY KEY (`status_id`)) TYPE=MyISAM AUTO_INCREMENT=9';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_requests_status` (`status_id`, `status_name`, `status_weight`) VALUES (-1, \'N/A\', 0), (1, \'Duplicate\', 1), '
		.'(2, \'Closed\', 2), (3, \'Inclusion\', 3), (4, \'Open\', 4), (5, \'Feedback\', 5), (6, \'Submitted\', 6), (7, \'Discarded\', 7), (8, \'Assigned\', 8)';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_requests_types\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_requests_types` (`type_id` int(11) NOT NULL auto_increment, `type_name` varchar(255) NOT NULL default \'\', '
		.'`type_weight` int(11) NOT NULL default \'0\', PRIMARY KEY (`type_id`)) TYPE=MyISAM AUTO_INCREMENT=2';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_requests_types` (`type_id`, `type_name`, `type_weight`) VALUES (-1, \'N/A\', 0), (1, \'General\', 1)';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_tasks\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_tasks` (`task_id` int(11) NOT NULL auto_increment, `project_id` int(11) NOT NULL default \'0\', '
		.'`task_name` varchar(255) NOT NULL default \'\', `task_description` text NOT NULL, `priority_id` int(11) NOT NULL default \'1\', '
		.'`status_id` int(11) NOT NULL default \'0\', `task_percent` float NOT NULL default \'0\', `date_created` int(14) NOT NULL default \'0\', '
		.'`date_started` int(14) NOT NULL default \'0\', `date_finished` int(14) NOT NULL default \'0\', '
		.'PRIMARY KEY (`task_id`)) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_tasks_members\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_tasks_members` (`task_id` int(11) NOT NULL default \'0\', `member_id` int(11) NOT NULL default \'0\', '
		.'`position_id` int(11) NOT NULL default \'0\', KEY `task_id` (`task_id`), KEY `member_id` (`member_id`)) TYPE=MyISAM';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_tasks_priorities\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_tasks_priorities` (`priority_id` int(11) NOT NULL auto_increment, `priority_name` varchar(30) NOT NULL default \'\', '
		.'`priority_weight` int(11) NOT NULL default \'1\', PRIMARY KEY (`priority_id`)) TYPE=MyISAM AUTO_INCREMENT=6';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_tasks_priorities` (`priority_id`, `priority_name`, `priority_weight`) VALUES (-1, \'N/A\', 0), (1, \'Low\', 1), '
		.'(2, \'Low-Med\', 2), (3, \'Medium\', 3), (4, \'High-Med\', 4), (5, \'High\', 5)';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_nsnpj_tasks_status\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_nsnpj_tasks_status` (`status_id` int(11) NOT NULL auto_increment, `status_name` varchar(255) NOT NULL default \'\', '
		.'`status_weight` int(11) NOT NULL default \'0\', PRIMARY KEY (`status_id`)) TYPE=MyISAM AUTO_INCREMENT=8';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_nsnpj_tasks_status` (`status_id`, `status_name`, `status_weight`) VALUES (-1, \'N/A\', 0), (1, \'Inactive\', 1), '
		.'(2, \'On Going\', 2), (3, \'Holding\', 3), (4, \'Open\', 4), (5, \'Completed\', 5), (6, \'Discontinued\', 6), (7, \'Active\', 7)';
		sqlexec($sql);
	}
	//END Project Tracking

	//START Remove Sunset theme if installed in forms
	$resultbc = $db->sql_query('SELECT `themes_id` FROM `'.$prefix.'_bbthemes` WHERE `template_name` = \'Sunset\'');
	if ($db->sql_numrows($resultbc) != 0) {
		list($style_id) = $db->sql_fetchrow($resultbc);
		$sql = 'DELETE FROM `'.$prefix.'_bbthemes` WHERE `themes_id` = \''.$style_id.'\'';
		sqlexec($sql);

		$resultbc = $db->sql_query('SELECT * FROM `'.$prefix.'_bbthemes_name` WHERE `themes_id` = \''.$style_id.'\'');
		if ($db->sql_numrows($resultbc) != 0) {
			$sql = 'DELETE FROM `'.$prefix.'_bbthemes_name` WHERE `themes_id` = \''.$style_id.'\'';
			sqlexec($sql);
		}

		$resultbc = $db->sql_query('SELECT `config_value` FROM `'.$prefix.'_bbconfig` WHERE `config_name` = \'default_style\'');
		$numrows = $db->sql_numrows($resultbc);
		$resultbc = $db->sql_fetchrow($resultbc);
		if ($numrows != 0 && $resultbc['config_value'] == $style_id) {
			$resultbc = $db->sql_query('SELECT `themes_id` FROM `'.$prefix.'_bbthemes` WHERE `template_name` = \'subSilver\'');
			list($new_default_theme) = $db->sql_fetchrow($resultbc);
			$sql = 'UPDATE `'.$prefix.'_bbconfig` SET `config_value` = "'.$new_default_theme.'" WHERE `config_name` = \'default_style\'';
			sqlexec($sql);
		}

		$resultbc = $db->sql_query('SELECT `user_style` FROM `'.$user_prefix.'_users` WHERE `user_style` = "'.$style_id.'"');
		if ($db->sql_numrows($resultbc) != 0) {
			$resultbc = $db->sql_query('SELECT `config_value` FROM `'.$prefix.'_bbconfig` WHERE `config_name` = \'default_style\'');
			list($default_theme) = $db->sql_fetchrow($resultbc);
			$sql = 'UPDATE `'.$user_prefix.'_users` SET `user_style` = "'.$default_theme.'" WHERE `user_style` = "'.$style_id.'"';
			sqlexec($sql);
		}
	}
	//END

	//Remove ultramode record from *_config
	$sql = 'SHOW COLUMNS FROM `'.$prefix.'_config` LIKE \'ultramode\'';
	$result = sqlexec($sql);
	if ($db->sql_numrows($result) > 0) {
		$sql = 'ALTER TABLE `'.$prefix.'_config` DROP ultramode';
		sqlexec($sql);
	}

	//Fix modules table from bad insertion code;.  May or maynot affect anything.  It depends on the site.
	$resultbc = $db->sql_query('SELECT `groups` FROM `'.$prefix.'_modules` WHERE `groups` = "0"');
	if ($db->sql_numrows($resultbc) != 0) {
		$sql = 'UPDATE `'.$prefix.'_modules` SET `groups` = "" WHERE `groups` = "0"';
		sqlexec($sql);
	}
	$resultbc = $db->sql_query('SELECT `admins` FROM `'.$prefix.'_modules` WHERE `admins` = "0"');
	if ($db->sql_numrows($resultbc) != 0) {
		$sql = 'UPDATE `'.$prefix.'_modules` SET `admins` = "" WHERE `admins` = "0"';
		sqlexec($sql);
	}
	$resultbc = $db->sql_query('SELECT `id` FROM `'.$prefix.'_groups` WHERE `id` = "1"');
	if ($db->sql_numrows($resultbc) == 0) {
		$sql = 'UPDATE `'.$prefix.'_modules` SET `mod_group` = "0" WHERE `mod_group` = "1"';
		sqlexec($sql);
	}

	//Install nukeDH
	$sql = 'SHOW TABLES LIKE \''.$prefix.'_seo_dh\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_seo_dh` (`dhid` int(11) NOT NULL auto_increment, `levelsort` int(1) NOT NULL, `title` varchar(255) NOT NULL, '
		.'`id` int(11) NOT NULL, `mid` int(11) NOT NULL, `lang` varchar(30) NOT NULL, `active` int(1) NOT NULL, '
		.'`metavalue` text NOT NULL, PRIMARY KEY (`dhid`), KEY `levelsort` (`levelsort`,`title`,`id`,`mid`)) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_seo_dh` (`dhid`, `levelsort`, `title`, `id`, `mid`, `lang`, `active`, `metavalue`) VALUES '
		.'(1, 0, \'\', 0, 1, \'\', 1, \'%slogan% - %sitename%\'), '
		.'(2, 0, \'\', 0, 5, \'\', 1, \'text/javascript\'), '
		.'(3, 0, \'\', 0, 6, \'\', 1, \'text/css\'), '
		.'(4, 0, \'\', 0, 8, \'\', 1, \'0\'), '
		.'(5, 0, \'\', 0, 16, \'\', 1, \'DOCUMENT\'), '
		.'(6, 0, \'\', 0, 17, \'\', 1, \'GLOBAL\'), '
		.'(7, 0, \'\', 0, 18, \'\', 1, \'%sitename%\'), '
		.'(8, 0, \'\', 0, 19, \'\', 1, \'Copyright (c) %year% by %sitename%\'), '
		.'(9, 0, \'\', 0, 20, \'\', 1, \'Welcome to %sitename%.  %slogan%\'), '
		.'(10, 0, \'\', 0, 21, \'\', 1, \'RavenNuke, news, technology, headlines, nuke, phpnuke, php-nuke, CMS, content management system\'), '
		.'(11, 0, \'\', 0, 22, \'\', 1, \'INDEX, FOLLOW\'), '
		.'(12, 0, \'\', 0, 23, \'\', 1, \'1 DAY\'), '
		.'(13, 0, \'\', 0, 24, \'\', 1, \'GENERAL\'), '
		.'(14, 0, \'\', 0, 25, \'\', 1, \'RavenNuke(tm) Copyright (c) 2002-2009 by Gaylen Fraley. This is free software, and you may redistribute it under the GPL '
		.'(http://www.gnu.org/licenses/gpl-2.0.txt). RavenNuke(tm) is supported by the RavenNuke(tm) Team at http://www.ravenphpscripts.com .\')';
		sqlexec($sql);
	}

	$sql = 'SHOW TABLES LIKE \''.$prefix.'_seo_dh_master\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'CREATE TABLE `'.$prefix.'_seo_dh_master` (`mid` int(11) NOT NULL auto_increment, `order` int(5) NOT NULL, `type` varchar(50) NOT NULL, '
		.'`name` varchar(50) NOT NULL, `default` varchar(255) NOT NULL, `active` int(1) NOT NULL, '
		.'PRIMARY KEY (`mid`), KEY `order` (`order`)) TYPE=MyISAM AUTO_INCREMENT=1';
		sqlexec($sql);
		$sql = 'INSERT INTO `'.$prefix.'_seo_dh_master` (`mid`, `order`, `type`, `name`, `default`, `active`) VALUES '
		.'(1, 0, \'title\', \'title\', \'$slogan\', 1), '
		.'(2, 100, \'http-equiv\', \'refresh\', \'\', 0), '
		.'(3, 200, \'http-equiv\', \'Content-Type\', \'\', 0), '
		.'(4, 300, \'http-equiv\', \'Content-Language\', \'\', 0), '
		.'(5, 400, \'http-equiv\', \'Content-Script-Type\', \'text/javascript\', 1), '
		.'(6, 500, \'http-equiv\', \'Content-Style-Type\', \'text/css\', 1), '
		.'(7, 600, \'http-equiv\', \'Pragma\', \'no-cache\', 1), '
		.'(8, 700, \'http-equiv\', \'Expires\', \'0\', 1), '
		.'(9, 800, \'http-equiv\', \'Ext-cache\', \'\', 0), '
		.'(10, 900, \'http-equiv\', \'set-cookie\', \'\', 0), '
		.'(11, 1000, \'http-equiv\', \'window-target\', \'\', 0), '
		.'(12, 1100, \'http-equiv\', \'PICS-Label\', \'\', 0), '
		.'(13, 1200, \'http-equiv\', \'Cache-Control\', \'\', 0), '
		.'(14, 1300, \'http-equiv\', \'Vary\', \'\', 0), '
		.'(15, 1400, \'name\', \'ROBOTS\', \'NOARCHIVE\', 0), '
		.'(16, 1500, \'name\', \'RESOURCE-TYPE\', \'DOCUMENT\', 1), '
		.'(17, 1600, \'name\', \'DISTRIBUTION\', \'GLOBAL\', 1), '
		.'(18, 1700, \'name\', \'AUTHOR\', \'$sitename\', 1), '
		.'(19, 1800, \'name\', \'COPYRIGHT\', \'Copyright (c) 2009 by $sitename\', 1), '
		.'(20, 1900, \'name\', \'DESCRIPTION\', \'$slogan\', 1), '
		.'(21, 2000, \'name\', \'KEYWORDS\', \'news, technology, headlines, nuke, phpnuke, php-nuke, geek, geeks, hacker, hackers\', 1), '
		.'(22, 2100, \'name\', \'ROBOTS\', \'INDEX, FOLLOW\', 1), '
		.'(23, 2200, \'name\', \'REVISIT-AFTER\', \'1 DAY\', 1), '
		.'(24, 2300, \'name\', \'RATING\', \'GENERAL\', 1), '
		.'(25, 2400, \'name\', \'GENERATOR\', \'RavenNuke(tm) Copyright (c) 2002-2009 by Gaylen Fraley. This is free software, and you may redistribute it under the GPL '
		.'(http://www.gnu.org/licenses/gpl-2.0.txt). RavenNuke(tm) is supported by the RavenNuke(tm) Team at http://www.ravenphpscripts.com .\', 1)';
		sqlexec($sql);
	}
	//Insert or update the nukeNAV _modules DB data to ensure the module is "active", which is required for proper operation.
	$sql = 'SELECT * FROM '.$prefix.'_modules WHERE `title` = \'nukeNAV\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'INSERT INTO `'.$prefix.'_modules` VALUES (NULL, \'nukeNAV\', \'NukeNav\', 1, 0, \'\', 0, 0, \'\');';
		sqlexec($sql);
	} else {
		$sql = 'UPDATE `'.$prefix.'_modules` SET `active` = 1 WHERE `title` = \'nukeNAV\'';
		sqlexec($sql);
	}
	//end nukeDH

	//Fix nukeFEED subscriptions table field sizing
	$sql = 'ALTER TABLE '.$prefix.'_seo_subscriptions CHANGE `type` `type` varchar(255) NOT NULL';
	sqlexec($sql);
	$sql = 'ALTER TABLE '.$prefix.'_seo_subscriptions CHANGE `image` `image` varchar(255) NOT NULL';
	sqlexec($sql);
	$sql = 'ALTER TABLE '.$prefix.'_seo_subscriptions CHANGE `icon` `icon` varchar(255) NOT NULL';
	sqlexec($sql);
	$sql = 'ALTER TABLE '.$prefix.'_seo_subscriptions CHANGE `url` `url` varchar(255) NOT NULL';
	sqlexec($sql);

	//Fix Newsletter module in modules table  if "_" is missing in title
	$resultbc = $db->sql_query('SELECT `title` FROM `'.$prefix.'_modules` WHERE `title` = \'HTML Newsletter\'');
	if ($resultbc) {
		$resultbc = $db->sql_query('SELECT `title` FROM `'.$prefix.'_modules` WHERE `title` = \'HTML_Newsletter\'');
		if ($db->sql_numrows($resultbc) == 0) {
			$sql = 'UPDATE `'.$prefix.'_modules` SET `title` = \'HTML_Newsletter\' WHERE `title` = \'HTML Newsletter\'';
			sqlexec($sql);
		} else {
			$sql = 'DELETE FROM `'.$prefix.'_modules` WHERE `title` = \'HTML Newsletter\'';
			sqlexec($sql);
		}
	}

	$already_ran = true;
	return;
}
/**
 * Performs updates needed to go from 2.40.00 to 2.40.01
 */
function rn24000() {
	static $already_ran;
	if (isset($already_ran)) return 1;
	global $prefix, $db, $user_prefix, $dbErrors;

	//0001637: Redundant Indexes where already have primary keys on a few tables
	//Yeah, I know, its not perfect, but this should catch the 99% set ups
	$sql = 'SHOW INDEX FROM `'.$prefix.'_newpages` WHERE `key_name` = \'pid\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) > 0) {
		$sql = 'ALTER TABLE `'.$prefix.'_newpages` DROP INDEX `pid`';
		sqlexec($sql);
	}
	$sql = 'SHOW INDEX FROM `'.$prefix.'_nsnpj_members` WHERE `key_name` = \'member_id\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) > 0) {
		$sql = 'ALTER TABLE `'.$prefix.'_nsnpj_members` DROP INDEX `member_id`';
		sqlexec($sql);
	}
	$sql = 'SHOW INDEX FROM `'.$prefix.'_nsnpj_members_positions` WHERE `key_name` = \'position_id\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) > 0) {
		$sql = 'ALTER TABLE `'.$prefix.'_nsnpj_members_positions` DROP INDEX `position_id`';
		sqlexec($sql);
	}

	//0001642: Add indexes to nuke_session and nuke_users_field_values
	//Yeah, I know, its not perfect, but this should catch the 99% set ups
	$sql = 'SHOW INDEX FROM `'.$prefix.'_session` WHERE `column_name` = \'uname\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'ALTER TABLE `'.$prefix.'_session` ADD INDEX (`uname`)';
		sqlexec($sql);
	}
	$sql = 'SHOW INDEX FROM `'.$user_prefix.'_users_field_values` WHERE `column_name` = \'fid\' OR `column_name` = \'uid\'';
	$result1 = sqlexec($sql);
	if ($db->sql_numrows($result1) == 0) {
		$sql = 'ALTER TABLE `'.$user_prefix.'_users_field_values` ADD INDEX `combo_fid_uid` (`fid`,`uid`)';
		sqlexec($sql);
	}

	$already_ran = true;
	return;
}
?>
</body>
</html>
