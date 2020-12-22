<?php // BigMap 2. Written by Ilya Zverev, licensed WTFPL.
define('IN_BIGMAP', true);

$max_tiles = 100;

$zoom = min(20, req_num('zoom'));
$zoom2 = pow(2, $zoom);
$xmin = max(0, req_num('xmin'));
$ymin = max(0, req_num('ymin'));
$xmax = min($zoom2 - 1, req_num('xmax'));
$ymax = min($zoom2 - 1, req_num('ymax'));
if( $xmax < $xmin ) $xmax = $xmin;
if( $ymax < $ymin ) $ymax = $ymin;
$xsize = $xmax - $xmin + 1;
$ysize = $ymax - $ymin + 1;
$llmin = tile2latlon($xmin, $ymax);
$llmax = tile2latlon($xmax, $ymin);
$lat_min = $llmin[0];
$lon_min = $llmin[1];
$lat_max = $llmax[2];
$lon_max = $llmax[3];
$scale = req_num('scale', 256);
$tiles = isset($_REQUEST['tiles']) && preg_match('/^[a-z0-9|-]+$/', $_REQUEST['tiles']) ? $_REQUEST['tiles'] : 'mapnik';
$layers = get_layers($tiles, $zoom);
$redirect = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/';
$permalink = $redirect."bigmap.php?xmin=$xmin&xmax=$xmax&ymin=$ymin&ymax=$ymax&zoom=$zoom&scale=$scale&tiles=$tiles";
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : 'Bigmap';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if( $action == 'ozimap' ) {
	require('ozimap.php');
	exit;
} else if( $action == 'wld' ) {
	require('wld.php');
	exit;
} else if( $action == 'kml' ) {
	require('kml.php');
	exit;
} else if( $action == 'perl' ) {
	require('perl.php');
	exit;
} else if( $action == 'python' ) {
	require('python.php');
	exit;
} else if( $action == 'enqueue' ) {
	require('queue.php');
	exit;
}

function req_num($name, $default = 0) {
	return isset($_REQUEST[$name]) && preg_match('/^\d+$/', $_REQUEST[$name]) ? $_REQUEST[$name] : $default;
}

function get_layers($tiles, $zoom) {
	global $attribution, $attrib_plain;
	$needed = explode('|', $tiles);
	$result = array();
	$attribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a>';
	$file = @fopen('tiles.txt', 'r');
	if( $file ) {
		while( ($line = fgets($file)) !== false ) {
			$layer = explode(',', chop($line));
			if( in_array($layer[0], $needed) && (count($result) ? $layer[1] : !$layer[1]) && $layer[2] <= $zoom && $layer[3] >= $zoom ) {
				$result[] = $layer[4];
				if( strlen($layer[5]) )
					$attribution .= ', '.$layer[5];
			}
			if( count($result) >= 4 )
				break;
		}
		fclose($file);
	}
	if( !count($result) )
		$result[] = 'http://tile.openstreetmap.org/!z/!x/!y.png';
	$attrib_plain = str_replace('&copy;', '(c)', preg_replace('/<[^>]+>/', '', $attribution));
	return $result;
}

function tile2latlon($x, $y) {
	global $zoom2;
	// taken from http://wiki.openstreetmap.org/wiki/Slippy_map_tilenames#Perl
	$relY1 = M_PI * (1 - 2 * $y / $zoom2);
	$relY2 = M_PI * (1 - 2 * ($y + 1) / $zoom2);
	$lat1 = rad2deg(atan(sinh($relY1)));
	$lat2 = rad2deg(atan(sinh($relY2)));
	$lon1 = 360 * ($x / $zoom2 - 0.5);
	$lon2 = $lon1 + 360 / $zoom2;
	return array($lat2, $lon1, $lat1, $lon2);
}

?>
<!doctype html>
<html>
<head>
<title>BigMap 2</title>
<meta charset="utf-8">
<meta name="robots" content="noindex, nofollow">
</head>
<body>
<?php
for( $y = $ymin; $y <= $ymax; $y++ ) {
	for( $x = $xmin; $x <= $xmax; $x++ ) {
		$xp = $scale * ($x - $xmin);
		$yp = $scale * ($y - $ymin);
		$style = "style=\"position: absolute; left: ${xp}px; top: ${yp}px; width: ${scale}px; height: ${scale}px\"";
		for( $l = 0; $l < count($layers); $l++ ) {
			$bg = str_replace('!x', $x, str_replace('!y', $y, str_replace('!z', $zoom, $layers[$l])));
			if( preg_match('/{([a-z0-9]+)}/', $bg, $m) )
				$bg = str_replace($m[0], substr($m[1], rand(0, strlen($m[1]) - 1), 1), $bg);
			echo "<img src=\"$bg\" $style onclick=\"getElementById('control').style.display='block';\">";
		}
	}
}
echo "<div style=\"position: absolute; left: 5px; top: ".($scale*($ymax-$ymin+1)-15)."px; font-size: 8px;\">$attribution</div>\n";

require('panel.php');
?>
</body>
</html>
