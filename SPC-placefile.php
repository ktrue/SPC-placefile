<?php
error_reporting(E_ALL);
ini_set('display_errors','1');
/*
*	Name:			    SPC-placefile.php
*	Author(s):	  Mike Davis-NWS Nashville	, Ken True saratoga-weather.org
*	Description:	Reads NWS Storm Prediction Center outlooks
*               and displays a GR2 placefile describing affected polygons.
*
* Thanks to Mike Davis for his inspiration, initial coding, assistance and debugging of this script
* 
*/
# Version 1.00 - 10-Nov-2023 - initial release
# Version 1.01 - 11-Nov-2023 - improved mouse-over popup display
# Version 1.02 - 16-Nov-2023 - added Text: displays on SPC lines for better clairity
# Version 1.03 - 16-Nov-2023 - added mouse-over tooltip for legend words on lines
# Version 1.04 - 20-Nov-2023 - fixed mouse-over for MGNL display

$Version = "SPC-placefile.php - V1.04 - 20-Nov-2023 - saratoga-weather.org";
# -----------------------------------------------
#  Settings
$timeFormat = "d-M-Y g:ia T";           # display format for times
$cacheFilenameTemplate = 'SPC-%s.json'; # store json cache file
$cacheTimeMax  = 480;                   # number of seconds cache is 'fresh'
$doLogging = true;
$doDebug = false; # =true; turn on additional display, may break placefile for GRLevelX
$TZ = "America/Chicago";
$SPCcolors= false;  # =true, use JSON 'stroke' color; =false, use built-in colors

$latitude = 37.155;
$longitude = -121.898;
$version  = 1.5;
# -----------------------------------------------
//--self downloader --
if(isset($_REQUEST['sce']) and strtolower($_REQUEST['sce']) == 'view') {
   $filenameReal = __FILE__;
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain,charset=ISO-8859-1");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   
   readfile($filenameReal);
   exit;
 }

header('Content-type: text/plain;charset=UTF-8');

date_default_timezone_set($TZ);

$URLS = array(
  '1'    => 'Day 1|https://www.spc.noaa.gov/products/outlook/day1otlk_cat.lyr.geojson',
  '2'    => 'Day 2|https://www.spc.noaa.gov/products/outlook/day2otlk_cat.lyr.geojson',
  '3'    => 'Day 3|https://www.spc.noaa.gov/products/outlook/day3otlk_cat.lyr.geojson',
	'4'    => 'Day 4|https://www.spc.noaa.gov/products/exper/day4-8/day4prob.lyr.geojson',
	'5'    => 'Day 5|https://www.spc.noaa.gov/products/exper/day4-8/day5prob.lyr.geojson',
	'6'    => 'Day 6|https://www.spc.noaa.gov/products/exper/day4-8/day6prob.lyr.geojson',
	'7'    => 'Day 7|https://www.spc.noaa.gov/products/exper/day4-8/day7prob.lyr.geojson',
	'8'    => 'Day 8|https://www.spc.noaa.gov/products/exper/day4-8/day8prob.lyr.geojson',
  'test' => 'Test|https://www.spc.noaa.gov/products/outlook/archive/2023/day1otlk_20230331_1630_cat.lyr.geojson',
);

$LEGEND = array(
# "LEGEND" -> Longname,Color,Line 
 "TSTM" => "General Thunderstorm Risk|Color: 183 233 193|Line: 4, 0,",
 "MRGL" => "Marginal Risk|Color: 0 176 80|Line: 4, 0,",
 "SLGT" => "Slight Risk|Color: 255 255 0|Line: 5, 0,",
 "ENH"  => "Enhanced Risk|Color: 255 163 41|Line: 6, 0,",
 "MDT"  => "Moderate Risk|Color: 255 0 0|Line: 7, 0,",
 "HIGH" => "High Risk|Color: 255 0 255|Line: 8, 0,",
 "unk" => "Unknown|Color: 128 128 128|Line: 4, 0,",
);

global $URLS,$LEGEND,$SPCcolors;
if(isset($_GET['lat'])) {$latitude = $_GET['lat'];}
if(isset($_GET['lon'])) {$longitude = $_GET['lon'];}
if(isset($_GET['version'])) {$GRversion = $_GET['version'];}

if(isset($latitude) and !is_numeric($latitude)) {
	print "Bad latitude spec.";
	exit;
}
if(isset($latitude) and $latitude >= -90.0 and $latitude <= 90.0) {
	# OK latitude
} else {
	print "Latitude outside range -90.0 to +90.0\n";
	exit;
}

if(isset($longitude) and !is_numeric($longitude)) {
	print "Bad longitude spec.";
	exit;
}
if(isset($longitude) and $longitude >= -180.0 and $longitude <= 180.0) {
	# OK longitude
} else {
	print "Longitude outside range -180.0 to +180.0\n";
	exit;
}	
if(!isset($latitude) or !isset($longitude) or !isset($GRversion)) {
	print "This script only runs via a GRlevelX placefile manager.";
	exit();
}

if(isset($doLogging) and $doLogging) {
	$fn = "SPC-placefile-log-".gmdate('Y-m-d').'.txt';
	$log = gmdate('H:i:s'). " ";
	$log .= isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']." ":"x.x.x.x ";
	$log .= isset($_SERVER['SCRIPT_URI'])?$_SERVER['SCRIPT_URI']:'(no-URI)';
	$log .= isset($_SERVER['QUERY_STRING'])?"?".$_SERVER['QUERY_STRING']:"?(no-QUERY)";
	file_put_contents($fn,$log.PHP_EOL,FILE_APPEND);
}

if(isset($_GET['debug']) and $_GET['debug'] == 'y') {$doDebug = true;}

$Day = '1';

if(isset($useDay)) {$Day = $useDay;} # invoked from dayN.php scripts

if(isset($_GET['day'])) {$tDay = $_GET['day']; }
if(isset($tDay) and isset($URLS[$tDay])) {$Day = $tDay; }

if(isset($useSPCcolors)) {$SPCcolors = $useSPCcolors;} # override from dayN.php scripts

$out = JSONread($Day);

print $out;


# -----------------------------------------------------
# functions
function JSONread($Day) {
	global $cacheFilenameTemplate,$cacheTimeMax,$doDebug,$Version,$URLS,$LEGEND,$SPCcolors;
	
  $out = '';  #collector for GRLevelX placefile statements
	
	$cacheFilename = sprintf($cacheFilenameTemplate,$Day);
	list($DayLegend,$url) = explode('|',$URLS[$Day]);

	$today = date("D M j G:i:s T Y");
	// Create the header information with hard returns
	$headerInfo = array(
			"; $Version",
			"; running on PHP ".phpversion(),
			"Title: SPC $DayLegend Outlook - ".$today." - PlacefileNation",
			"Refresh: 5",
			"Color: 200 200 255",
			'Font: 1, 11, 1, \'Arial\'',
			"Threshold: 999",
	);

  // Print the header information with hard returns
  $out .= (implode(PHP_EOL, $headerInfo) . PHP_EOL . PHP_EOL);


	if($doDebug) {$out .= "; JSONread entered\n"; }
	$STRopts = array(
		'http' => array(
			'method' => "GET",
			'protocol_version' => 1.1,
			'header' => "Cache-Control: no-cache, must-revalidate\r\n" . 
				"Cache-control: max-age=0\r\n" . 
				"Connection: close\r\n" . 
				"User-agent: Mozilla/5.0 (NWS_Polygon_Alerts_Colored - saratoga-weather.org)\r\n" . 
				"Accept: application/json,application/xml\r\n"
		) ,
		'ssl' => array(
			'method' => "GET",
			'protocol_version' => 1.1,
			'verify_peer' => false,
			'header' => "Cache-Control: no-cache, must-revalidate\r\n" . 
				"Cache-control: max-age=0\r\n" . 
				"Connection: close\r\n" . 
				"User-agent: Mozilla/5.0 (NWS_Polygon_Alerts_Colored - saratoga-weather.org)\r\n" . 
				"Accept: application/json,application/xml\r\n"
		)
	);
	$STRcontext = stream_context_set_default($STRopts);
  
	if(!file_exists($cacheFilename) or
	   (file_exists($cacheFilename) and filemtime($cacheFilename)+$cacheTimeMax < time()) ) {
    $rawJSON = file_get_contents($url);
    file_put_contents($cacheFilename,$rawJSON);
	  $out .= "; cache file '$cacheFilename' refreshed from $url \n\n";
	} else {
		$rawJSON = file_get_contents($cacheFilename);
		$age = time()-filemtime($cacheFilename);
		$out .= "; cache file '$cacheFilename' loaded. age=$age seconds.\n\n";
	}
	
  $JSON = json_decode($rawJSON,true,512,JSON_BIGINT_AS_STRING+JSON_OBJECT_AS_ARRAY);
	#var_export($JSON,false);
	#return;
  
  if(function_exists('json_last_error')) {
  switch (json_last_error()) {
  case JSON_ERROR_NONE:
    $JSONerror = '- No errors';
    break;

  case JSON_ERROR_DEPTH:
    $JSONerror = '- Maximum stack depth exceeded';
    break;

  case JSON_ERROR_STATE_MISMATCH:
    $JSONerror = '- Underflow or the modes mismatch';
    break;

  case JSON_ERROR_CTRL_CHAR:
    $JSONerror = '- Unexpected control character found';
    break;

  case JSON_ERROR_SYNTAX:
    $JSONerror = '- Syntax error, malformed JSON';
    break;

  case JSON_ERROR_UTF8:
    $JSONerror = '- Malformed UTF-8 characters, possibly incorrectly encoded';
    break;

  default:
    $JSONerror = '- Unknown error';
    break;
  }
    
  $out .= "; JSON decode - $JSONerror\n";    
  }

  if (!isset($JSON['features'][0])) {
		$out .= "; .. no data found\n";
		if($doDebug) {$out .= "; JSONread returned\n"; }

    return($out);
  }
  
  $out .= "; ".count($JSON['features'])." features found\n\n";
	$out .= "; using ";
	$out .= $SPCcolors?' SPC stroke colors':' builtin stroke colors';
	$out .= "\n";

  foreach ($JSON['features'] as $idx => $feature) { # do the heavy lifting.. decode each alert in severity sort order
	  if($doDebug) {$out .= "\n; JSONread: idx=$idx calling decodeOutlook\n";}
    $out .= decodeOutlook($feature,$DayLegend);
		if($doDebug) {$out .= ";--------------\n; JSONread: decodeOutlook returns\n";}
  }
	if($doDebug) {$out .= "\n; JSONread returns\n"; }

  return($out);  # this will be appended to $output in main
} // end JSONread function

# -----------------------------------------------------

function decodeOutlook($feature,$DayLegend) {
	global $doDebug,$LEGEND,$latitude,$longitude,$SPCcolors;
	
	$out = '';
	if($doDebug) {$out .= "\n; decodeOutlook entered.\n"; }
	
	$tCode = !empty($feature['properties']['LABEL'])?$feature['properties']['LABEL']:'unk';
	list($title,$color,$line) = isset($LEGEND[$tCode])?
	  explode('|',$LEGEND[$tCode]):
		explode('|',"Severe Weather Outlook ".($tCode*100)."%|Color: 247 246 144|Line: 4, 0,");
	
	$color = ($SPCcolors and isset($feature['properties']['fill']))?
	    convert_hex_color($feature['properties']['fill']):$color;

    // Get the LABEL2 value
#    $label2 = $feature['properties']['LABEL2'];
#    $colors = $feature['properties']['LABEL2'];
	$valid = $feature['properties']['VALID'];
	$pValid = format_date($feature['properties']['VALID']);
	$valid = substr($valid, 8);
	$pExpires = format_date($feature['properties']['EXPIRE']);
	$pIssued = format_date($feature['properties']['ISSUE']);

	$popup  = '"SPC '.$DayLegend.' Convective Outlook:\n'.$title.'\n';
	$popup .= '-----------------------------------------\n';
	$popup .= "Issued:  $pIssued".'\n';
	$popup .= "Valid:   $pValid".'\n';
	$popup .= "Expires: $pExpires".'"'."\n";

  $theLine = "; type=".$feature['geometry']['type']."\n";
  $theLine .= $color. "\n";
  $theLine .= $line.' '.$popup;
/*  $feature['geometry']['coordinates'] looks like (for nolyy polygon:
array (
  0 => 
  array (
    0 => 
    array (
      0 => -75.67573071133666,
      1 => 44.82149387149493,
    ),
    1 => 
    array (
      0 => -74.72,
      1 => 43.64,
    ),
...
    41 => 
    array (
      0 => -75.977,
      1 => 44.604,
    ),
    42 => 
    array (
      0 => -75.67573071133666,
      1 => 44.82149387149493,
    ),
  ),
)
*/

 if(!isset($feature['geometry']['coordinates'][0])) {
	 list($t,$d) = explode(' ',$DayLegend.' 0');  # get day number;
	 $offset = is_numeric($d)?intval($d*15)+15:20;
	 $offset = -$offset;
	 $out .= "; No Significant Convective Outlook for $DayLegend issued. (no geometry coordinates in JSON)\n";
	 $out .= "Object: $latitude,$longitude\n";
	 $out .= "Color: 255 255 255\n";
	 $out .= "Text: 1, $offset, 1, \"SPC: No Risk Areas for $DayLegend.\"\n";
	 $out .= "End:\n";
	 
	 return($out);
 }
   #print "; coordinates\n; ----------\n".var_export($feature['geometry']['coordinates'],true)."\n; ----------\n";
# Text: lat, lon, fontNumber, "string", "hover"
 $txtMarker = "Color: 255 255 255\n";
 #$txtMarker = "$color\n";
 $txtCode = is_numeric($tCode)?(string)($tCode*100)."%":$tCode;
 if($feature['geometry']['type'] == "Polygon") { # process simple polygon
   $out .= $theLine; # insert color/line info
   foreach($feature['geometry']['coordinates'] as $i => $C) {
     foreach ($C as $SET) {
        $out .= $SET[1] .",".$SET[0]. "\n";
				$txtMarker .= "Text: ".$SET[1] . "," . $SET[0].',1,"'.$txtCode.'",'.$popup;
     }
	 }
   $out.= "End:"."\n\n"; # finish Line set

 } else { #Multipolygon
	 foreach($feature['geometry']['coordinates'] as $i => $R) {
     $out .= "; polygon #$i\n".$theLine; # insert color/line info
		 foreach ($R as $C) {
			 foreach ($C as $SET) {
					$out .= $SET[1] .",".$SET[0]. "\n";
				  $txtMarker .= "Text: ".$SET[1] . "," . $SET[0].',1,"'.$txtCode.'",'.$popup;
			 }
		 }
	   $out.= "End:"."\n\n"; # finish Line set
	 }
   // Convert coordinates array to string and remove brackets

  }
 $out .= "; text markers\n";
 $out .= $txtMarker;
 $out .= "; end text markers $txtCode\n\n";
	if($doDebug) {$out .= "\n; decodeOutlook returned.\n"; }

return( $out );
	
} # end decodeOutlook

# -----------------------------------------------------

function convert_hex_color($CSScolor) {
	# expect CSScolor like "#55BB55"
	if(substr($CSScolor,0,1) !== '#' or strlen($CSScolor) !== 7) {
		return ('Color: 128 128 128');
	}
	$R = hexdec(substr($CSScolor,1,2));
	$G = hexdec(substr($CSScolor,3,2));
	$B = hexdec(substr($CSScolor,5,2));
	return("Color: $R $G $B");
}

# -----------------------------------------------------

function format_date($indate) {
	# input date format '202311111609'
	$t = substr($indate,0,8)."T".substr($indate,8,4)."00+00:00";
	return(date("D g:ia T M j Y",strtotime($t)));
}
