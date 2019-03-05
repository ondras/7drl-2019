<?php

define("LEVELS", 2);
include "color.php";
include "creatures.php";

$counter = 0;
function id() {
	global $counter;
	return $counter++;
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

function generate_creatures($number) {
	$creatures = array();
	$creatures[] = creature_from_template("goblin", array(3, 2));
	$creatures[] = creature_from_template("orc", array(7, 2));
	$creatures[] = creature_from_template("bat", array(7, 3));
	$creatures[] = creature_from_template("kobold", array(3, 1));

	return $creatures;
}

function generate_gold($number) {
	$gold = array();
	$gold[] = array(
		"id" => id(),
		"position" => array(4, 2)
	);
	$gold[] = array(
		"id" => id(),
		"position" => array(3, 2)
	);
	$gold[] = array(
		"id" => id(),
		"position" => array(3, 1)
	);
	return $gold;
}

function generate_intro($number) {
	return  "This is intro."; // fixme
}

function generate_level($number, $seed) {
	mt_srand($seed + $number);
	$level = array();

	$level["hp"] = 2;
	$level["keys"] = 2;
	$level["intro"] = generate_intro($number);
	$level["size"] = array(11, 5);
	$level["pc"] = array(floor($level["size"][0]/2), floor($level["size"][1]/2));
	$level["creatures"] = generate_creatures($number);
	$level["gold"] = generate_gold($number);

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
