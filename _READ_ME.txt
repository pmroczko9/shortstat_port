*********************************************************************************
ShortStat : Short but Sweet

v0.36b

Created: 	04.03.04
Updated:	05.05.30
 
By:			Shaun Inman
 			http://www.shauninman.com/


*********************************************************************************
DISCLAIMER
.................................................................................

This software is provided strictly on an as is basis. You are soley responsible
and liable for any damage you do to your website or server. If you are unsure 
how to install or operate any server side software pleace contact your server 
administrator.

This project is released under the GNU GPL (General Public License) 
<http://www.gnu.org/copyleft/gpl.html> unless otherwise specfied. You may use, 
redistribute, and modify any code to your liking. If you put up for download 
any of this software or flavor of this software please give reference to 
http://www.shauninman.com/


CONTENTS

1.	Installation Instructions
2.	IP-to-Country Plug-in 	 
	Installation Instructions	 
3.	Clean-up
4. _killprevious.php Instructions 


*********************************************************************************
INSTALLATION INSTRUCTIONS (UPGRADE INSTRUCTIONS BELOW)
.................................................................................

Step 1. Open the file "configuration.php" included in this package and change 
		$SI_db['server'], $SI_db['username'], $SI_db['password'] and 
		$SI_db['database'] to their appropriate values. 
		
		Only change $SI_tables['stats'], $SI_tables['searchterms'] and 
		$SI_tables['countries'] if their current value causes a conflict with an 
		existing table. This really shouldn't be an issue. If it is the installer 
		will inform you of the conflict and not proceed. 
		
		Set your local timezone offset. 
		
		Set $shortstat to true.
		
		Save and close the file.


.................................................................................

Step 2. Upload the entire "/shortstat/" directory to your server. The remainder of
		these instructions assume that you have uploaded this to root.


.................................................................................

Step 3. Load http://yourdomainhere.com/shortstat/_install.php in the web browser of 
		choice.


.................................................................................

Step 4. Upon successful feedback from Step 3 rename or remove 
		"/shortstat/_install.php" from your server.


.................................................................................

Step 5. Load http://yourdomainhere.com/shortstat/_trackme.php 
		This page contains the following code which should be added to any page 
		that you would like to track the activity of or to an include used by 
		all pages:
		
		<?php @include_once($_SERVER["DOCUMENT_ROOT"]."/shortstat/inc.stats.php"); ?>


.................................................................................

Step 6. Now visit http://yourdomainhere.com/shortstat/. You should see 
		/shortstat/_trackme.php in the Resources table as well as your Platform 
		and Browser information. Once you've added the code from Step 5 to the 
		desired pages you're done!


*********************************************************************************
IP-TO-COUNTRY PLUG-IN INSTALLATION INSTRUCTIONS

This plug-in uses the IP-to-Country Database provided by WebHosting.Info
http://ip-to-country.webhosting.info/
.................................................................................
Step 1.	Make sure _ip-to-country.txt has been completely uploaded to your server,
		it is a 2.1MB CSV file so depending on you connection speed it may take a
		while.
		
.................................................................................

Step 2. Load http://yourdomainhere.com/shortstat/_ip-to-country.php in the web 
		browser of choice. Follow the prompts.


*********************************************************************************
CLEAN-UP
.................................................................................
The following files are required to run ShortStat. Once installed any file 
beginning with an underscore (or left over from a previous install) should be 
removed from the /shortstat/ directory.

configuration.php
functions.php
inc.stats.php
index.php
styles.css


*********************************************************************************
_KILLPREVIOUS.PHP INSTRUCTIONS

WARNING: This page has the ability to wipe out all your ShortStat data from
previous months. This action is not undoable. It will not touch any data from the
current month. It does not affect Search terms which are stored in a separate 
table. You may want to keep a local copy and only upload it to the server as
needed if you are worried about abuse of this unprotect page.
.................................................................................

(Optional) Save a rendered version of your current ShortStats as HTML for posterity.

.................................................................................

Step 1. Load http://yourdomainhere.com/shortstat/_killprevious.php in the web 
		browser of choice. You will be asked for confirmation before deleting the
		old data (just in case your browser gets a little overzealous with auto-
		complete).


*********************************************************************************
See http://shortstat.shauninman.com/ for an example of a worn-in installation of 
the ShortStat package.