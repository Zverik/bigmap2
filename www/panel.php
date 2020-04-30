<?php // BigMap 2 Panel. Written by Frederik Ramm and Ilya Zverev, licensed WTFPL.

// temporary perl code
    print '<div id="control" style="align:center;position:fixed;top:50px;margin-left:50px;margin-right:50px;padding:10px;background:#ffffff;opacity:0.8;border:solid 1px;border-color:green;">';
    $widtiles = $xmax-$xmin+1;
    $heitiles = $ymax-$ymin+1;
    $widpix = $widtiles*256;
    $heipix = $heitiles*256;
    $asp=asp($widtiles,$heitiles);

    printf("Map \"%s\" is %dx%d tiles (%dx%d px) at zoom %d, aspect %s<br>",
        $name,$widtiles,$heitiles,$widpix,$heipix,$zoom,$asp);
    echo '<table cellspacing="0" cellpadding="2"><tr>';
    echo tde(); // td("tl", "right", $xmin-1, $xmax, $ymin-1, $ymax, $zoom);
    echo $ymin <= 0 ? tde('top') : td("top", "center", $xmin, $xmax, $ymin-1, $ymax, $zoom, $name);
    echo tde(); // td("tr", "left", $xmin, $xmax+1, $ymin-1, $ymax, $zoom);
    echo "<td>&nbsp;</td>";
    echo tde(); // td("ul", "right", $xmin-1, $xmax-1, $ymin-1, $ymax-1, $zoom);
    echo $ymin <= 0 ? tde('up') : td("up", "center", $xmin, $xmax, $ymin-1, $ymax-1, $zoom, $name);
    echo tde(); // td("ur", "left", $xmin+1, $xmax+1, $ymin-1, $ymax-1, $zoom);
    echo "<td>&nbsp;</td>";
    echo tde(); // td("tl", "right", $xmin+1, $xmax, $ymin+1, $ymax, $zoom);
    echo $ymin == $ymax ? tde('top') : td("top", "center", $xmin, $xmax, $ymin+1, $ymax, $zoom, $name);
    echo tde(); // td("tr", "left", $xmin, $xmax-1, $ymin+1, $ymax, $zoom);
    echo "</tr><tr>";
    echo $xmin <= 0 ? tde('left', 'right') : td("left", "right", $xmin-1, $xmax, $ymin, $ymax, $zoom, $name);
    echo "<td align='center' bgcolor='#aaaaaa'><b>EXPAND</b></td>";
    echo $xmax >= $zoom2-1 ? tde('right', 'left') : td("right", "left", $xmin, $xmax+1, $ymin, $ymax, $zoom, $name);
    echo "<td>&nbsp;</td>";
    echo $xmin <= 0 ? tde('left', 'right') : td("left", "right", $xmin-1, $xmax-1, $ymin, $ymax, $zoom, $name);
    echo "<td align='center' bgcolor='#aaaaaa'><b>SHIFT</b></td>";
    echo $xmax >= $zoom2-1 ? tde('right', 'left') : td("right", "left", $xmin+1, $xmax+1, $ymin, $ymax, $zoom, $name);
    echo "<td>&nbsp;</td>";
    echo $xmin == $xmax ? tde('left', 'right') : td("left", "right", $xmin+1, $xmax, $ymin, $ymax, $zoom, $name);
    echo "<td align='center' bgcolor='#aaaaaa'><b>SHRINK</b></td>";
    echo $xmin == $xmax ? tde('right', 'left') : td("right", "left", $xmin, $xmax-1, $ymin, $ymax, $zoom, $name);
    echo "</tr><tr>";
    echo tde(); // td("bl", "right", $xmin-1, $xmax, $ymin, $ymax+1, $zoom);
    echo $ymax >= $zoom2-1 ? tde('bottom') : td("bottom", "center", $xmin, $xmax, $ymin, $ymax+1, $zoom, $name);
    echo tde(); // td("br", "left", $xmin, $xmax+1, $ymin, $ymax+1, $zoom);
    echo "<td>&nbsp;</td>";
    echo tde(); // td("dl", "right", $xmin-1, $xmax-1, $ymin+1, $ymax+1, $zoom);
    echo $ymax >= $zoom2-1 ? tde('down') : td("down", "center", $xmin, $xmax, $ymin+1, $ymax+1, $zoom, $name);
    echo tde(); // td("dr", "left", $xmin+1, $xmax+1, $ymin+1, $ymax+1, $zoom);
    echo "<td>&nbsp;</td>";
    echo tde(); // td("bl", "right", $xmin+1, $xmax, $ymin, $ymax-1, $zoom);
    echo $ymin == $ymax ? tde('bottom') : td("bottom", "center", $xmin, $xmax, $ymin, $ymax-1, $zoom, $name);
    echo tde(); // td("br", "left", $xmin, $xmax-1, $ymin, $ymax-1, $zoom);
    echo "</tr><tr><td></td></tr>";
    echo "<tr><td colspan='11'><table bgcolor='#aaaaaa' width='100%' border='0' cellpadding='0' cellspacing='0'><tr>";
    echo "<td>&nbsp;</td>";
    echo td("in/double size", "left", $xmin*2,$xmax*2+1,$ymin*2,$ymax*2+1,$zoom+1, $name);
    echo "<td>&nbsp;</td>";
    echo td("in/keep size", "left", $xmin*2+($xmax-$xmin)/2,$xmax*2-($xmax-$xmin)/2,$ymin*2+($ymax-$ymin)/2,$ymax*2-($ymax-$ymin)/2,$zoom+1, $name);
    echo "<td>&nbsp;</td>";
    echo "<td bgcolor='#aaaaaa'><b>ZOOM</b></td>";
    echo "<td>&nbsp;</td>";
    echo td("out/keep size", "left", $xmin/2-($xmax-$xmin)/4,$xmax/2+($xmax-$xmin)/4,$ymin/2-($ymax-$ymin)/4,$ymax/2+($ymax-$ymin)/4,$zoom-1, $name);
    echo "<td>&nbsp;</td>";
    echo td("out/halve size", "left", $xmin/2,$xmax/2,$ymin/2,$ymax/2,$zoom-1, $name);
    echo "</tr></table></td></tr><tr><td></td></tr>";
    echo "<tr><td colspan='11'><table bgcolor='#aaaaaa' width='100%' border='0' cellpadding='0' cellspacing='0'><tr>";
    echo "<td>&nbsp;</td>";
    echo preg_replace('/\?[^"]+/', sprintf('index.html#map=%d/%f/%f',$zoom,($lat_min+$lat_max)/2,($lon_min+$lon_max)/2), td("<b>BigMap</b>", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name));
    echo "<td>&nbsp;</td>";
    echo td("Permalink", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name);
    echo "<td>&nbsp;</td>";
    echo str_replace('?', '?action=ozimap&', td("OZI", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name));
    echo "<td>&nbsp;</td>";
    echo str_replace('?', '?action=wld&', td("WLD", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name));
    echo "<td>&nbsp;</td>";
    echo str_replace('?', '?action=perl&', td("Perl", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name));
    echo "<td>&nbsp;</td>";
    echo str_replace('?', '?action=python&', td("Py", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name));
    echo "<td>&nbsp;</td>";
    echo ($xmax-$xmin+1) * ($ymax-$ymin+1) > $max_tiles ? tde("Enqueue", "left") :  str_replace('?', '?action=enqueue&', td("Enqueue", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name));
    echo "<td>&nbsp;</td>";
    echo td("100", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name,256);
    echo "<td>/</td>";
    echo td("50", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name,128);
    echo "<td>/</td>";
    echo td("25%", "left", $xmin,$xmax,$ymin,$ymax,$zoom,$name,64);
    echo "<td>&nbsp;</td>";
    echo "<td align='right'><a href=\"#\" onclick=\"getElementById('control').style.display='none';\">hide this</a></td>";
    echo "</tr></table></td></tr></table>";
    echo "</div></div>";

# functions to create aspect ratio
function gcd($a,$b) {
    return ($a % $b) ? gcd($b,$a % $b) : $b;
}

function asp($w, $h) {
	$gcd = gcd($w,$h);
	return $w / $gcd . ":" . $h / $gcd;
}

# helper to display a table cell with a parametrized link inside
function td($what, $align, $xmi, $xma, $ymi, $yma, $zm, $nm, $scl = 0) {
    global $scale, $tiles;
    if( !$scl ) $scl = $scale;
    return sprintf('<td bgcolor="#aaaaaa" align="%s"><a href="?xmin=%d&xmax=%d&ymin=%d&ymax=%d&zoom=%d&name=%s&scale=%d&tiles=%s" rel="nofollow">%s</a></td>',
        $align,
        $xmi, $xma,
        $ymi, $yma, 
        $zm, $nm, $scl, $tiles, $what);
}

function tde($text = '', $align = 'center') {
    return '<td bgcolor="#aaaaaa" align="'.$align.'">'.$text.'</td>';
}
?>
