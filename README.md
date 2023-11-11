# SPC-placefile
## GRLevelX placefile generator for risk hazards from [NOAA-NWS Storm Prediction Center](https://www.spc.noaa.gov/)
## Purpose:

This script set gets JSON outlook data from the [SPC GIS Convective Outlooks](https://www.spc.noaa.gov/gis/) and formats a placefile for GRLevelX software
to display outlines of the areas with risk hazards.
A mouse-over the line will popup a text tooltip with details about the risk area.

The *SPC-placefile.php* script is to be accessed by including the website URL in the GRLevelX placefile manager window.
There are 'stub' scripts to invoke *SPC-placefile.php* with various days selected:

- *day1.php*  Selects risk areas for Day 1. (same as invoking `SPC-placefile.php?day=1`)
- *day2.php*  Selects risk areas for Day 2. (same as invoking `SPC-placefile.php?day=2`)
- *day3.php*  Selects risk areas for Day 3. (same as invoking `SPC-placefile.php?day=3`)
- *day4.php*  Selects risk areas for Day 4. (same as invoking `SPC-placefile.php?day=4`)
- *day5.php*  Selects risk areas for Day 5. (same as invoking `SPC-placefile.php?day=5`)
- *day6.php*  Selects risk areas for Day 6. (same as invoking `SPC-placefile.php?day=6`)
- *day7.php*  Selects risk areas for Day 7. (same as invoking `SPC-placefile.php?day=7`)
- *day8.php*  Selects risk areas for Day 8. (same as invoking `SPC-placefile.php?day=8`)

For ease of usage, the above 8 scripts should be the end-point targets for the GRLevelX placefile manager window.
 
The cache files are named *SPC-N.json* (N=1..8) and are created/maintained automatically with a default 8 minute cache lifetime.
 
## Scripts:

### *SPC-placefile.php*

This script generates a GRLevelX placefile from the cached JSON  
file from SPC GIS on demand by a GRLevel3 instance.

If you run the script for debugging **in a browser**, add `?version=1.5&dpi=96&lat={latitude}&lon={longitude}` to
the URL so it knows what to select for display.  The output of the script is always text/plain;charset=ISO-8859-1 for the placefile.
Viewing the placefile in a browser will show GRLevel3 comments (lines starting with ';') showing how each alert is handled.

In the GRLevel3 placefile manager window, just add the script URL without a query string -- GRLevel3 will automatically add those based on the current radar site selected.

Additional documentation is in each script for further modification convenience.

## Installation

Upload the files/directories into a directory *under* the document root of your website.  
(We used 'placefiles' in the examples below)

Then you can test the placefile script by **using your browser** to go to<br>
`https://your.website.com/placefiles/day1.php?version=1.5&dpi=96&lat=37.0&lon=-122.0`

If that returns a placefile, then add your placefile URL (minus the URL query string) into the GRLevelX placefile
manager window.

## Settings in *SPC-placefile.php*

```php
# -----------------------------------------------
#  Settings
$timeFormat = "d-M-Y g:ia T";           # display format for times
$cacheFilenameTemplate = 'SPC-%s.json'; # store json cache file
$cacheTimeMax  = 480;                   # number of seconds cache is 'fresh'
$doLogging = true;
$doDebug = false; # =true; turn on additional display, may break placefile for GRLevelX
$TZ = "America/Los_Angeles";
$SPCcolors= false;  # =true, use JSON 'stroke' color; =false, use built-in colors

$latitude = 37.155;
$longitude = -121.898;
$version  = 1.5;
# -----------------------------------------------

```

Please note that the default installation includes `$doLogging = true;` which enables logging of accesses 
to <br> *SPC-placefile-log-{year}-{month}-{day}.txt* (UTC date) text files to the installation directory.
These files are useful to help debugging, but you may want to switch them off by specifying `$doLogging = false;`.

## Sample *SPC-placefile.php* output:
```
; SPC-placefile.php - V1.00 - 10-Nov-2023 - saratoga-weather.org
; running on PHP 8.2.0
Title: SPC Day 1 Outlook - Fri Nov 10 13:27:18 PST 2023 - PlacefileNation
Refresh: 5
Color: 200 200 255
Font: 1, 11, 1, 'Arial'
Threshold: 999

; cache file 'SPC-1.json' refreshed from https://www.spc.noaa.gov/products/outlook/day1otlk_cat.lyr.geojson 

; JSON decode - - No errors
; 1 features found

; using  builtin stroke colors
; polygon #0
; type=MultiPolygon
Color: 183 233 193
Line: 4, 0, "SPC Day 1 Outlook - General Thunderstorm Risk 2000"
29.0050330131,-94.591263231441
28.808,-94.9
28.597,-95.134
28.428,-95.489
28.182,-96.044
28.055,-96.147
27.94,-96.427
27.579,-96.796
27.081,-97.017
26.595,-96.876
25.948,-96.749
25.799,-97.075
25.659,-97.23
25.837,-97.68
25.934,-98.218
26.039,-98.509
26.254,-99.103
26.519,-99.357
26.735876493841,-99.457448060305
28,-98.1
29.1,-95.61
29.0050330131,-94.591263231441
End:

; polygon #1
; type=MultiPolygon
Color: 183 233 193
Line: 4, 0, "SPC Day 1 Outlook - General Thunderstorm Risk 2000"
30.000914625093,-86.924182925019
29.878,-87.771
29.863,-88.406
29.714,-88.432
29.394,-88.695
29.249,-88.581
29.022,-88.619
28.691,-88.937
28.63,-89.175
28.577,-89.399
28.693,-89.712
28.916,-89.828
28.755,-90.102
28.711,-90.54
28.711,-90.745
28.732,-91.083
28.733956887596,-91.085819862871
28.733956887596,-91.085819862871
30,-90.3
30.79,-89.3
30.98,-88.27
30.63,-87.28
30.13,-86.95
30.000914625093,-86.924182925019
End:

; polygon #2
; type=MultiPolygon
Color: 183 233 193
Line: 4, 0, "SPC Day 1 Outlook - General Thunderstorm Risk 2000"
46.867344015155,-124.42312443269
46.914,-124.45
47.095,-124.416
47.322,-124.59
47.654,-124.653
47.833,-124.891
48.196,-125.021
48.603,-125.022
48.528,-124.41
48.375,-123.817
48.395,-123.588
48.452,-123.444
48.604,-123.444
48.73,-123.553
48.947,-123.617
49.187,-123.29
49.134,-123.111
49.303,-122.837
49.247,-122.534
49.18,-121.576
49.186263712123,-121.23865436137
48.51,-122.17
47.46,-123.03
47.21,-123.69
46.867344015155,-124.42312443269
End:
```

## Display samples with GRLevel3
### Day 1 Sample
![SPC-sample-day1](https://github.com/ktrue/SPC-placefile/assets/17507343/c5ffdc58-dcc3-4c86-8b6f-57f296d2c9c9)

### Day 2 Sample
![SPC-sample-day2](https://github.com/ktrue/SPC-placefile/assets/17507343/768b95ab-c21b-408b-b1b9-5ce67d8dabda)

### Day 3 Sample
![SPC-sample-day3](https://github.com/ktrue/SPC-placefile/assets/17507343/75b807e1-801a-499c-afdb-fd1ed1b1373b)

### Sample with all risks displayed
![SPC-sample-test](https://github.com/ktrue/SPC-placefile/assets/17507343/5e781238-6d5f-488e-8ade-ec309a2b0727)

## Acknowledgements

Special thanks to Mike Davis, W1ARN of the National Weather Service, Nashville TN office
for his inspiration/testing/feedback during development.

