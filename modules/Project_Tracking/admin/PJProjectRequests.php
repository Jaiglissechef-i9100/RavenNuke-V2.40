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

$pagetitle = ': ' . _PJ_TITLE . ': ' . _PJ_PROJECTS . ': ' . _PJ_REQUESTLIST;

include('header.php');

$projectresult = $db->sql_query('SELECT project_name, project_description, status_id, priority_id FROM ' . $prefix . '_nsnpj_projects WHERE project_id="' . $project_id . '"');
list($project_name, $project_description, $status_id, $priority_id) = $db->sql_fetchrow($projectresult);

pjadmin_menu(_PJ_PROJECTS . ': ' . _PJ_REQUESTLIST);
echo '<br />'."\n";

$requestresult = $db->sql_query('SELECT request_id, request_name, type_id, status_id FROM ' . $prefix . '_nsnpj_requests WHERE project_id="' . $project_id . '" ORDER BY request_name');

$typeresult = $db->sql_query('SELECT * FROM ' . $prefix . '_nsnpj_reports_types WHERE type_weight > 0');
$type_total = $db->sql_numrows($typeresult);

OpenTable();
echo '<table width="100%" border="1" cellspacing="0" cellpadding="2">'."\n";
echo '<tr><td colspan="2" bgcolor="' . $bgcolor2 . '" width="100%" nowrap="nowrap"><b>' . _PJ_PROJECT . '</b></td>'."\n";
echo '<td align="center" bgcolor="' . $bgcolor2 . '"><b>' . _PJ_STATUS . '</b></td>'."\n";
echo '<td align="center" bgcolor="' . $bgcolor2 . '"><b>' . _PJ_PRIORITY . '</b></td>'."\n";
echo '<td align="center" bgcolor="' . $bgcolor2 . '"><b>' . _PJ_FUNCTIONS . '</b></td></tr>'."\n";

$pjimage = pjimage('project.png', $module_name);
echo '<tr><td><img src="' . $pjimage. '" alt="" title="" /></td>'."\n";
echo '<td width="100%"><a href="' . $admin_file . '.php?op=PJProjectList">' . _PJ_PROJECTS . '</a> / <b>' . $project_name . '</b></td>'."\n";

$projectstatus = pjprojectstatus_info($status_id);
if($projectstatus['status_name'] == '') {
	$projectstatus['status_name'] = _PJ_NA;
}

echo '<td align="center"><a href="' . $admin_file . '.php?op=PJProjectStatusList">' . $projectstatus['status_name'] . '</a></td>'."\n";

$projectpriority = pjprojectpriority_info($priority_id);
if($projectpriority['priority_name'] == '') {
	$projectpriority['priority_name'] = _PJ_NA;
}

echo '<td align="center"><a href="' . $admin_file . '.php?op=PJProjectPriorityList">' . $projectpriority['priority_name'] . '</a></td>'."\n";
echo '<td align="center" nowrap="nowrap">[ <a href="' . $admin_file . '.php?op=PJProjectEdit&amp;project_id=' . $project_id . '">' . _PJ_EDIT . '</a> |';
echo ' <a href="' . $admin_file . '.php?op=PJProjectRemove&amp;project_id=' . $project_id . '">' . _PJ_DELETE . '</a> ]</td></tr>'."\n";
echo '<tr><td colspan="5" width="100%" bgcolor="' . $bgcolor2 . '" nowrap="nowrap"><b>' . _PJ_PROJECTOPTIONS . '</b></td></tr>'."\n";

$pjimage = pjimage('options.png', $module_name);
echo '<tr><td><img src="' . $pjimage. '" alt="" title="" /></td><td colspan="4" width="100%" nowrap="nowrap"><a href="' . $admin_file . '.php?op=PJTaskAdd&amp;project_id=' . $project_id . '">' . _PJ_TASKADD . '</a></td></tr>'."\n";

$pjimage = pjimage('stats.png', $module_name);
echo '<tr><td><img src="' . $pjimage. '" alt="" title="" /></td><td colspan="4" width="100%" nowrap="nowrap">' . _PJ_TOTALTASKS . ': <b>' . $task_total . '</b></td></tr>'."\n";
echo '</table>'."\n";
CloseTable();
echo '<br />'."\n";
OpenTable();
echo '<table width="100%" border="1" cellspacing="0" cellpadding="2">'."\n";
echo '<tr><td colspan="2" bgcolor="' . $bgcolor2 . '" width="100%"><b>' . _PJ_PROJECTREQUESTS . '</b></a></td>'."\n";
echo '<td align="center" bgcolor="' . $bgcolor2 . '"><b>' . _PJ_STATUS . '</b></td>'."\n";
echo '<td align="center" bgcolor="' . $bgcolor2 . '"><b>' . _PJ_TYPE . '</b></td>'."\n";
echo '<td align="center" bgcolor="' . $bgcolor2 . '"><b>' . _PJ_FUNCTIONS . '</b></td></tr>'."\n";

if($request_total != 0) {
	while(list($request_id, $request_name, $type_id, $status_id) = $db->sql_fetchrow($requestresult)) {
		$pjimage = pjimage('request.png', $module_name);
		echo '<tr><td><img src="' . $pjimage . '" alt="" title="" /></td>'."\n";
		echo '<td width="100%">' . $request_name . '</td>'."\n";

		$status = pjrequeststatus_info($status_id);
		if($status['status_name'] == '') {
			$status['status_name'] = '<i>N/A</i>';
		}

		echo '<td align="center"><a href="' . $admin_file . '.php?op=PJRequestStatusList">' . $status['status_name'] . '</a></td>'."\n";

		$type = pjrequesttype_info($type_id);
		if($type['type_name'] == '') {
			$type['type_name'] = '<i>N/A</i>';
		}

		echo '<td align="center"><a href="' . $admin_file . '.php?op=PJRequestTypeList">' . $type['type_name'] . '</a></td>'."\n";
		echo '<td align="center" nowrap="nowrap">[ <a href="' . $admin_file . '.php?op=PJRequestEdit&amp;request_id=' . $request_id . '">' . _PJ_EDIT . '</a>';
		echo ' | <a href="' . $admin_file . '.php?op=PJRequestRemove&amp;request_id=' . $request_id . '">' . _PJ_DELETE . '</a> ]</td>'."\n";
		echo '</tr>'."\n";
	}
} else {
	echo '<tr><td width="100%" colspan="4" align="center">' . _PJ_NOPROJECTREQUESTS . '</td></tr>'."\n";
}

echo '</table>'."\n";
CloseTable();

pj_copy();

include('footer.php');

?>