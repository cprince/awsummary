<?php

require_once("config.php");

$link = mysql_connect($host, $user, $passwd) or die('Could not connect: ' . mysql_error());
mysql_select_db($db) or die('Could not select database');

// typical filename: awstats082010.jbc.library.utoronto.ca.txt where jbc is the journal path

$filename = $argv[1];
$awname = basename($filename);
$firstdot = 14;
$seconddot = strpos($awname,'.',14);

$month = (int)substr($awname,7,2);
$year = (int)substr($awname,9,4);
$jpath = substr($awname,$firstdot,$seconddot-$firstdot);
$journal_id = $journals[$jpath];

$itemplate = "insert into $summarytablename (journal_id, year, month, section, rank, value1, value2, value3, value4)
                 values (%d, %d, %d, '%s', %d, '%s', '%s', '%s', '%s');";

if (!$f = fopen($filename,"r"))
	die("could not open file\n");

do {
    $line = trim(fgets($f));
    if ($line{0}=="#") continue;
    list($sectionName, $sectionPos) = explode(" ", $line);
    if ($sectionName == 'POS_GENERAL') $generalPos = $sectionPos;
    if ($sectionName == 'POS_DOMAIN') $domainPos = $sectionPos;
    if ($sectionName == 'POS_SEREFERRALS') $serefPos = $sectionPos;
    if ($sectionName == 'POS_SIDER') $pagePos = $sectionPos;
    if ($sectionName == 'POS_PAGEREFS') $pagerefPos = $sectionPos;
    if ($sectionName == 'POS_DAY') $dayPos = $sectionPos;
    if ($sectionName == 'POS_SEARCHWORDS') $searchWordsPos = $sectionPos;
    if ($sectionName == 'POS_PLUGIN_geoip_city_maxmind') $cityPos = $sectionPos;
} while ($line != 'END_MAP');


function processSection($f,$thePos,$beginSTR,$endSTR,$section) {
    global $summarytablename;
    global $itemplate;
    global $journal_id;
    global $year;
    global $month;
    fseek($f, $thePos);
    $rank = 0;

    // first remove that journal's existing section data for the month
    $deleter = sprintf("delete from %s where journal_id=%d and year=%d and month=%d and section='%s';",
                            $summarytablename, $journal_id, $year, $month, $section);

    print($deleter);
    echo "\n\n";

    $result = mysql_query($deleter);
    print(mysql_error());

    do {
        $line = trim(fgets($f));
        if ($line{0} == "#") continue;
        list($value1, $value2, $value3, $value4) = explode(" ", $line);
        if ($value1 == $beginSTR) continue;
        if ($value1 == $endSTR) continue;
        $rank++;

        $inserter = sprintf($itemplate, $journal_id, $year, $month, $section, $rank,
            mysql_real_escape_string($value1), mysql_real_escape_string($value2),
            mysql_real_escape_string($value3), mysql_real_escape_string($value4));

        print($inserter);
        echo "\n\n";

        $result = mysql_query($inserter);
        print(mysql_error());

    } while ($line != $endSTR);
}


processSection($f,$generalPos,"BEGIN_GENERAL","END_GENERAL","General");
processSection($f,$domainPos,"BEGIN_DOMAIN","END_DOMAIN","Domain");
processSection($f,$serefPos,"BEGIN_SEREFERRALS","END_SEREFERRALS","Search Engines");
processSection($f,$pagePos,"BEGIN_SIDER","END_SIDER","Pages");
processSection($f,$pagerefPos,"BEGIN_PAGEREFS","END_PAGEREFS","Page Refs");
processSection($f,$dayPos,"BEGIN_DAY","END_DAY","Days of the Month");
processSection($f,$searchWordsPos,"BEGIN_SEARCHWORDS","END_SEARCHWORDS","Search Keywords");
processSection($f,$cityPos,"BEGIN_PLUGIN_geoip_city_maxmind","END_PLUGIN_geoip_city_maxmind","GeoIP Cities");

?>

