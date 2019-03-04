<?php

$counter = 0;
function id() {
	global $counter;
	return $counter++;
}

function generate_creatures($number) {
	$creatures = array();
	$creatures[] = array(
		"id" => id(),
		"letter" => "g",
		"name" => "Goblin",
		"key" => true,
		"position" => array(3, 2)
	);
	$creatures[] = array(
		"id" => id(),
		"letter" => "o",
		"name" => "Orc",
		"key" => false,
		"position" => array(7, 2)
	);
	return $creatures;
}

function generate_items($number) {
	$items = array();
	for ($i=0;$i<3;$i++) {
		$items[] = array("type" => "gold", "id" => id());
	}
	return $items;
}

function generate_intro($number) {
	return  "This is intro."; // fixme
}

function generate_level($number, $seed) {
	mt_srand($seed + $number);

	$level = array();
	$next = $number+1;
	$level["url_next"] = "?seed={$seed}&level={$next}";
	$level["url_reload"] = "?seed={$seed}&level={$number}";
	$level["url_new"] = ".";
	$level["intro"] = generate_intro($number);
	$level["size"] = array(11, 5);
	$level["pc"] = array(floor($level["size"][0]/2), floor($level["size"][1]/2));
	$level["creatures"] = generate_creatures($number);
	$level["items"] = generate_items($number);
	return $level;
}

?>
