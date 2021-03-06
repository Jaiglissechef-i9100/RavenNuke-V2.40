<?php

/***************************************************************************/
/* PHP-NUKE: Web Portal System									*/
/* ===========================									*/
/*														*/
/* Copyright (c) 2002 by Francisco Burzi								*/
/* http://phpnuke.org											*/
/*														*/
/* ========================									*/
/* Based on PHP-Nuke Add-On										*/
/* Copyright (c) 2001 by Richard Tirtadji AKA King Richard					*/
/*							  (rtirtadji@hotmail.com)			*/
/*							  Hutdik Hermawan AKA hotFix		*/
/*							  (hutdik76@hotmail.com)			*/
/* http://www.nukeaddon.com										*/
/*														*/
/* This program is free software. You can redistribute it and/or modify 			*/
/* it under the terms of the GNU General Public License as published by 			*/
/* the Free Software Foundation; either version 2 of the License.				*/
/*														*/
/***************************************************************************/
/*			Additional security & Abstraction layer conversion			*/
/*									2003 chatserv			*/
/*		http://www.nukefixes.com -- http://www.nukeresources.com			*/
/***************************************************************************/
###################################################
#
# nukeWYSIWYG Copyright (c) 2005 Kevin Guske	http://nukeseo.com
# kses developed by Ulf Harnhammar			http://kses.sf.net
# kses ideas contributed by sixonetonoffun		http://netflake.com
# FCKeditor by Frederico Caldeira Knabben		http://fckeditor.net
# Original FCKeditor for PHP-Nuke by H.Theisen  http://phpnuker.de
#
###################################################

if ( !defined('ADMIN_FILE') ) {
	die ('Access Denied');
}

global $prefix, $db, $admin_file;

$aid = substr("$aid", 0,25);
$row = $db->sql_fetchrow($db->sql_query("SELECT title, admins FROM ".$prefix."_modules WHERE title='FAQ'"));
$row2 = $db->sql_fetchrow($db->sql_query("SELECT name, radminsuper FROM ".$prefix."_authors WHERE aid='$aid'"));
$admins = explode(",", $row['admins']);
$auth_user = 0;
for ($i=0; $i < sizeof($admins); $i++) {
	if ($row2['name'] == "$admins[$i]" AND !empty($row['admins'])) {
		$auth_user = 1;
	}
}

if ($row2['radminsuper'] == 1 || $auth_user == 1) {

	/***********************************************************/
	/* Faq Admin Function								*/
	/***********************************************************/

	function FaqAdmin() {
		global $admin, $bgcolor2, $prefix, $db, $currentlang, $multilingual, $admin_file;
		include_once("header.php");
		GraphicAdmin();
		OpenTable();
			echo "<center><font class=\"title\"><b>" . _FAQADMIN . "</b></font></center>";
		CloseTable();
		echo "<br />";
		OpenTable();
			echo "<center><font class=\"option\"><b>" . _ACTIVEFAQS . "</b></font></center><br />"
			."<table border=\"1\" width=\"100%\" align=\"center\"><tr>"
			."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>" . _ID . "</b></td>"
			."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>" . _CATEGORIES . "</b></td>"
			."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>" . _LANGUAGE . "</b></td>"
			."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>" . _FUNCTIONS . "</b></td></tr>";
			if (empty($flanguage)) {
				$flanguage = _ALL;
			}
			$result = $db->sql_query("select id_cat, categories, flanguage from ".$prefix."_faqcategories order by id_cat");
			while ($row = $db->sql_fetchrow($result)) {
				$id_cat = $row['id_cat'];
				$categories = $row['categories'];
				$flanguage = $row['flanguage'];
				echo "<tr><td align=\"center\">$id_cat</td>"
				."<td align=\"center\">$categories</td>"
				."<td align=\"center\">$flanguage</td>"
				."<td align=\"center\">[ <a href=\"".$admin_file.".php?op=FaqCatGo&amp;id_cat=$id_cat\">" . _CONTENT . "</a> | <a href=\"".$admin_file.".php?op=FaqCatEdit&amp;id_cat=$id_cat\">" . _EDIT . "</a> | <a class=\"rn_csrf\" href=\"".$admin_file.".php?op=FaqCatDel&amp;id_cat=$id_cat&amp;ok=0\">" . _DELETE . "</a> ]</td></tr>";
			}
			echo "</table>";
		CloseTable();
		echo "<br />";
		OpenTable();
			echo "<center><font class=\"option\"><b>" . _ADDCATEGORY . "</b></font></center><br />"
			."<form action=\"".$admin_file.".php\" method=\"post\">"
			."<table border=\"0\" width=\"100%\"><tr><td>"
			."" . _CATEGORIES . ":</td><td><input type=\"text\" name=\"categories\" size=\"30\" /></td>";
			if ($multilingual == 1) {
				echo "<td>" . _LANGUAGE . ":</td><td>"
				."<select name=\"flanguage\">";
				$handle=opendir('language');
				$languageslist = '';
				while ($file = readdir($handle)) {
					if (preg_match("/^lang\-(.+)\.php/", $file, $matches)) {
						$langFound = $matches[1];
						$languageslist .= "$langFound ";
					}
				}
				closedir($handle);
				$languageslist = explode(" ", $languageslist);
				sort($languageslist);
				for ($i=0; $i < sizeof($languageslist); $i++) {
					if(!empty($languageslist[$i])) {
						echo "<option value=\"$languageslist[$i]\" ";
						if($languageslist[$i]==$currentlang) echo "selected=\"selected\"";
						echo ">".ucfirst($languageslist[$i])."</option>\n";
					}
				}
				echo "</select></td>";
			} else {
				echo "<td><input type=\"hidden\" name=\"flanguage\" value=\"$flanguage\" /></td>";
			}
			echo "</tr></table>"
			."<input type=\"hidden\" name=\"op\" value=\"FaqCatAdd\" />"
			."<input type=\"submit\" value=\""._SAVE."\" />"
			."</form>";
		CloseTable();
		include_once("footer.php");
	}

	function FaqCatGo($id_cat) {
		global $admin, $bgcolor2, $prefix, $db, $admin_file;

		include_once("header.php");
		GraphicAdmin();
		OpenTable();
		echo "<center><font class=\"title\"><b>" . _FAQADMIN . "</b></font></center>";
		CloseTable();
		echo "<br />";
		OpenTable();
		echo "<center><font class=\"option\"><b>" . _QUESTIONS . "</b></font></center><br />"
		."<table border=\"1\" width=\"100%\" align=\"center\"><tr>"
		."<td bgcolor=\"$bgcolor2\" align=\"center\">" . _CONTENT . "</td>"
		."<td bgcolor=\"$bgcolor2\" align=\"center\">" . _FUNCTIONS . "</td></tr>";

		$id_cat = intval($id_cat);
		$result = $db->sql_query("select id, question, answer from ".$prefix."_faqanswer where id_cat='$id_cat' order by id");
		while ($row = $db->sql_fetchrow($result)) {
		$id = intval($row['id']);
		$question = $row['question'];
		$answer = $row['answer'];

		echo "<tr><td><i>$question</i><br /><br />$answer"
			."</td><td align=\"center\">[ <a href=\"".$admin_file.".php?op=FaqCatGoEdit&amp;id=$id\">" . _EDIT . "</a> | <a class=\"rn_csrf\" href=\"".$admin_file.".php?op=FaqCatGoDel&amp;id=$id&amp;ok=0\">" . _DELETE . "</a> ]</td></tr>"
			."</tr>";
		 }
		 echo "</table>";
		 CloseTable();
		 echo "<br />";
		 OpenTable();
		 echo "<center><font class=\"option\"><b>" . _ADDQUESTION . "</b></font></center><br />"
		."<form action=\"".$admin_file.".php\" method=\"post\">"
		."<table border=\"0\" width=\"100%\"><tr><td>"
		."" . _QUESTION . ":</td><td><input type=\"text\" name=\"question\" size=\"40\" /></td></tr><tr><td>"
		."" . _ANSWER . " </td><td>";
		wysiwyg_textarea("answer", "", "PHPNukeAdmin", "60", "10");
		echo "</td></tr></table>"
		."<input type=\"hidden\" name=\"id_cat\" value=\"$id_cat\" />"
		."<input type=\"hidden\" name=\"op\" value=\"FaqCatGoAdd\" />"
		."<input type=\"submit\" value=\""._SAVE."\" /> " . _GOBACK . ""
		."</form>";
		 CloseTable();
		 include_once("footer.php");
	}

	function FaqCatEdit($id_cat) {
		global $admin, $db, $multilingual, $admin_file;
		include_once("config.php");
		include_once("header.php");
		GraphicAdmin();
		OpenTable();
		echo "<center><font class=\"title\"><b>" . _FAQADMIN . "</b></font></center>";
		CloseTable();
		echo "<br />";

		$id_cat = intval($id_cat);
		$row = $db->sql_fetchrow($db->sql_query("SELECT categories, flanguage from " . $prefix . "_faqcategories where id_cat='$id_cat'"));
		$categories = $row['categories'];
		$flanguage = $row['flanguage'];

		OpenTable();
		echo "<center><font class=\"option\"><b>" . _EDITCATEGORY . "</b></font></center>"
		."<form action=\"".$admin_file.".php\" method=\"post\">"
		."<input type=\"hidden\" name=\"id_cat\" value=\"$id_cat\" />"
		."<table border=\"0\" width=\"100%\"><tr><td>"
		."" . _CATEGORIES . ":</td><td><input type=\"text\" name=\"categories\" size=\"31\" value='$categories' /></td>";
		if ($multilingual == 1) {
			echo "<td>" . _LANGUAGE . ":</td><td>"
				."<select name=\"flanguage\">";

			$handle=opendir('language');
			$languageslist = '';
			while ($file = readdir($handle)) {
				 if (preg_match("/^lang\-(.+)\.php/", $file, $matches)) {
					  $langFound = $matches[1];
					  $languageslist .= "$langFound ";
				 }
			}
			closedir($handle);
			$languageslist = explode(" ", $languageslist);
			sort($languageslist);
			for ($i=0; $i < sizeof($languageslist); $i++) {
			if(!empty($languageslist[$i])) {
				echo "<option value=\"$languageslist[$i]\" ";
				if($languageslist[$i]==$flanguage) echo "selected=\"selected\"";
				echo ">".ucfirst($languageslist[$i])."</option>\n";
				 }
			}
			echo "</select></td>";
		} else {
			echo "<input type=\"hidden\" name=\"flanguage\" value=\"$flanguage\" />";
		}

		echo "</tr></table>"
		."<input type=\"hidden\" name=\"op\" value=\"FaqCatSave\" />"
		."<input type=\"submit\" value=\""._SAVE."\" /> "._GOBACK.""
		."</form>";
		 CloseTable();
		 include_once("footer.php");
	}

	function FaqCatGoEdit($id) {
		global $admin, $bgcolor2, $prefix, $db, $admin_file;

		include_once("header.php");

		GraphicAdmin();
		OpenTable();
		echo "<center><font class=\"title\"><b>" . _FAQADMIN . "</b></font></center>";
		CloseTable();
		echo "<br />";

		$id = intval($id);
		$row = $db->sql_fetchrow($db->sql_query("SELECT question, answer from " . $prefix . "_faqanswer where id='$id'"));
		$question = $row['question'];
		$answer = $row['answer'];
		OpenTable();
		echo "<center><font class=\"option\"><b>" . _EDITQUESTIONS . "</b></font></center>"
		."<form action=\"".$admin_file.".php\" method=\"post\">"
		."<input type=\"hidden\" name=\"id\" value=\"$id\" />"
		."<table border=\"0\" width=\"100%\"><tr><td>"
		."" . _QUESTION . ":</td><td><input type=\"text\" name=\"question\" size=\"31\" value=\"$question\" /></td></tr><tr><td>"
		."" . _ANSWER . ":</td><td>";
		wysiwyg_textarea("answer", "$answer", "PHPNukeAdmin", "60", "10");
		echo "</td></tr></table>"
		."<input type=\"hidden\" name=\"op\" value=\"FaqCatGoSave\" />"
		."<input type=\"submit\" value=\"". _SAVE."\"  /> " . _GOBACK . ""
		."</form>";
		 CloseTable();
		 include_once("footer.php");
	}

	function FaqCatSave($id_cat, $categories, $flanguage) {
		global $prefix, $db, $admin_file;
		$categories = stripslashes(FixQuotes($categories));
		$id_cat = intval($id_cat);
		$db->sql_query("update ".$prefix."_faqcategories set categories='$categories', flanguage='$flanguage' where id_cat='$id_cat'");
		Header("Location: ".$admin_file.".php?op=FaqAdmin");
	}

	function FaqCatGoSave($id, $question, $answer) {
		global $prefix, $db, $admin_file;
		$question = stripslashes(FixQuotes($question));
		$answer = stripslashes(FixQuotes($answer));
		$id = intval($id);
		$db->sql_query("update ".$prefix."_faqanswer set question='$question', answer='$answer' where id='$id'");
		Header("Location: ".$admin_file.".php?op=FaqAdmin");
	}

	function FaqCatAdd($categories, $flanguage) {
		global $prefix, $db, $admin_file;
		$categories = stripslashes(FixQuotes($categories));
		$db->sql_query("insert into ".$prefix."_faqcategories values (NULL, '$categories', '$flanguage')");
		Header("Location: ".$admin_file.".php?op=FaqAdmin");
	}

	function FaqCatGoAdd($id_cat, $question, $answer) {
		global $prefix, $db, $admin_file;
		$question = stripslashes(FixQuotes($question));
		$answer = stripslashes(FixQuotes($answer));
		$db->sql_query("insert into ".$prefix."_faqanswer values (NULL, '$id_cat', '$question', '$answer')");
		Header("Location: ".$admin_file.".php?op=FaqCatGo&id_cat=$id_cat");
	}

	function FaqCatDel($id_cat, $ok=0) {
		global $prefix, $db, $admin_file;
		if($ok==1) {
			$id_cat = intval($id_cat);
			$db->sql_query("delete from ".$prefix."_faqcategories where id_cat='$id_cat'");
			$db->sql_query("delete from ".$prefix."_faqanswer where id_cat='$id_cat'");
			Header("Location: ".$admin_file.".php?op=FaqAdmin");
		} else {
			include_once("header.php");
			GraphicAdmin();
			OpenTable();
			echo "<center><font class=\"title\"><b>" . _FAQADMIN . "</b></font></center>";
			CloseTable();
			echo "<br />";
			OpenTable();
			echo "<br /><center><b>" . _FAQDELWARNING . "</b><br /><br />";
		}
		echo "[ <a class=\"rn_csrf\" href=\"".$admin_file.".php?op=FaqCatDel&amp;id_cat=$id_cat&amp;ok=1\">" . _YES . "</a> | <a href=\"".$admin_file.".php?op=FaqAdmin\">" . _NO . "</a> ]</center><br /><br />";
		CloseTable();
		include_once("footer.php");
	}

	function FaqCatGoDel($id, $ok=0) {
		global $prefix, $db, $admin_file;
		if($ok==1) {
			$id = intval($id);
			$db->sql_query("delete from ".$prefix."_faqanswer where id='$id'");
			Header("Location: ".$admin_file.".php?op=FaqAdmin");
		} else {
			include_once("header.php");
			GraphicAdmin();
			OpenTable();
			echo "<center><font class=\"title\"><b>" . _FAQADMIN . "</b></font></center>";
			CloseTable();
			echo "<br />";
			OpenTable();
			echo "<br /><center><b>" . _QUESTIONDEL . "</b><br /><br />";
		}
		echo "[ <a class=\"rn_csrf\" href=\"".$admin_file.".php?op=FaqCatGoDel&amp;id=$id&amp;ok=1\">" . _YES . "</a> | <a href=\"".$admin_file.".php?op=FaqAdmin\">" . _NO . "</a> ]</center><br /><br />";
		CloseTable();
		include_once("footer.php");
	}

	switch($op) {
		case 'FaqCatSave':
			csrf_check();
			FaqCatSave($id_cat, $categories, $flanguage);
			break;

		case 'FaqCatGoSave':
			csrf_check();
			FaqCatGoSave($id, $question, $answer);
			break;

		case 'FaqCatAdd':
			csrf_check();
			FaqCatAdd($categories, $flanguage);
			break;

		case 'FaqCatGoAdd':
			csrf_check();
			FaqCatGoAdd($id_cat, $question, $answer);
			break;

		case 'FaqCatEdit':
			FaqCatEdit($id_cat);
			break;

		case 'FaqCatGoEdit':
			FaqCatGoEdit($id);
			break;

		case 'FaqCatDel':
			csrf_check();
			FaqCatDel($id_cat, $ok);
			break;

		case 'FaqCatGoDel':
			csrf_check();
			FaqCatGoDel($id, $ok);
			break;

		case 'FaqAdmin':
			FaqAdmin();
			break;

		case 'FaqCatGo':
			FaqCatGo($id_cat);
			break;
	}

} else {
	include_once('header.php');
	GraphicAdmin();
	OpenTable();
	echo '<center><b>' . _ERROR . '</b><br /><br />You do not have administration permission for module ' . $module_name . '</center>';
	CloseTable();
	include_once('footer.php');
}

?>