<?php

function hsv2rgb($hue,$sat,$val) {;
	$rgb = array(0, 0, 0);
	//calc rgb for 100% SV, go +1 for BR-range
	for($i=0;$i<4;$i++) {
		if (abs($hue - $i*120)<120) {
			$distance = max(60,abs($hue - $i*120));
			$rgb[$i % 3] = 1 - (($distance-60) / 60);
		}
	}
	//desaturate by increasing lower levels
	$max = max($rgb);
	$factor = 255 * ($val/100);
	for($i=0;$i<3;$i++) {
		//use distance between 0 and max (1) and multiply with value
		$rgb[$i] = round(($rgb[$i] + ($max - $rgb[$i]) * (1 - $sat/100)) * $factor);
	}
	return sprintf("#%02X%02X%02X", $rgb[0], $rgb[1], $rgb[2]);
}

function expand_color($c) {
	$h = $c["h"];
	$s = $c["s"];
	$v = $c["v"];
	if (is_array($h)) { $h = mt_rand($h[0], $h[1]); }
	if (is_array($s)) { $s = mt_rand($s[0], $s[1]); }
	if (is_array($v)) { $v = mt_rand($v[0], $v[1]); }

	return hsv2rgb($h, $s, $v);
}

?>
