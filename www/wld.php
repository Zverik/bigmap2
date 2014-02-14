<?php // BigMap 2 WLD export. Written by Ilya Zverev, licensed WTFPL.
header('Content-type: application/x-wld');
header('Content-disposition: attachment; filename="'.$basename.'.wld"');

$width = ($xmax - $xmin + 1) * 256;
$height = ($ymax - $ymin + 1) * 256;
$pixel_x_size = ($lon_max - $lon_min) / $width;
$pixel_y_size = ($lat_max - $lat_min) / $height;
$left_pixel_center_lon = $lon_min + $pixel_x_size / 2;
$top_pixel_center_lat = $lat_max + $pixel_y_size / 2;

echo sprintf('%.8f', $pixel_x_size)."\n";
echo "0.00000000\n";
echo "0.00000000\n";
echo sprintf('%.8f', -$pixel_y_size)."\n";
echo sprintf('%.8f', $left_pixel_center_lon)."\n";
echo sprintf('%.8f', $top_pixel_center_lat);
