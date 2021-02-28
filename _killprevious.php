<?php
/******************************************************************************
 ShortStat : Short but Sweet
 Kill Previous
 v0.36b
 
 Created: 	04.06.19 for Andrei Herasimchuk <designbyfire.com>
 Updated:	04.06.27
 
 By:		Shaun Inman
 			http://www.shauninman.com/
 ******************************************************************************
 This page should be loaded only when you are prepared to wipe out ALL stored
 data from previous months. It is recommended that you save as HTML a recently
 generated ShortStat report for posterity.
 ******************************************************************************/
include_once("configuration.php");
include_once("functions.php");

if ($_POST["confirm"] == "kill") {
	SI_pconnect();
	$m = strtotime("1 ".gmdate("F Y",time()));
	$prev = $m+(((gmdate('I'))?($tz_offset+1):$tz_offset)*3600);
	mysql_query("DELETE FROM $SI_tables[stats] WHERE dt < $prev");
	echo mysql_affected_rows()." entries deleted";
	}
else { ?>
<form action="_killprevious.php" method="post" style="width: 360px;">
	<label>
		<input type="checkbox" id="confirm" name="confirm" value="kill" /> 
		Delete all ShortStat data from previous months.
	</label>
	<p>This is NOT undoable. It is recommended that you save as HTML a recently
 generated ShortStat report for posterity. You must confirm this decision above.</p>
	<input type="submit" value="Delete">
</form>
<?php } ?>

