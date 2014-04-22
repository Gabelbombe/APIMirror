<?php

$extendedAsciiHTML = array(
128 => '€',
129 => '',
130 => '‚',
131 => 'ƒ',
132 => '„',
133 => '…',
134 => '†',
135 => '‡',
136 => 'ˆ',
137 => '‰',
138 => 'Š',
139 => '‹',
140 => 'Œ',
141 => '',
142 => 'Ž',
143 => '',
144 => '',
145 => '‘',
146 => '’',
147 => '“',
148 => '”',
149 => '•',
150 => '–',
151 => '—',
152 => '˜',
153 => '™',
154 => 'š',
155 => '›',
156 => 'œ',
157 => '',
158 => 'ž',
159 => 'Ÿ',
160 => ' ',
161 => '¡',
162 => '¢',
163 => '£',
164 => '¤',
165 => '¥',
166 => '¦',
167 => '§',
168 => '¨',
169 => '©',
170 => 'ª',
171 => '«',
172 => '¬',
173 => ' ',
174 => '®',
175 => '¯',
176 => '°',
177 => '±',
178 => '²',
179 => '³',
180 => '´',
181 => 'µ',
182 => '¶',
183 => '·',
184 => '¸',
185 => '¹',
186 => 'º',
187 => '»',
188 => '¼',
189 => '½',
190 => '¾',
191 => '¿',
192 => 'À',
193 => 'Á',
194 => 'Â',
195 => 'Ã',
196 => 'Ä',
197 => 'Å',
198 => 'Æ',
199 => 'Ç',
200 => 'È',
201 => 'É',
202 => 'Ê',
203 => 'Ë',
204 => 'Ì',
205 => 'Í',
206 => 'Î',
207 => 'Ï',
208 => 'Ð',
209 => 'Ñ',
210 => 'Ò',
211 => 'Ó',
212 => 'Ô',
213 => 'Õ',
214 => 'Ö',
215 => '×',
216 => 'Ø',
217 => 'Ù',
218 => 'Ú',
219 => 'Û',
220 => 'Ü',
221 => 'Ý',
222 => 'Þ',
223 => 'ß',
224 => 'à',
225 => 'á',
226 => 'â',
227 => 'ã',
228 => 'ä',
229 => 'å', 
230 => 'æ',
231 => 'ç',
232 => 'è',
233 => 'é',
234 => 'ê',
235 => 'ë',
236 => 'ì',
237 => 'í',
238 => 'î',
239 => 'ï',
240 => 'ð',
241 => 'ñ',
242 => 'ò',
243 => 'ó',
244 => 'ô',
245 => 'õ',
246 => 'ö',
247 => '÷',
248 => 'ø',
249 => 'ù',
250 => 'ú',
251 => 'û',
252 => 'ü',
253 => 'ý',
254 => 'þ',
255 => 'ÿ'
);

// 15 min...... it happens
$ebits = ini_get('error_reporting');
error_reporting($ebits ^ (E_NOTICE | E_WARNING));
set_time_limit(1200);

$config = array(
    // this is the connection string handed into odbc_connect()
    "connection" => "fmodbc",
		//////9/20/13 - ODBC change:
		//////Depreciated - does not support text fields over 255 characters:
		///////------>//////"Driver={Filemaker ODBC};Server=localhost;Database=NMA2007_DB;",
		////////////////////////////////////////
		////////////////////////////////////////
		////////////The "connection" key now points to a System DSN for the Filemaker ODBC driver,
		////////////where configuration for the driver now allows text fields over 255 characters
		////////////to display correctly.
	// "connection" => "Driver={Filemaker ODBC};Server=dev.emos.productplacement.corbis.pre;Database=NMA2007_DB;",
	// "connection" => "Driver={Filemaker ODBC};Server=192.168.50.8;Database=NMA2007_DB;",

    "layout_keys" => array(
        "brands" => "BrandModel",
        "clients" => "ClientModel",
        "accounts" => "AccountModel",
        "opportunities" => "OpportunityModel",
        "categories" => "CategoryModel",
        "genres" => "GenreModel",
        "placements" => "PlacementModel",
		"vehicles" => "VehicleModel",
    ),
);

define("DEBUG", array_key_exists("debug", $_GET));
define("utf8_encode_all", array_key_exists("utf8", $_GET));


// we need to check for a layout
if (!array_key_exists("layout", $_GET))
{
    build_error_and_die("layout must be specified", "-1");
}

// now we need to check that the layout is valid
$layout = $_GET["layout"];
if (!array_key_exists($layout, $config["layout_keys"]))
{
    build_error_and_die("invalid layout: $layout", "-2");
}

try {
	// put together the model
	$modelname = $config["layout_keys"][$layout];
	// load the matching EAPI class file for $modelname
	// class dependencies require including all the class files
	loadfile("EAPI_CLASSES/$modelname.php");
	$model = new $modelname();
	if (DEBUG)
	{
		println("model name: $modelname");
	}

	// now, we need to find the authentication stuff
	$user = null;
	$pass = null;
	if (file_exists("benconfig.php"))
	{
		include 'benconfig.php';
		$user = $auth["user"];
		$pass = $auth["pass"];
	}
	else if (file_exists("../../phpconfig/benconfig.php"))
	{
		include "../../phpconfig/benconfig.php";
		$user = $auth["user"];
		$pass = $auth["pass"];
	}
	if (DEBUG) { $connection_start = microtime(true); }
	$connection = odbc_connect($config["connection"],
							   $user,
							   $pass);
	
	if (!$connection)
	{
		$odbc_msg = odbc_errormsg($connection);
		build_error_and_die("unable to connect to db [odbc msg: $odbc_msg]", "-3");
	}
	if (DEBUG) { println("odbc connection time: ".(microtime(true) - $connection_start)); }


	// if the param key 'id' is specified, then we will
	// return a single document of the given layout with
	// the specified id, other than that, we return the
	// list of ids for that layout
	$result = null;

	if (DEBUG) { $query_start = microtime(true); }
	if(array_key_exists("id", $_GET))
	{
		$result = $model->get_by_id($_GET["id"]);
	}
	else if (array_key_exists("ids", $_GET)) 
	{
		$ids_split = explode(",", $_GET["ids"]);
		$result = $model->get_by_ids($ids_split);
	}
	else if (array_key_exists("start_date", $_GET))
	{
		$end_date = false;
		if (array_key_exists("end_date", $_GET))
		{
			$end_date = $_GET["end_date"];
		}
		$result = $model->get_by_dates($_GET["start_date"], $end_date);
	}
	else
	{
		$result = $model->get_all();
		
		if (DEBUG) { println("total ids found: ".count($result)); }
	}
	if (DEBUG) { println("total query time: ".(microtime(true) - $query_start)); }

	// ahh! lollipop! yay!!!
	//echo var_dump($result);
	//echo var_dump(htmlspecialchars ($result));
	// var_dump($result); exit();
	//echo "<br>\n";
	header("Content-type: application/json; charset=utf-8");
	//iconv(mb_detect_encoding($result,mb_detect_order(),true),"utf-8",$result);
	//$result=htmlentities($result,ENT_QUOTES);
	//print_r($result);

	clean_array($result);
	echo json_encode($result);

	exit(0);
}
catch(Exception $ex) {
	build_error_and_die($ex->getMessage(), "1");
}






// Helper Functions
function build_error_and_die($error, $code)
{
    global $connection;
    if ($connection)
    {
        odbc_close($connection);
    }
    $result = new stdClass();
    $result->code = $code;
    $result->error = $error;
	header("Content-type: application/json; charset=utf-8");
    die(json_encode($result));
}

function abstraction_get_ids($err_code, $sql)
{
    global $connection;

    if (DEBUG){ println("running sql: $sql"); }
    if (DEBUG){ $query_start = microtime(true); }
    $query_result = odbc_exec($connection, $sql);
    if($query_result === false)
    {
        $code = odbc_error($connection);
        $msg = odbc_errormsg($connection);
        build_error_and_die("unable to run queries, odbc error: ".
                      "[error code: $code] $msg", $err_code);
    }
    if (DEBUG){ println("query to run sql: ".(microtime(true) - $query_start)); }

    $id_collection = array();
    while(($result = odbc_fetch_object($query_result)) != false)
    {
        $id_collection[] = $result->id;
    }

    if (DEBUG)
    {
        println("ids found in query: ".count($id_collection));
    }
    return $id_collection;
}

function abstraction_query_single_array($err_code, $sql)
{
    global $connection;

    if (DEBUG){ println("running sql: $sql"); }
    if (DEBUG){ $query_start = microtime(true); }
    $query_result = odbc_exec($connection, $sql);
    if($query_result === false)
    {
        $code = odbc_error($connection);
        $msg = odbc_errormsg($connection);
        build_error_and_die("unable to run queries, odbc error: ".
                      "[error code: $code] $msg \n $sql", $err_code);
    }
    if (DEBUG){ println("query to run sql: ".(microtime(true) - $query_start)); }

	$result = odbc_fetch_array($query_result, 0);
	odbc_free_result($query_result);



	if ($result === false)
	{
		build_error_and_die("object not found", $err_code);
	}

	return $result;
}

function clean_array(&$result)
{
	$t = gettype($result);
	foreach($result as $key => $value)
	{
		$t2 = gettype($value);
		if($t2 == "string")
		{
			if($t == "array")
			{
				$result[$key] = clean_string($value);
			}
			else if($t == "object")
			{
				$result -> $key = clean_string($value);
			}
		}
		else if($t2 == "array" || $t2 == "object")
		{
			clean_array($value);
			if($t == "array")
			{
				$result[$key] = $value;
			}
			else if($t == "object")
			{
				$result -> $key = $value;
			}
		}
	}
}

function clean_string($result)
{
	global $extendedAsciiHTML;

	$cleaned = "";
	$chars = str_split($result);
	for($j=0; $j < count($chars); $j++)
	{
		$chr = $chars[$j];
		
		$o = ord($chr);
		if($o > 31 && $o < 127)
		{
			// accept printable ascii chars as-is
			$cleaned .= $chr;
		}
		else if($o > 127 && $o < 256)
		{
			// otherwise convert to an extended ascii character
			$cleaned .= $extendedAsciiHTML[$o];
		}
	}
	return $cleaned;
}

function abstraction_query_multi_array($err_code, $sql)
{
    global $connection;

    if (DEBUG){ println("running sql: $sql"); }
    if (DEBUG){ $query_start = microtime(true); }
    $query_result = odbc_exec($connection, $sql);
    if($query_result === false)
    {
        $code = odbc_error($connection);
        $msg = odbc_errormsg($connection);
        build_error_and_die("unable to run queries, odbc error: ".
                      "[error code: $code] $msg", $err_code);
    }
    if (DEBUG){ println("query to run sql: ".(microtime(true) - $query_start)); }

	if (DEBUG) { $counter = 0; }
	$result = array();
	while(($obj = odbc_fetch_array($query_result)) != false)
	{
		if (DEBUG) {
			$counter++;
			if ($counter % 100 == 0) {
				println("got $counter records");
			}
		}
		$result[] = $obj;
	}

	odbc_free_result($query_result);


	if ($result === false)
	{
		build_error_and_die("object not found [sql:$sql]", $err_code);
	}

	return $result;
}

function collide_object($extending, $odbc_array)
{
	foreach($odbc_array as $key => $value)
	{
		$extending->$key = $value;
	}
}

function shift_object_key($object, $dest, $key)
{
	$object->$dest->$key = $object->$key;
	unset($object->$key);
}

function println($msg)
{
    echo "$msg<br />\n";
}

function emos_split($string) {
  if (!$string) {
    return array();
  }
  $result = array();
  $arraycheck = preg_split("/[\r\n]/m", $string);
  foreach($arraycheck as $check)
  {
    $check = trim($check);
    if ($check)
    {
      $result[] = $check;
    }
  }
  return $result;
}

function utf8_encode_all($string){
	return iconv(mb_detect_encoding($string,mb_detect_order(),true),"utf-8",$string);
}

function build_talent($id, $name, $role, $q_score) {
  $result = new stdClass();
  $result->TalentId = $id;
  $result->Name = $name;
  $result->Role = $role;
  $result->QScore = (is_numeric($q_score) ? $q_score : null);
  return $result;
}

function loadFile($filename) {
	if(file_exists("./$filename")) {
		include "./$filename";
	} else {
		println("Cannot load file: $filename");
		die();
	}
}

// function convert_smart_quotes($string)
// {
    // $search = array(chr(145),
                    // chr(146),
                    // chr(147),
                    // chr(148),
					// '?',
					// '?',
                    // chr(151));

    // $replace = array("'",
                     // "'",
                     // '"',
                     // '"',
                     // '"',
                     // '"',
                     // '-');

    // return str_replace($search, $replace, $string);
// }


