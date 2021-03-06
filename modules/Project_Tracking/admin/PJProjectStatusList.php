<?php

/***********************************************************/
/* NukeScripts Network (webmaster@nukescripts.net) 			*/
/* http://www.nukescripts.net 						*/
/* Copyright � 2000-2005 by NukeScripts Network 			*/
/***********************************************************/
/*"I�t�rn�ti�n�liz�ti�n"							*/
/* Project Tracking 					 			*/
/* http://www.ravennuke.com	 						*/
/* Copyright � 2009 by RavenNuke�		 			*/
/* Author: Palbin (matt@phpnuke-guild.org)					*/
/* Description of changes: Made 100% XHTML 1.0 Transitional	*/
/*	Compliant.  Bugs fixes and major code formating changes	*/
/***********************************************************/

if (!defined('ADMIN_FILE')) {
	die ('Access Denied');
}

$pagetitle = ': ' . _PJ_TITLE . ': ' . _PJ_PROJECTS . ': ' . _PJ_STATUSLIST;

include('header.php');

pjadmin_menu(_PJ_PROJECTS . ': ' . _PJ_STATUSLIST);
echo '<br />'."\n";

$statusresult = $db->sql_query('SELECT * FROM ' . $prefix . '_nsnpj_projects_status WHERE status_weight > 0 ORDER BY status_weight');
$status_total = $db->sql_numrows($statusresult);

OpenTable();
echo '<table width="100%" border="1" cellspacing="0" cellpadding="2">'."\n";
echo '<tr><td colspan="3" width="100%" bgcolor="' . $bgcolor2 . '" nowrap="nowrap"><b>' . _PJ_STATUSOPTIONS . '</b></td></tr>'."\n";

$pjimage = pjimage('options.png', $module_name);
echo '<tr><td><img src="' . $pjimage. '" alt="" title="" /></td><td colspan="2" width="100%" nowrap="nowrap"><a href="' . $admin_file . '.php?op=PJProjectStatusAdd">' . _PJ_STATUSADD . '</a></td></tr>'."\n";

$pjimage = pjimage("stats.png", $module_name);
echo '<tr><td><img src="' . $pjimage. '" alt="" title="" /></td><td colspan="2" width="100%" nowrap="nowrap">' . _PJ_TOTALPROJECTSTATUSES . ': <b>' . $status_total . '</b></td></tr>'."\n";
echo '</table>'."\n";
echo '<br />'."\n";
echo '<table width="100%" border="1" cellspacing="0" cellpadding="2">'."\n";
echo '<tr><td colspan="2" bgcolor="' . $bgcolor2 . '" width="100%"><b>' . _PJ_STATUSES . '</b></td>'."\n";
echo '<td align="center" bgcolor="' . $bgcolor2 . '"><b>' . _PJ_WEIGHT . '</b></td>'."\n";
echo '<td align="center" bgcolor="' . $bgcolor2 . '"><b>' . _PJ_FUNCTIONS . '</b></td></tr>'."\n";

if($status_total != 0) {
	while($status_row = $db->sql_fetchrow($statusresult)) {
		$pjimage = pjimage("status.png", $module_name);
		echo '<tr><td><img src="' . $pjimage. '" alt="" title="" /></td><td width="100%">' . $status_row['status_name'] . '</td>'."\n";

		$weight1 = $status_row['status_weight'] - 1;
		$weight3 = $status_row['status_weight'] + 1;

		list($pid1) = $db->sql_fetchrow($db->sql_query('SELECT status_id FROM ' . $prefix . '_nsnpj_projects_status WHERE status_weight="' . $weight1 . '"'));
		list($pid2) = $db->sql_fetchrow($db->sql_query('SELECT status_id FROM ' . $prefix . '_nsnpj_projects_status WHERE status_weight="' . $weight3 . '"'));

		echo '<td align="center" nowrap="nowrap">';

		if($pid1 AND $pid1 > 0) {
			echo '<a class="rn_csrf" href="' . $admin_file . '.php?op=PJProjectStatusOrder&amp;weight=' . $status_row['status_weight'] . '&amp;pid=' . $status_row['status_id'] . '&amp;weightrep=' . $weight1 . '&amp;pidrep=' . $pid1 . '"><img src="modules/' . $module_name . '/images/weight_up.png" border="0" hspace="3" alt="' . _PJ_UP . '" title="' . _PJ_UP . '" /></a>';
		} else {
			echo '<img src="modules/' . $module_name . '/images/weight_up_no.png" border="0" hspace="3" alt="" title="" />';
		}

		if($pid2) {
			echo '<a class="rn_csrf" href="' . $admin_file . '.php?op=PJProjectStatusOrder&amp;weight=' . $status_row['status_weight'] . '&amp;pid=' . $status_row['status_id'] . '&amp;weightrep=' . $weight3 . '&amp;pidrep=' . $pid2 . '"><img src="modules/' . $module_name . '/images/weight_dn.png" border="0" hspace="3" alt="' . _PJDOWN . '" title="' . _PJ_DOWN . '" /></a>';
		} else {
			echo '<img src="modules/' . $module_name . '/images/weight_dn_no.png" border="0" hspace="3" alt="" title="" />';
		}

		echo '</td>'."\n";
		echo '<td align="center" nowrap="nowrap">[ <a href="' . $admin_file . '.php?op=PJProjectStatusEdit&amp;status_id=' . $status_row['status_id'] . '">' . _PJ_EDIT . '</a>';
		echo ' | <a href="' . $admin_file . '.php?op=PJProjectStatusRemove&amp;status_id=' . $status_row['status_id'] . '">' . _PJ_DELETE . '</a> ]</td></tr>'."\n";
	}

	echo '<tr><td align="center" colspan="4"><a class="rn_csrf" href="' . $admin_file . '.php?op=PJProjectStatusFix">' . _PJ_FIXWEIGHT . '</a></td></tr>'."\n";
} else {
	echo '<tr><td width="100%" colspan="3" align="center">' . _PJ_NOPROJECTSTATUS . '</td></tr>'."\n";
}

echo '</table>'."\n";
CloseTable();

pj_copy();

include('footer.php');

?>