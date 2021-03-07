<?php
/******************************************************************************
 ShortStat : Short but Sweet
 View
 v0.50
 
 Created: 	04.03.04
 Updated:	20.03.03
 
 By:		Shaun Inman
 			http://www.shauninman.com/
 Lame port to PHP 8.0 by Paweł Mroczkowski			
 ******************************************************************************/

// Redirect to homepage if linked directly
if (!empty($_SERVER['HTTP_REFERER'])) { header("Location:http://$_SERVER[SERVER_NAME]"); }
 
include_once("configuration.php");
include_once("functions.php");

$main_obj = SI_pconnect();
echo "<!-- ShortStat $SI_display[version] -->";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<meta name="robots" content="noindex,nofollow" />
<title><?php echo $SI_display['sitename']; ?>: Statistics</title>
<style type="text/css" media="all">
/* <![CDATA[ */
@import url(styles.css);
/* ]]> */
</style>
</head>
<body>
<h1>Statistics for <?php echo $SI_display['sitename']; if (!$shortstat) echo ' <em>Disabled</em>'; ?></h1>
<h2></h2>

<div class="column">
	<div class="module waccents">
		<h3>Hits <span>Uniques</span></h3>
		<div><table border="0" cellspacing="0" cellpadding="0">
			<tr><th>Hits</th><th class="last">Uniques</th></tr>
			<tr><td colspan="2" class="accent">Since <?php echo gmdate("g:i a j M Y",SI_getFirstHit($main_obj)+(((gmdate('I'))?($tz_offset+1):$tz_offset)*3600));?></td></tr>
			<tr><td><?php echo SI_getTotalHits($main_obj); ?></td><td class="last"><?php echo SI_getUniqueHits($main_obj); ?></td></tr>
			<tr><td colspan="2" class="accent">Just Today as of <?php echo gmdate("g:i a j M Y",time()+(((gmdate('I'))?($tz_offset+1):$tz_offset)*3600));?></td></tr>
			<tr><td><?php echo SI_getTodaysHits($main_obj); ?></td><td class="last"><?php echo SI_getTodaysUniqueHits($main_obj); ?></td></tr>
		</table></div>
	</div>
	
	<div class="module waccents">
		<h3>Hits in the last week</h3>
		<div><?php echo SI_getWeeksHits($main_obj); ?></div>
	</div>
	
	<div class="module">
		<h3>Platform <span>%</span></h3>
		<div><?php echo SI_getPlatforms($main_obj); ?></div>
	</div>
</div> <!-- CLOSE COLUMN -->

<div class="module">
	<h3>Browser <span>%</span></h3>
	<div><?php echo SI_getBrowsers($main_obj); ?></div>
</div>

<div class="module">
	<h3>Recent Referrers <span>When</span></h3>
	<div><?php echo SI_getReferers($main_obj); ?></div>
</div>

<div class="module">
	<h3>Repeat Referrers <span>Hits</span></h3>
	<div><?php echo SI_getDomains($main_obj); ?></div>
</div>

<div class="module">
	<h3>Resources <span>Hits</span></h3>
	<div><?php echo SI_getResources($main_obj); ?></div>
</div>

<div class="module">
	<h3>Search Strings <span>Hits</span></h3>
	<div><?php echo SI_getKeywords($main_obj); ?></div>
</div>

<?php if (SI_isIPtoCountryInstalled($main_obj)) { ?>
<div class="module">
	<h3>Countries <span>Visits</span></h3>
	<div><?php echo SI_getCountries($main_obj); ?></div>
</div>
<?php }?>

<div class="module">
	<h3>Languages <span>%</span></h3>
	<div><?php echo SI_getLanguage($main_obj); ?></div>
</div>

<div id="donotremove"><?php if (SI_isIPtoCountryInstalled($main_obj)) { ?><br />This application uses the <a href="http://ip-to-country.webhosting.info">IP-to-Country Database</a> provided by <a href="http://www.webhosting.info">WebHosting.Info</a><?php } ?></div>
</body>
</html>
