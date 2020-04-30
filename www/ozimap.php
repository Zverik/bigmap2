<?php // BigMap 2 OziExplorer MAP export. Written by Ilya Zverev, licensed WTFPL.
header('Content-type: application/x-map');
header('Content-disposition: attachment; filename="' . $name . '.map"');

$width = ($xmax - $xmin + 1) * 256;
$height = ($ymax - $ymin + 1) * 256;
$lon2 = ($lon_min + $lon_max) / 2;
$lat2 = ($lat_min + $lat_max) / 2;
$m_per_px = 156543.034 * cos(deg2rad($lat2)) / $zoom2;

function deg( $value, $is_lon ) {
	$deg = floor(abs($value));
	$min = (abs($value) - floor(abs($value))) * 60;
	return sprintf('%4d,%3.5F,%s', $deg, $min, $value < 0 ? ($is_lon ? 'W' : 'S') : ($is_lon ? 'E' : 'N'));
}
?>
OziExplorer Map Data File Version 2.2
BigMap
map<?=$name ?>.png
1 ,Map Code,
WGS 84,WGS 84,   0.0000,   0.0000,WGS 84
Reserved 1
Reserved 2
Magnetic Variation,,,E
Map Projection,Mercator,PolyCal,No,AutoCalOnly,No,BSBUseWPX,No
Point01,xy,    0,    0,in, deg,<?=deg($lat_max, false) ?>,<?=deg($lon_min, true) ?>, grid,   ,           ,           ,N
Point02,xy, <?=sprintf('%4d', $width-1) ?>, <?=sprintf('%4d', $height-1) ?>,in, deg,<?=deg($lat_min, false) ?>,<?=deg($lon_max, true) ?>, grid,   ,           ,           ,N
Point03,xy,     ,     ,in, deg,    ,        ,N,    ,        ,E, grid,   ,           ,           ,N
Point04,xy,     ,     ,in, deg,    ,        ,N,    ,        ,E, grid,   ,           ,           ,N
Point05,xy,     ,     ,in, deg,    ,        ,N,    ,        ,E, grid,   ,           ,           ,N
Point06,xy,     ,     ,in, deg,    ,        ,N,    ,        ,E, grid,   ,           ,           ,N
Point07,xy,     ,     ,in, deg,    ,        ,N,    ,        ,E, grid,   ,           ,           ,N
Point08,xy,     ,     ,in, deg,    ,        ,N,    ,        ,E, grid,   ,           ,           ,N
Point09,xy,     ,     ,in, deg,    ,        ,N,    ,        ,E, grid,   ,           ,           ,N
Point10,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point11,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point12,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point13,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point14,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point15,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point16,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point17,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point18,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point19,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point20,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point21,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point22,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point23,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point24,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point25,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point26,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point27,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point28,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point29,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Point30,xy,     ,     ,in, deg,    ,        ,N,    ,        ,W, grid,   ,           ,           ,N
Projection Setup,,,,,,,,,,
Map Feature = MF ; Map Comment = MC     These follow if they exist
Track File = TF      These follow if they exist
Moving Map Parameters = MM?    These follow if they exist
MM0,Yes
MMPNUM,4
MMPXY,1,0,0
<?php
echo "MMPXY,2,$width,0\n";
echo "MMPXY,3,$width,$height\n";
echo "MMPXY,4,0,$height\n";
echo 'MMPLL,1,'.sprintf('%4.6f', $lon_min).','.sprintf('%4.6f', $lat_max)."\n";
echo 'MMPLL,2,'.sprintf('%4.6f', $lon_max).','.sprintf('%4.6f', $lat_max)."\n";
echo 'MMPLL,3,'.sprintf('%4.6f', $lon_max).','.sprintf('%4.6f', $lat_min)."\n";
echo 'MMPLL,4,'.sprintf('%4.6f', $lon_min).','.sprintf('%4.6f', $lat_min)."\n";
echo "MM1B,$m_per_px\n";
echo "MOP,Map Open Position,0,0\n";
echo "IWH,Map Image Width/Height,$width,$height\n";
