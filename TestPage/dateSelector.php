<?php 

function date_selector($name) {
	make_day_selector($name . "_day");
	make_month_selector($name . "_month");
	make_year_selector($name . "_year");	
}
			
function make_year_selector($name) {
	$year = intval(date("Y"));
	echo "<select class=\"input-small\" name=\"$name\">";
	echo "<option value=0000>Year</option>";
	for( $i = $year - 5; $i <= $year + 5; $i++ ) {
		echo "<option value=" . $i . ">" . $i . "</option>";
	}
	echo "</select>";
}
			
function make_month_selector($name) {
$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	echo "<select class=\"input-medium\" name=\"$name\">";
	echo "<option value=00>Month</option>";
	for( $i = 0; $i < 12; $i++ ) {
		echo "<option value=" . ($i+1) . ">" . $months[$i] . "</option>";
	}
	echo "</select>";
}
			
function make_day_selector($name) {
	echo "<select class=\"input-small\" name=\"$name\">";
	echo "<option value=0>Day</option>";
	for( $i = 1; $i <= 31; $i++ ) {
		echo "<option value=" . $i . ">" . $i . "</option>";
	}
	echo "</select>";
}
