<?php

/********************************************************/
/* NukeProject(tm)                                      */
/* By: NukeScripts Network (webmaster@nukescripts.net)  */
/* http://www.nukescripts.net                           */
/* Copyright � 2000-2005 by NukeScripts Network         */
/********************************************************/

if(!defined('NSNPJ_ADMIN')) { die("Illegal Access Detected!!!"); }
$pagetitle = ": "._PJ_TITLE.": "._PJ_REQUESTS.": "._PJ_DELETEREQUEST;
include("header.php");
$request = pjrequest_info($request_id);
pjadmin_menu(_PJ_REQUESTS.": "._PJ_DELETEREQUEST);
echo "<br />";
OpenTable();
echo "<form method='post' action='".$admin_file.".php'>";
echo "<table align='center' border='0' cellpadding='2' cellspacing='2'>";
echo "<tr><td align='center'><b>"._PJ_REQUESTCONFIRMDELETE."</b></td></tr>";
echo "<tr><td align='center'><b><i>".$request['request_name'].":</i></b></td></tr>";
echo "<tr><td align='center'><i>".$request['request_description']."</i></td></tr>";
echo "<tr><td align='center'>\n";
echo "<input type='hidden' name='op' value='PJRequestDelete' />";
echo "<input type='hidden' name='request_id' value='$request_id' />";
echo "<input type='hidden' name='project_id' value='".$request['project_id']."' />";
echo "<input type='submit' value='"._PJ_DELETEREQUEST."' />\n";
echo "</td></tr>\n";
echo "</table>";
echo "</form>";
CloseTable();
pj_copy();
include("footer.php");

?>