<?php

include("mainfile.php");
global $prefix, $db, $nukeurl;
header("Content-Type: text/xml");


$result = $db->sql_query("SELECT pid, title, counter FROM ".$prefix."_pages ORDER BY pid DESC LIMIT 0,10");

$btitle ="$sitename - New Content";

echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n\n";
echo "<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\"\n";
echo " \"http://my.netscape.com/publish/formats/rss-0.91.dtd\">\n\n";
echo "<rss version=\"0.91\">\n\n";
echo "<channel>\n";
echo "<title>".htmlspecialchars($sitename)."</title>\n";
echo "<link>$nukeurl</link>\n";
echo "<description>".htmlspecialchars($btitle)."</description>\n";
echo "<language>$backend_language</language>\n\n";


while ($row = $db->sql_fetchrow($result)) {
 	$transfertitle = str_replace(" ", "_", $row[title]);
    echo "<item>\n";
    echo "<title>".htmlspecialchars($row[title])."</title>\n";
    echo "<link>$nukeurl/modules.php?name=Content&pa=showpage&pid=$row[pid]</link>\n";
    echo "</item>\n\n";
}


echo "</channel>\n";
echo "</rss>";


?>