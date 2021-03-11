<?php
/******************************************************************************
 ShortStat : Short but Sweet
 Installation
 v0.36b
 
 Created: 	04.03.04
 Updated:	04.11.16
 
 By:		Shaun Inman
 			http://www.shauninman.com/
 ******************************************************************************

 Please see the READ_ME.txt file for IP-to-Country plug-in installation instructions.

 ******************************************************************************/
include_once("configuration.php");
include_once("functions.php");

function inlineCountry($ip){
	$curlSession = curl_init();
    	curl_setopt($curlSession, CURLOPT_URL, 'http://www.geoplugin.net/json.gp?ip='.$ip);
    	
	curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

    	$jsonData = json_decode(curl_exec($curlSession));
    	curl_close($curlSession);

    	return $jsonData->geoplugin_countryName;

}

$main_obj = SI_pconnect();

/*if (!isset($match_existing)) {
	// First we see if this database has already been installed
	if (@mysql_query("SELECT * FROM $SI_tables[countries]")) { 
		echo "<p>The IP-to-Country table has already been created. This file and \"_ip-to-country.txt\" should be removed from your server 
			 UNLESS you have been running a previous or current version of ShortStat prior to installation of this plug-in and would like 
			 to map existing IPs to the appropriate country.</p>
			 <p>NOTE: This process took more than an hour to complete on an existing ShortStat database of 50k+ hits so it's best to open the link 
			 below in a browser that won't timeout on the script. If your browser does time out, don't fret. Just reload the page and the 
			 script should pick up where it left off.</p>
			 <p><a href=\"$PHP_SELF?match_existing=true\">Proceed.</a></p></p>";
		}
	// If not we do it to it...
	else {
		$query = "CREATE TABLE $SI_tables[countries] (
				  ip_from double NOT NULL default '0',
				  ip_to double NOT NULL default '0',
				  country_code2 char(2) NOT NULL default '',
				  country_code3 char(3) NOT NULL default '',
				  country_name varchar(50) NOT NULL default ''
				  ) TYPE=MyISAM;";
		if (@mysql_query($query)) {
			echo "<p>The IP-to-Country table has been created successfully! Importing country data...</p>";
			
			if($file = file('_ip-to-country.txt')) {
				for ($line=0; $line<count($file); $line++) {
					list($ipfrom,$ipto,$countrycode2,$countrycode3,$country) = explode(',',addslashes(eregi_replace('"','',trim($file[$line]))));
					$query = "INSERT INTO $SI_tables[countries]
							  (ip_from,ip_to,country_code2,country_code3,country_name) VALUES
							  ($ipfrom,$ipto,'$countrycode2','$countrycode3','$country')";
					
					mysql_query($query);
					}
				echo "<p>Country data load complete.</p>";
				
				
				echo "<p>IP-to-Country plug-in was installed successfully.</p>
					  <p>This file and \"_ip-to-country.txt\" should be removed from your 
					  server UNLESS you have been running a previous or current version of ShortStat prior to installation
					  of this plug-in and would like to map existing IPs to the appropriate country.</p>
					  <p>NOTE: This process took more than an hour to complete on an existing ShortStat database of 50k+ hits so it's best to open the
					  link below in a browser that won't timeout on the script. If your browser does time out, don't fret. Just reload the
					  page and the script should pick up where it left off.</p>
					  <p><a href=\"$PHP_SELF?match_existing=true\">Proceed.</a></p>";
				}
			else {
				echo "<p>Could not access _ip-to-country.txt. Please make sure that it has been uploaded into the /shortstat/ directory.</p>";
				}
			}
		else {
			echo "<p>The IP-to-Country table could not be created.</p>";
			}
		}
	}
else {
	if (@mysql_query("SELECT * FROM $SI_tables[countries]")) {*/
		echo "<p>Mapping existing IPs to countries.</p>";
		// Match existing ips to countries
		$query = "SELECT id,remote_ip FROM $SI_tables[stats] LIMIT 10000";
		if ($result = mysqli_query($main_obj,$query)) {
			while ($r = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$country = inlineCountry($r['remote_ip']);
				$query = "UPDATE $SI_tables[stats] SET country='$country' WHERE id=$r[id]";
				mysqli_query($main_obj,$query);
				}
			}
		echo "<p>Existing IPs mapped to appropriate country. This file and \"_ip-to-country.txt\" should be removed from your server.</p>";
	/*	}
	else {
		echo "<p>The IP-to-Country table does not exist. <a href=\"$PHP_SELF\">Install the IP-to-Country plug-in.</a></p>";
		}
	}*/
?>
