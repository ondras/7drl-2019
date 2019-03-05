<?php

define("LEVELS", 2);
include "color.php";
include "creatures.php";

$counter = 0;
function id() {
	global $counter;
	return $counter++;
}

function free_position(&$level) {
	$w = $level["size"][0];
	$h = $level["size"][1];
	while (1) {
		$x = mt_rand(0, $w-1);
		$y = mt_rand(0, $h-1);
		$key = "{$x},{$y}";
		if (in_array($key, $level["used"])) { continue; }

		$level["used"][] = $key;
		return array($x, $y);
	}
}

function creature_from_template($name, $position) {
	global $creature_templates;
	$t = $creature_templates[$name];
	$c = array(
		"id" => id(),
		"letter" => $t["letter"],
		"name" => $t["name"],
		"position" => $position,
		"key" => $t["key"]
	);

	if (mt_rand(0, 1) == 0) {
		$c["letter"] = strtoupper($c["letter"]);
	}

	$c["color"] = expand_color($t["color"]);
	return $c;
}

function generate_creatures(&$level) {
	global $creature_templates;

	$names = array_keys($creature_templates);
	shuffle($names);
	$half = floor(count($names)/2);

	$templates_ok = array_slice($names, 0, $half);
	$templates_ko = array_slice($names, $half);
	foreach ($templates_ok as $name) { $creature_templates[$name]["key"] = true; }
	foreach ($templates_ko as $name) { $creature_templates[$name]["key"] = false; }

	$creatures = array();

	$test = array("goblin", "orc", "bat", "kobold");
	foreach ($test as $name) {
		$pos = free_position($level);
		$creatures[] = creature_from_template($name, $pos);
		if (mt_rand(0, 1)) {  // has gold
			$level["gold"][] = array(
				"id" => id(),
				"position" => $pos
			);
		}
	}

	$level["creatures"] = $creatures;
}

function generate_gold(&$level) {
	$count = $level["number"];
	$gold = array();

	for ($i=0;$i<$count;$i++) {
		$pos = free_position($level);
		$gold[] = array(
			"id" => id(),
			"position" => $pos
		);
	}

	$level["gold"] = $gold;
}

function generate_pc(&$level) {
	$level["pc"] = free_position($level); 
}

function generate_intro(&$level) {
	$level["intro"] = "<p>Welcome.</p>"; // fixme
}

function generate_level($number, $seed) {
	mt_srand($seed + $number);
	$level = array();

	$level["used"] = array();
	$level["number"] = $number;
	$level["hp"] = 3;
	$level["keys"] = min($number, 3);
	$level["size"] = array(11, 5); // fixme

	generate_intro($level);
	generate_gold($level);
	generate_creatures($level);
	generate_pc($level);

	$url_reload = "?seed={$seed}&amp;level={$number}";
	$url_new = ".";
	$level["gameover"] = "<p><strong>Game over!</strong> You have lost all your health. Try to fight those creatures that leave keys in order to finish the level.</p>
							<p>You can try <a href='{$url_reload}'>reloading</a> this level or starting a <a href='{$url_new}'>new game</a>.</p>";

	$next = $number+1;
	if ($next > LEVELS) {
		$level["victory"] = "<p><strong>Congratulations!</strong> You managed to escape all levels designed by the mad General Sibling Combinator and won the game! This is a heroic achievement indeed. We hope you liked the game!</p>
							<p>Your quest is over now, but if you seek additional thrill and adventure, you are free to study this game's <a href='http://github.com/ondras/7drl-2019'>source code</a>.</p>";
	} else {
		$url_next = "?seed={$seed}&amp;level={$next}";
		$level["victory"] = "<p>Good job! You managed to retrieve all keys, gathering <span class='gold-count'></span> gold in the process. Please continue to the <a href='{$url_next}'>next level</a> immediately.";
	}

	return $level;
}

?>
