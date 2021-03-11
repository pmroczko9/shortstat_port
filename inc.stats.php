<?php
/******************************************************************************
 ShortStat : Short but Sweet
 Include
 v0.36b
 
 Created: 	04.03.04
 Updated:	04.11.16
 
 By:		Shaun Inman
 			http://www.shauninman.com/
 ******************************************************************************
 This page should be included in all pages that you would like to track activity
 on, from homepages to secret gardens. It will record information like referrer,
 ip address, the resource being accessed as well as browser and platform.
 ******************************************************************************/
include_once("configuration.php");
include_once("functions.php");

if ($shortstat) {
	$obj_main = SI_pconnect();
	
	$ip	= get_client_ip_server();
	$cntry	= SI_determineCountry($ip);
	$lang	= SI_determineLanguage();
	$ref	= $_SERVER['HTTP_REFERER'];
	$url 	= parse_url($ref);
	$domain	= pregi_replace("/www./i","",$url['host']);
	$res	= $_SERVER['REQUEST_URI'];
	$ua	= $_SERVER['HTTP_USER_AGENT'];
	$br	= SI_parseUserAgent($ua);
	$dt	= time();
	
	SI_sniffKeywords($url);
	
	$query = "INSERT INTO $SI_tables[stats] (remote_ip,country,language,domain,referer,resource,user_agent,platform,browser,version,dt) 
			  VALUES ('$ip','$cntry','$lang','$domain','$ref','$res','$ua','$br[platform]','$br[browser]','$br[version]',$dt)";
	@mysqli_query($obj_main,$query);
	}
?>
