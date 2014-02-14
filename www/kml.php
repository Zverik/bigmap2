<?php // BigMap 2 KML export. Written by Ilya Zverev, licensed WTFPL.
header('Content-type: application/vnd.google-earth.kml+xml');
header('Content-disposition: attachment; filename="'.$basename.'.kml"');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
?>
<kml xmlns="http://www.opengis.net/kml/2.2">
	<GroundOverlay>
		<Icon>
			<href><?=$basename ?>.png</href>
		</Icon>
		<LatLonBox>
			<north><?=$lat_max ?></north>
			<south><?=$lat_min ?></south>
			<east><?=$lon_max ?></east>
			<west><?=$lon_min ?></west>
			<rotation>0</rotation>
		</LatLonBox>
	</GroundOverlay>
</kml>