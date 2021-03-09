<?php
/******************************************************************************
 ShortStat : Short but Sweet
 Functions
 v0.36b
 
 Created: 	04.03.04
 Updated:	05.01.20
 
 By:		Shaun Inman
 			http://www.shauninman.com/
 ******************************************************************************
 Database Connection
 ******************************************************************************/
function SI_pconnect(): mysqli {
	global $SI_db;
	//$horribly = "Could not access the database, please make sure that the appropriate values have been added to the configuration file included in this package.";
	$func_obj = @mysqli_connect($SI_db['server'],$SI_db['username'],$SI_db['password']);
	/* check connection */
	if (mysqli_connect_errno()) {
    		printf("Connect failed: %s\n", mysqli_connect_error());
    		exit();
		}
	/* database select */
	if (@!mysqli_select_db($func_obj, $SI_db['database'])) {
		printf("Database: %s select failed!\n", $SI_db['database']);
		exit();
		}
	return $func_obj;
	}


/******************************************************************************
 SI_isIPtoCountryInstalled()
 Confirms the existance of the IP-to-Country database
 ******************************************************************************/
function SI_isIPtoCountryInstalled($func_obj) {
	global $SI_tables;
	$query="SELECT * FROM $SI_tables[countries] LIMIT 0,1";
	return ($result = mysqli_query($func_obj, $query))?mysql_num_rows($result):0;
	}

/******************************************************************************
get_client_ip_server()
Evaluates client's IP address
Taken from https://stackoverflow.com/questions/12553160/getting-visitors-country-from-their-ip
******************************************************************************/
function get_client_ip_server() {
  $ipaddress = '';
if (isset($_SERVER['HTTP_CLIENT_IP']))
  $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
  $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
else if(isset($_SERVER['HTTP_X_FORWARDED']))
  $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
  $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
else if(isset($_SERVER['HTTP_FORWARDED']))
  $ipaddress = $_SERVER['HTTP_FORWARDED'];
else if(isset($_SERVER['REMOTE_ADDR']))
  $ipaddress = $_SERVER['REMOTE_ADDR'];
else
  $ipaddress = 'UNKNOWN';

  return $ipaddress;
}

/******************************************************************************
 SI_determineCountry()
 Determines the viewers country based on their ip address.
 
 This function uses the IP-to-Country Database provided by geoplugin.net 
 (https://www.geoplugin.net), available from 
 https://www.geoplugin.com.
 ******************************************************************************/
function SI_determineCountry($ip) {
	//if (!SI_isIPtoCountryInstalled($func_obj)) return '';
	
	//global $SI_tables;
	$ip = sprintf("%u",ip2long($ip));
	
	//$query = "SELECT country_name FROM $SI_tables[countries]
	//		  WHERE ip_from <= $ip AND
	//		  ip_to >= $ip";
	//if ($result = mysql_query($query)) {
	//	if ($r = mysql_fetch_array($result)) {
	//		return trim(ucwords(preg_replace("/([A-Z\xC0-\xDF])/e","chr(ord('\\1')+32)",$r['country_name'])));
	//		}
	//	}
	$curlSession = curl_init();
    	curl_setopt($curlSession, CURLOPT_URL, 'http://www.geoplugin.net/json.gp?ip='.$ip);
    	curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

    	$jsonData = json_decode(curl_exec($curlSession));
    	curl_close($curlSession);

    	return $jsonData->geoplugin_countryCode;
	}

/******************************************************************************
 SI_sniffKeywords()
 Sniffs out referrals from search engines (see supported list 
 below) and tries to determine the query string.
 
 Currently supported search engines:
 google.xx
 yahoo.xx
 ******************************************************************************/
function SI_sniffKeywords($url) { // $url should be an array created by parse_url($ref)
	global $SI_tables;	
	
	// Check for google first
	if (preg_match("/google\./i", $url['host'])) {
		parse_str($url['query'],$q);
		// Googles search terms are in "q"
		$searchterms = $q['q'];
		}
	else if (preg_match("/alltheweb\./i", $url['host'])) {
		parse_str($url['query'],$q);
		// All the Web search terms are in "q"
		$searchterms = $q['q'];
		}
	else if (preg_match("/yahoo\./i", $url['host'])) {
		parse_str($url['query'],$q);
		// Yahoo search terms are in "p"
		$searchterms = $q['p'];
		}
	else if (preg_match("/search\.aol\./i", $url['host'])) {
		parse_str($url['query'],$q);
		// Yahoo search terms are in "query"
		$searchterms = $q['query'];
		}
	else if (preg_match("/search\.msn\./i", $url['host'])) {
		parse_str($url['query'],$q);
		// MSN search terms are in "q"
		$searchterms = $q['q'];
		}
	
	if (isset($searchterms) && !empty($searchterms)) {
		// Remove BINARY from the SELECT statement for a case-insensitive comparison
		$exists_query = "SELECT id FROM $SI_tables[searchterms] WHERE searchterms = BINARY '$searchterms'";
		$exists = mysql_query($exists_query);
		
		if (mysql_num_rows($exists)) {
			$e = mysql_fetch_array($exists);
			$query = "UPDATE $SI_tables[searchterms] SET count = (count+1) WHERE id = $e[id]";
			mysql_query($query);
			}
		else {
			$query = "INSERT INTO $SI_tables[searchterms] (searchterms,count) VALUES ('$searchterms',1)";
			mysql_query($query);
			}
		}
	}


/******************************************************************************
 SI_parseUserAgent()
 Attempts to suss out the browser info from its user agent string.
 It is possible to spoof a string though so don't blame me if something doesn't
 seem right. This will need updating as newer browsers are released.
 ******************************************************************************/
function SI_parseUserAgent($ua) {
	$browser['platform']	= "Indeterminable";
	$browser['browser']	= "Indeterminable";
	$browser['version']	= "Indeterminable";
	$browser['majorver']	= "Indeterminable";
	$browser['minorver']	= "Indeterminable";
	
	
	// Test for platform
	if (preg_match('/Win/i',$ua)) {
		$browser['platform'] = "Windows";
		}
	else if (preg_match('/Mac/i',$ua)) {
		$browser['platform'] = "Macintosh";
		}
	else if (preg_match('/Linux/i',$ua)) {
		$browser['platform'] = "Linux";
		}
	
	
	// Test for browser type
	if (preg_match('Mozilla/4',$ua) && !preg_match('/compatible/i',$ua)) {
		$browser['browser'] = "Netscape";
		preg_match('/Mozilla/([[:digit:]\.]+)/i',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Mozilla/5',$ua) || preg_match('Gecko',$ua)) {
		$browser['browser'] = "Mozilla";
		preg_match('rv(:| )([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[2];
		}
	if (preg_match('Safari',$ua)) {
		$browser['browser'] = "Safari";
		$browser['platform'] = "Macintosh";
		preg_match('Safari/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		
		if (preg_match('125',$browser['version'])) {
			$browser['version'] 	= 1.2;
			$browser['majorver']	= 1;
			$browser['minorver']	= 2;
			}
		else if (preg_match('100',$browser['version'])) {
			$browser['version'] 	= 1.1;
			$browser['majorver']	= 1;
			$browser['minorver']	= 1;
			}
		else if (preg_match('85',$browser['version'])) {
			$browser['version'] 	= 1.0;
			$browser['majorver']	= 1;
			$browser['minorver']	= 0;
			}
		else if ($browser['version']<85) {
			$browser['version'] 	= "Pre-1.0 Beta";
			}
		}
	if (preg_match('iCab',$ua)) {
		$browser['browser'] = "iCab";
		preg_match('iCab/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Firefox',$ua)) {
		$browser['browser'] = "Firefox";
		preg_match('Firefox/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Firebird',$ua)) {
		$browser['browser'] = "Firebird";
		preg_match('Firebird/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Phoenix',$ua)) {
		$browser['browser'] = "Phoenix";
		preg_match('Phoenix/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Camino',$ua)) {
		$browser['browser'] = "Camino";
		preg_match('Camino/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Chimera',$ua)) {
		$browser['browser'] = "Chimera";
		preg_match('Chimera/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Netscape',$ua)) {
		$browser['browser'] = "Netscape";
		preg_match('Netscape[0-9]?/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('MSIE',$ua)) {
		$browser['browser'] = "Internet Explorer";
		preg_match('MSIE ([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Opera',$ua)) {
		$browser['browser'] = "Opera";
		preg_match('Opera( |/)([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[2];
		}
	if (preg_match('OmniWeb',$ua)) {
		$browser['browser'] = "OmniWeb";
		preg_match('OmniWeb/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Konqueror',$ua)) {
		$browser['platform'] = "Linux";

		$browser['browser'] = "Konqueror";
		preg_match('Konqueror/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Crawl',$ua) || preg_match('bot',$ua) || preg_match('slurp',$ua) || preg_match('spider',$ua)) {
		$browser['browser'] = "Crawler/Search Engine";
		}
	if (preg_match('Lynx',$ua)) {
		$browser['browser'] = "Lynx";
		preg_match('Lynx/([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	if (preg_match('Links',$ua)) {
		$browser['browser'] = "Links";
		preg_match('\(([[:digit:]\.]+)',$ua,$b);
		$browser['version'] = $b[1];
		}
	
	
	// Determine browser versions
	if ($browser['browser']!='Safari' && $browser['browser'] != "Indeterminable" && $browser['browser'] != "Crawler/Search Engine" && $browser['version'] != "Indeterminable") {
		// Make sure we have at least .0 for a minor version
		$browser['version'] = (!preg_match('\.',$browser['version']))?$browser['version'].".0":$browser['version'];
		
		preg_match('^([0-9]*).(.*)$',$browser['version'],$v);
		$browser['majorver'] = $v[1];
		$browser['minorver'] = $v[2];
		}
	if (empty($browser['version']) || $browser['version']=='.0') {
		$browser['version']		= "Indeterminable";
		$browser['majorver']		= "Indeterminable";
		$browser['minorver']		= "Indeterminable";
		}
	
	return $browser;
	}

function SI_getKeywords($func_obj) {
	global $SI_tables;
	$query = "SELECT searchterms, count
			  FROM $SI_tables[searchterms]
			  ORDER BY count DESC
			  LIMIT 0,36";
	
	if ($result = mysqli_query($func_obj,$query)) {
		$ul  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$ul .= "\t<tr><th>Search Strings</th><th class=\"last\">Hits</th></tr>\n";
		while ($r = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$ul .= "\t<tr><td>$r[searchterms]</td><td class=\"last\">$r[count]</td></tr>\n";
			}
		$ul .= "</table>";
		mysqli_free_result($result);
		}
	return $ul;
	}


/******************************************************************************
 SI_getReferers()
 Updated 04.06.19 for Andrei Herasimchuk <designbyfire.com>
 Added requested resource as a tooltip
 ******************************************************************************/
function SI_getReferers($func_obj) {
	global $SI_tables,$SI_display,$tz_offset,$_SERVER;
	
	$query = "SELECT referer, resource, dt 
			  FROM $SI_tables[stats]
			  WHERE referer NOT LIKE '%".SI_trimReferer($_SERVER['SERVER_NAME'])."%' AND 
					referer!='' 
			  ORDER BY dt DESC 
			  LIMIT 0,36";
			  
	if ($result = mysqli_query($func_obj,$query)) {
		$ul  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$ul .= "\t<tr><th>Recent Referrers</th><th class=\"last\">When</th></tr>\n";
		while ($r = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$url = parse_url($r['referer']);
			
			$when = ($r['dt'] >= strtotime(date("j F Y",time())))?gmdate("g:i a",$r['dt']+(((gmdate('I'))?($tz_offset+1):$tz_offset)*3600)):gmdate("M j",$r['dt']+(((gmdate('I'))?($tz_offset+1):$tz_offset)*3600));
			
			$resource = ($r['resource']=="/")?$SI_display["siteshort"]:$r['resource'];
			$ul .= "\t<tr><td><a href=\"$r[referer]\" title=\"$resource\" rel=\"nofollow\">".SI_trimReferer($url['host'])."</a></td><td class=\"last\">$when</td></tr>\n";
			}
		$ul .= "</table>";
		mysqli_free_result($result);
		}
	return $ul;
	}



/******************************************************************************
 SI_getDomains()
 Updated 04.06.19 for Andrei Herasimchuk <designbyfire.com>
 Added requested resource as a tooltip
 ******************************************************************************/
function SI_getDomains($func_obj) {
	global $SI_tables,$SI_display,$_SERVER;
	
	$query = "SELECT domain, referer, resource, COUNT(domain) AS 'total' 
			  FROM $SI_tables[stats]
			  WHERE domain !='".SI_trimReferer($_SERVER['SERVER_NAME'])."' AND 
					domain!='' 
			  GROUP BY domain 
			  ORDER BY total DESC, dt DESC";
	
	if ($result = mysqli_query($func_obj,$query)) {
		$ul  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$ul .= "\t<tr><th>Repeat Referrers</th><th class=\"last\">Hits</th></tr>\n";
		$i=0;
		while ($r = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			if ($i < 36) {
				$resource = ($r['resource']=="/")?$SI_display["siteshort"]:$r['resource'];
				$ul .= "\t<tr><td><a href=\"$r[referer]\" title=\"$resource\" rel=\"nofollow\">$r[domain]</a></td><td class=\"last\">$r[total]</td></tr>\n";
				$i++;
				}
			}
		$ul .= "</table>";
		mysqli_free_result($result);
		}
	return $ul;
	}


function SI_getCountries() {
	global $SI_tables,$_SERVER;
	
	$query = "SELECT country, COUNT(country) AS 'total' 
			  FROM $SI_tables[stats]
			  WHERE country!='' 
			  GROUP BY country 
			  ORDER BY total DESC";
	
	if ($result = mysql_query($query)) {
		$ul  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$ul .= "\t<tr><th>Country</th><th class=\"last\">Visits</th></tr>\n";
		$i=0;
		while ($r = mysql_fetch_array($result)) {
			if ($i < 36) {
				$url = parse_url($r['referer']);
				$ul .= "\t<tr><td>$r[country]</td><td class=\"last\">$r[total]</td></tr>\n";
				$i++;
				}
			}
		$ul .= "</table>";
		}
	return $ul;
	}


/******************************************************************************
 SI_getResources()
 Updated 04.06.19 for Andrei Herasimchuk <designbyfire.com>
 Added requesting referrer as a tooltip
 ******************************************************************************/
function SI_getResources($func_obj) {
	global $SI_tables, $SI_display;
	
	$query = "SELECT resource, referer, COUNT(resource) AS 'requests' 
			  FROM $SI_tables[stats]
			  WHERE 
			  resource NOT LIKE '%/inc/%' 
			  GROUP BY resource
			  ORDER BY requests DESC 
			  LIMIT 0,36";
	
	if ($result = mysqli_query($func_obj,$query)) {
		$ul  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$ul .= "\t<tr><th>Resource</th><th class=\"last\">Requests</th></tr>\n";
		while ($r = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$resource = ($r['resource']=="/")?$SI_display["siteshort"]:SI_truncate($r['resource'],24);
			$referer = (!empty($r['referer']))?$r['referer']:'No referrer';
			$ul .= "\t<tr><td><a href=\"http://".SI_trimReferer($_SERVER['SERVER_NAME'])."$r[resource]\" title=\"$referer\">".$resource."</a></td><td class=\"last\">$r[requests]</td></tr>\n";
			}
		mysqli_free_result($result);
		$ul .= "</table>";
		}
	return $ul;
	}


function SI_getPlatforms($func_obj) {
	global $SI_tables;
	$th = SI_getTotalHits($func_obj);
	$query = "SELECT platform, COUNT(platform) AS 'total' 
			  FROM $SI_tables[stats]
			  GROUP BY platform
			  ORDER BY total DESC";
	if ($result = mysqli_query($func_obj,$query)) {
		$ul  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$ul .= "\t<tr><th>Platform</th><th class=\"last\">%</th></tr>\n";
		while ($r = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			//var_dump($r);
			$ul .= "\t<tr><td>$r[platform]</td><td class=\"last\">".number_format(($r['total']/$th)*100)."%</td></tr>\n";
			}
		mysqli_free_result($result);
		$ul .= "</table>";
		}
	return $ul;
	}

/******************************************************************************
 SI_getBrowsers()
 Updated 04.06.19 for Andrei Herasimchuk <designbyfire.com>
 Removed distinguishing between browser version
 Will develop better approach for v4
 ******************************************************************************/
function SI_getBrowsers($func_obj) {
	global $SI_tables,$SI_display;
	$collapse = ($SI_display['collapse'])?'browser':'browser, version';
	$th = SI_getTotalHits($func_obj);
	$query = "SELECT browser, version, COUNT(*) AS 'total' 
			  FROM $SI_tables[stats]
			  WHERE browser != 'Indeterminable' 
			  GROUP BY $collapse
			  ORDER BY total DESC";
	if ($result = mysqli_query($func_obj,$query)) {
		$ul  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$ul .= "\t<tr><th>Browser</th><th>Version</th><th class=\"last\">%</th></tr>\n";
		while ($r = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$p = number_format(($r['total']/$th)*100);
			// $p = ($p==0)?"&lt;1":$p;
			if ($p>=1) {
				$ul .= "\t<tr><td>$r[browser]</td><td>$r[version]</td><td class=\"last\">$p%</td></tr>\n";
				}
			}
		$ul .= "</table>";
		}
	return $ul;
	}

function SI_getTotalHits($func_obj) {
	global $SI_tables;
	$query = "SELECT COUNT(*) AS 'total' FROM $SI_tables[stats]";
	if ($result = mysqli_query($func_obj,$query)) {
		if ($count = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			mysqli_free_result($result);
			return $count['total'];
			}
		}
	}
function SI_getFirstHit($func_obj) {
	global $SI_tables;
	$query = "SELECT dt FROM $SI_tables[stats] ORDER BY dt ASC LIMIT 0,1";
	if ($result = mysqli_query($func_obj,$query)) {
		if ($r = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			mysqli_free_result($result);
			return $r['dt'];
			}
		}
	}
function SI_getUniqueHits($func_obj) {
	global $SI_tables;
	$query = "SELECT COUNT(DISTINCT remote_ip) AS 'total' FROM $SI_tables[stats]";
	if ($result = mysqli_query($func_obj,$query)) {
		if ($count = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			mysqli_free_result($result);
			return $count['total'];
			}
		}
	}
function SI_getTodaysHits($func_obj) {
	global $SI_tables,$tz_offset;
	$dt = strtotime(gmdate("j F Y",time()+(((gmdate('I'))?($tz_offset+1):$tz_offset)*3600)));
	$dt = $dt-(3600*2); // The above is off by two hours. Don't know why yet...
	$query = "SELECT COUNT(*) AS 'total' FROM $SI_tables[stats] WHERE dt >= $dt";
	if ($result = mysqli_query($func_obj,$query)) {
		if ($count = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			mysqli_free_result($result);
			return $count['total'];
			}
		}
	}
	
function SI_getTodaysUniqueHits($func_obj) {
	global $SI_tables,$tz_offset;
	$dt = strtotime(gmdate("j F Y",time()+(((gmdate('I'))?($tz_offset+1):$tz_offset)*3600)));
	$dt = $dt-(3600*2); // The above is off by two hours. Don't know why yet...
	$query = "SELECT COUNT(DISTINCT remote_ip) AS 'total' FROM $SI_tables[stats] WHERE dt >= $dt";
	if ($result = mysqli_query($func_obj,$query)) {
		if ($count = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			mysqli_free_result($result);
			return $count['total'];
			}
		}
	}

/******************************************************************************
 SI_getWeeksHits()
 Created 04.04.24 v0.4b
 Integrated 04.06.19  v0.31b for Andrei Herasimchuk
 ******************************************************************************/
function SI_getWeeksHits($func_obj) {
	global $SI_tables,$tz_offset;
	
	$dt = strtotime(gmdate("j F Y",time()+(((gmdate('I'))?($tz_offset+1):$tz_offset)*3600)));
	$dt = $dt-(3600*2); // The above is off by two hours. Don't know why yet...
	
	$tmp = "";
	$dt_start = time();
	
	$tmp  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
	$tmp .= "\t<tr><th colspan=\"2\">Hits in the last week</th></tr>\n";
	$tmp .= "\t<tr><td class=\"accent\">Day</td><td class=\"accent last\">Hits</td></tr>\n";
	
	for ($i=0; $i<7; $i++) {
		$dt_stop = $dt_start;
		$dt_start = $dt - ($i * 60 * 60 * 24);
		$day = ($i)?gmdate("l, j M Y",$dt_stop):"Today, ".gmdate("j M Y",$dt_stop);
		$query = "SELECT COUNT(*) AS 'total' FROM $SI_tables[stats] WHERE dt > $dt_start AND dt <=$dt_stop";
		if ($result = mysqli_query($func_obj,$query)) {
			if ($count = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				mysqli_free_result($result);
				$tmp .= "\t<tr><td>$day</td><td class=\"last\">$count[total]</td></tr>\n";
				}
			}
		}
	$tmp .= "</table>";
	return $tmp;
	}

/******************************************************************************
 SI_determineLanguage()
 Added 04.06.27
 Based on code submitted by Gerhard Schoder <buero-schoder.de>
 ******************************************************************************/
function SI_determineLanguage() {
	global $_SERVER;
	if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
		// Capture up to the first delimiter (, found in Safari)
		preg_match("/([^,;]*)/",$_SERVER["HTTP_ACCEPT_LANGUAGE"],$langs);
		$lang_choice=$langs[0];
		}
	else { $lang_choice="empty"; }
	return $lang_choice;
	}
/******************************************************************************
 SI_getLanguage()
 Added 04.06.27
 Based on code submitted by Gerhard Schoder <buero-schoder.de>
 ******************************************************************************/
function SI_getLanguage($func_obj) {
	include_once("languages.php");
	global $SI_tables;
	
	$query = "SELECT COUNT(*) AS 'total' FROM $SI_tables[stats] WHERE language != '' AND language != 'empty'";
	if ($result = mysqli_query($func_obj, $query)) {
		if ($count = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$th = $count['total'];
			}
		}
	$query = "SELECT language, COUNT(language) AS 'total' 
			  FROM $SI_tables[stats] 
			  WHERE language != '' AND 
			  language != 'empty' 
			  GROUP BY language
			  ORDER BY total DESC";
	if ($result = mysqli_query($func_obj, $query)) {
		$html  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$html .= "\t<tr><th>Language</th><th class=\"last\">%</th></tr>\n";
		while ($r = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$l = $r['language'];
			$lang = (isset($SI_languages[$l]))?$SI_languages[$l]:$l;
			$per = number_format(($r['total']/$th)*100);
			$per = ($per)?$per:'<1';
			$html .= "\t<tr><td>$lang</td><td class=\"last\">$per%</td></tr>\n";
			}
		$html .= "</table>";
		mysqli_free_result($result);
		}
	return $html;
	}

function SI_truncate($var, $len = 120) {
	if (empty ($var)) { return ""; }
	if (strlen ($var) < $len) { return $var; }
	if (preg_match ("/(.{1,$len})\s./ms", $var, $match)) { return $match [1] . "..."; }
	else { return substr ($var, 0, $len) . "..."; }
	}
function SI_trimReferer($r) {
	$r = preg_replace("~https?://~","",$r);
	$r = preg_replace("^www.^","",$r);
	$r = SI_truncate($r,36);
	
	return $r;
	}
?>
