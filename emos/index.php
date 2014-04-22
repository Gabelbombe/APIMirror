<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>BEN EMOS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Test queries against the EMOS database">
    <meta name="author" content="Reid Mayfield">
	
	<!--StyleSheets-->
	<link href="css/common.css" rel="stylesheet" media="screen">
	<link href="css/ben.bootstrap.css" rel="stylesheet" media="screen">

	<!--Scripts for loading gif-->
	<script>
	function displayLoading() {
		if (document.getElementById) { // DOM3 = IE5, NS6 
			document.getElementById('loading').style.display = 'block'; 
		} else { 
			if (document.layers) { // Netscape 4 
				document.loading.display = 'block'; 
			} else { // IE 4 
				document.all.loading.style.display = 'block'; 
			} 
		} 
		if (document.getElementById) { // DOM3 = IE5, NS6 
			document.getElementById('done_loading').style.display = 'none'; 
		} else { 
			if (document.layers) { // Netscape 4 
				document.done_loading.display = 'none'; 
			} else { // IE 4 
				document.all.done_loading.style.display = 'none'; 
			} 
		} 
	}
	</script>
	
	<!--Icon-->
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
  </head>

  <body>
	<?php include("navbar.php"); ?>
	<div id="container">
		<h1 class="uppercase">EMOS</h1> <br /><br />
	<?php

			/******** Nav Bar *************/
			if(!array_key_exists("layout", $_GET)) {
					//defaults to accounts in drop down menu
					$select = "accounts";
				} else {
					$select = $_GET["layout"];
			}
			echo "<a href=\"&#63layout=accounts\">Accounts</a> :: ";
			echo "<a href=\"&#63layout=brands\">Brands</a> :: ";
			echo "<a href=\"&#63layout=categories\">Categories</a> :: ";
			echo "<a href=\"&#63layout=clients\">Clients</a> :: ";
			echo "<a href=\"&#63layout=genres\">Genres</a> :: ";
			echo "<a href=\"&#63layout=opportunities\">Opportunities</a> :: ";
			echo "<a href=\"&#63layout=placements\">Placements</a> :: ";
			echo "<a href=\"&#63layout=vehicles\">Vehicles</a>";
			?>
			<?php if ($select !== 'vehicles') {
			echo "<p class=\"lead\">
				Get All
				<br>
			</p>

			<form>
				<input type='hidden' name='getall' />
				<input type='hidden' name='layout' value=$select>
				<input type='submit' class='btn' value=\"Search\" onclick=\"displayLoading()\">
			</form>
			<hr>";
			} ?>
			<p class="lead">
				Get By ID
				<br>
			</p>
			<form>
				<div class="input-append">
					<input type='hidden' name='layout' value=<?=$select?> />
					<input class="input-small" name="id" type="text" placeholder="ID #" >
					<button class="btn" onclick="displayLoading()" type="submit" >Search</button>
				</div>
		</form>
		<hr>
		
		<!-- GetByDates -->
		<?php
		if($select !== "genres" && $select !== "categories") {
			echo "<p class=\"lead\">
				Get By Date Range
				<br>
			</p>
			Start Date:
			<form name=\"date_selector\" action=\"\">";
			loadFile("dateSelector.php");
			date_selector("start");
			echo "<br>End Date:<br>";
			date_selector("end");
			echo "<br>";
			echo "<input type='hidden' name='layout' value=$select />
				<input type=\"submit\" class=\"btn\" value=\"Search\" onclick=\"displayLoading()\">
				</form><hr>";
		}
		?>
		
		
		<div id="loading" style="display:none;">
			<p class="lead"> 
				Results 
			</p>
			<img src="img/loader.gif" alt="Loading..." title="Loading...">
		</div>
		<div id="done_loading">
			<?php
		

set_time_limit(600);	
/*****************************
*
*
* Workhorse
* 
*
*****************************/


//Select Environment

//PROD	
//$emosURL = "http://emos.productplacement.com/benphp/index.php?layout=$select";   

//DEV
// $emosURL = "http://dev.emos.productplacement.corbis.pre/?layout=$select";

//TEMPORARY
$emosURL = "http://localhost/index.php/?layout=$select";



/*
 *  Param Setup for use in constructing URI to access EAPI
 */ 
 $useLocalFile = array_key_exists("localfile", $_GET);
 $debug = array_key_exists("debug", $_GET);
 $param_list = array("start_year", "start_month", "start_day", "end_year", "end_month", "end_day");
 
if(array_key_exists("id", $_GET)) {
	// check for invalid id param
	if($_GET["id"] == NULL) {
		echo"<hr><p class=\"lead\"> Results </p>";
		println("Please specify a valid ID number");
		die();
	}
	// If specified, put the single id onto the emosURL
	if($_GET["id"] != NULL) {
		$id = $_GET["id"];
		$emosURL .= "&id=" . strtoupper($id);
	}
} else if (array_key_exists($param_list[0], $_GET)) {
	//check param_list to ensure all keys are present
	$emosURL .= "&start_date=";
	foreach($param_list as $param) {
		//check for array key's existence
		if(!array_key_exists($param, $_GET)) {
			die();
		}
		//build URL using $_GET parameters
		if($_GET[$param] == 0) {
			echo"<p class=\"lead\"> Results </p>";
			println("Please specify a valid start date");
			die();
		}
		$emosURL .= $_GET[$param];
		if($param == "start_day") {
			if($_GET["end_year"] != 0 && $_GET["end_month"] != 0 && $_GET["end_day"] != 0) {
				$emosURL .= "&end_date=";
			} else {
				break;
			}
		} else if($param != "end_day") {
			$emosURL .= "-";	
		}
	}
} else if (!(array_key_exists("getall", $_GET) || $useLocalFile)) {
	die();
} else {
	//do nothing! $emosURL is ready if using getall and not needed if using local file
}

echo"<p class=\"lead\"> Results </p>";


/*
 * Set debug options
 */
if( $debug ) {
	println("<_________________DEBUGGER__________________>");
	println("--> Constructed emosURL: " . $emosURL);
} else {
	$debug = false;
}

loadFile("timer.php");

// Get Json
if( $useLocalFile ) {
	$file = fopen("debug_emos_response", 'r');
	$emosResponse = fread($file, filesize("debug_emos_response"));
	fclose($file);
} else {
	//use timer object to get queryTime
	$timer = new Timer();
	$timer->start();
	$ctx = stream_context_create(
		array(
			'http' => array(
				'timeout' => 600
			)
		)
	);
	$emosResponse = @file_get_contents($emosURL, 0, $ctx);
	// var_dump($emosResponse);
	$queryTime = round($timer->stop(),2);
}


if($debug) {
	println("--> Query time: " . round($timer->stop(), 2) . " sec");
}

// Check for valid Json response
if($emosResponse === FALSE) {
	println("Error: failed to open stream: EMOS is mostly likely down :(");
	die();
} else {
	$emosJson = json_decode($emosResponse, true);
	// echo var_dump($emosJson);
	
	//now check if emos returned any error messages
	if(isset($emosJson['error'])) {
		// There was an error, most likely an invalid ID number was entered
		echo "error " . $emosJson['code'];
		echo ': ';
		println($emosJson['error']);
		echo "Query Time: $queryTime";
	} else if (empty($emosJson)) {
		// Tis empty
		echo "No results";
	} else if (array_keys($emosJson)[0] != "0" && !($select === "genres" && array_key_exists("getall", $_GET))) {
		// EMOS returned a single result obj
		$id = $_GET['id'];
		
		//assert not null
		if($emosJson == NULL) {
			echo "$select:$id returned NULL";
			exit();
		}
		echo "Found <b>1</b> Result in $queryTime seconds<br> ";	
        echo newResultBlock($emosJson, 0);
		
	} else {
		//EMOS returned a list of result objs
		//obvi we are going to have to loop through the results and eventually make a resultBlock out of each result obj
		echo "<p>Found ";
		$i = 0;
        $result_list =  "<dl>";
		foreach($emosJson as $key => $result) {
			//select the right identifier based on layout (stored in $select)
			if ($select === "accounts") {
				$id = $result["AccountId"];
			} else if ($select === "placements") {
                $id = $result["PlacementId"];  
            } else if ($select === "vehicles") {
                $id = $result["ID"];
            } else if ($select === "brands") {
				$id = $result["BrandId"];
			} else if ($select === "categories") {
				$id = $result["CategoryId"];
			} else if ($select ==="genres") {
				$id = $result["GenreId"];
			} else if ($select === "clients") {
				$id = $result["ClientId"];
			} else if ($select === "opportunities") {
				$id = $result["OpportunityId"];
			}
			if($result == NULL) {
				println("$select:$id returned NULL");
				continue;
			} else {
				//toggle button
				$result_list .= "<dt><div class=\"result-row\" result-id=\"$id\"><i class=\"icon-chevron-right\" id=\"$i\"></i>     $id"; 

				//result block
				$result_list .= newResultBlock($result, $id, false);

				$result_list .= "</div></dt>";
				
				$i++;
			}
		}
        echo "</dl><b>$i</b> Results in $queryTime seconds</p>$result_list";
	}
}	

//Each object returned by the EAPI is turned into a unique instance of a resultBlock
//$resultObj is the json to be parsed
//$id is used by the jQuery script to couple it with the toggle button
//$visible is needed because in the case of finding only 1 result object, the object is initially visible and there is no toggle button
function newResultBlock($resultObj, $id, $visible=true) {
    $html = "<div class=\"result-block\" id=\"$id\"";
    if(!$visible) {
		$html .= " style=\"display:none;\"";
	} 
    $html .= ">";
    foreach($resultObj as $key => $value) {
           $html  .= "<div class=\"row\"><div class=\"span3\">$key:</div>";
           $html .= "<div class=\"span8\">" . handleJson($value) . "</div></div>";
    }
    return $html . "</div>";
}


//recursive function to handle content of a single result object
function handleJson($json) {
	$body = "";
	$dateTimeFormat = "/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/";
	// echo gettype($json); exit();
	if(!is_array($json)) {
		if(is_bool($json)) {
			if($json === false) {
				$body .= "No";
			} else { 
				$body .= "Yes";
			} 
		} else { //string
            //check if url, make a hyperlink if true
            if(substr($json, 0, 7) === "http://") {
                $body .= "<a href=\"$json\">$json</a>";
            // } else if(preg_match($dateTimeFormat, $json)) { //DateTime format
				// $date = strtok($json, "T");
				// $time = strtok("T");
				// return parseDateTime($date, $time);
			} else {   
                //otherwise it is just a native string
			    $body .= htmlspecialchars($json);
            }
		}
	} else if(!empty($json)) {
		if(array_keys($json)[0] == "0") {
			//list
			$first = true;
			foreach($json as $list_elem) {
				if($first) {
					$first = false;
				} else {
					$body .= " ";
				}
				$body .= handleJson($list_elem);
			}
		} else {
			//object
            $body .= "<div class=\"object-box\">";
			foreach($json as $key => $value) {
				if(($key == "Name" && $value == NULL) || ($key == "BrandId" && $value == NULL)) {
					return;
				}
                $body .= "$key: " . handleJson($value) . "<br>";                       
            }
            $body .=  "</div>";
		}
	}
	return $body;
}

//changes DateTime strings into human-friendly format
function parseDateTime($date, $time) {
	//parse date
	$dateray = array();
	$dateray[] = strtok($date, "-");
	for($i=0; $i<2; $i++) {
		$temp = strtok("-");
		$dateray[] = (substr($temp, 0, 1) == "0") ? substr($temp, 1, 1) : $temp; //eliminates leading zeros
	}
	$body .= "$dateray[1]/$dateray[2]/$dateray[0]";
	
	//parse time
	$timeray = array();
	$isDefaultTime = true;
	$timeray[] = strtok($time, ":-");
	for($i=0; $i<2; $i++) {
		$timeray[] = strtok(":-");
	}
	foreach($timeray as $timeCheck) {
		if($timeCheck != "00") {
			$isDefaultTime = false;
		}
	}
	$meridiem = "AM";
	if($isDefaultTime) {
		$time = strtok("-");
		$timeray[0] = (substr(($temp = strtok($time, ":")), 0, 1) == "0") ? substr($temp, 1, 1) : $temp;
		$timeray[1] = strtok(":");
		$timeray[2] = (($temp = strtok(":")) === FALSE) ? "00" : $temp;
		if(intval($timeray[0]) > 12) {
			$meridiem = "PM";
			$timeray[0] = intval($timeray[0]) - 12; //changes from military time to 12 hour clock
		}
		$body .= " $timeray[0]:$timeray[1]:$timeray[2] $meridiem";
	} else { 
		$timeray[0] = (substr($timeray[0], 0, 1) == "0") ? substring($timeray[0], 1, 1) : $timeray[0]; //eliminates leading zero
		if(intval($timeray[0]) > 12) {
			$meridiem = "PM";
			$timeray[0] = intval($timeray[0]) - 12; //changes from military time to 12 hour clock
		}
		$body .= " $timeray[0]:$timeray[1]:$timeray[2] $meridiem";
	}
	return $body;
}

function println($msg){
    echo "$msg<br />\n";
}	
		
//loads $filename, squawks if the file doesn't exist
function loadFile($filename) {
	if(file_exists("./$filename")) {
		include "./$filename";
	} else {
		println("Cannot load file: $filename");
		die();
	}
}
			?>	
		</div>
		
		<!--JavaScripts-->
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/displayTable.js"></script>	
		<script>
		function swap() {
			if (document.getElementById) { // DOM3 = IE5, NS6 
				document.getElementById('loading').style.display = 'none'; 
			} else { 
				if (document.layers) { // Netscape 4 
					document.loading.display = 'none'; 
				} else { // IE 4 
					document.all.loading.style.display = 'none'; 
				} 
			} 
			
			//Show the results div
			if (document.getElementById) { // DOM3 = IE5, NS6 
				document.getElementById('done_loading').style.display = 'block'; 
			} else { 
				if (document.layers) { // Netscape 4 
					document.done_loading.display = 'block'; 
				} else { // IE 4 
					document.all.done_loading.style.display = 'block'; 
				} 
			} 
		}		
		</script>	
	
