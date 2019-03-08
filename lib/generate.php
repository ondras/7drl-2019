<?php

define("LEVELS", 7);
include "color.php";
include "creatures.php";

$counter = 0;
function id() {
	global $counter;
	return $counter++;
}

function free_position(&$level, $force = false) {
	$w = $level["size"][0];
	$h = $level["size"][1];
	while (1) {
		$x = mt_rand(0, $w-1);
		$y = mt_rand(0, $h-1);
		if ($force) {
			$x = $force[0];
			$y = $force[1];
			$force = false;
		}
		$key = "{$x},{$y}";
		if (in_array($key, $level["used"])) { continue; }

		$level["used"][] = $key;
		return array($x, $y);
	}
}

function creature_from_template($name, $position) {
	global $creature_templates;
	$corpses = array("*", "%", "&");
	$corpse = $corpses[array_rand($corpses)];
	$t = $creature_templates[$name];
	$c = array(
		"id" => id(),
		"letter" => $t["letter"],
		"name" => $t["name"],
		"corpse" => $corpse,
		"position" => $position,
		"key" => $t["key"]
	);

	if (mt_rand(0, 1)) {
		$c["letter"] = strtoupper($c["letter"]);
	}

	$c["color"] = expand_color($t["color"]);
	return $c;
}

function init_creatures() {
	global $creature_templates;
	shuffle($creature_templates);
}

function generate_creatures(&$level) {
	global $creature_templates;

	$names = array_keys($creature_templates);
	$half = floor(count($names)/2);

	$names_ok = array_slice($names, 0, $half);
	$names_ko = array_slice($names, $half);
	foreach ($names_ok as $name) { $creature_templates[$name]["key"] = true; }
	foreach ($names_ko as $name) { $creature_templates[$name]["key"] = false; }

	$creatures = array();
	$number = $level["number"];
	switch ($number) {
		case 1:
			$pos = free_position($level);
			$creatures[] = creature_from_template($names_ok[0], $pos);
			$pos = free_position($level);
			$creatures[] = creature_from_template($names_ko[0], $pos);
		break;

		case 2:
			$pos = free_position($level);
			$creatures[] = creature_from_template($names_ok[0], $pos);
			$pos = free_position($level);
			$creatures[] = creature_from_template($names_ok[1], $pos);

			$pos = free_position($level);
			$creatures[] = creature_from_template($names_ko[0], $pos);
			$pos = free_position($level);
			$creatures[] = creature_from_template($names_ko[0], $pos);
		break;

		default:
			for ($i=0;$i<3;$i++) {
				$pos = free_position($level);
				$idx = array_rand($names_ok);
				$creatures[] = creature_from_template($names_ok[$idx], $pos);
			}
			for ($i=0;$i<$number;$i++) {
				$pos = free_position($level);
				$idx = array_rand($names_ko);
				$creatures[] = creature_from_template($names_ko[$idx], $pos);
			}
		break;
	}

	foreach ($creatures as &$creature) {
		if (mt_rand(0, 1)) {  // has gold
			$level["gold"][] = array(
				"id" => id(),
				"position" => $creature["position"]
			);
		}
	}

/*
	$test = array("goblin", "orc", "bat", "kobold");
	foreach ($test as $name) {
		$pos = free_position($level);
		$creatures[] = creature_from_template($name, $pos);
	}
*/

	$level["creatures"] = $creatures;
}

function generate_gold(&$level) {
	$number = $level["number"];
	$gold = array();

	switch ($number) {
		case 1:
			$x = $level["size"][0]-1;
			$y = $level["size"][1]-1;
			$pos = free_position($level, array($x, $y));
			$gold[] = array(
				"id" => id(),
				"position" => $pos
			);
		break;

		default:
			for ($i=0;$i<$number;$i++) {
				$pos = free_position($level);
				$gold[] = array(
					"id" => id(),
					"position" => $pos
				);
			}
		break;
	}

	$level["gold"] = $gold;
}

function generate_pc(&$level) {
	$x = floor($level["size"][0]/2);
	$y = floor($level["size"][1]/2);
	$level["pc"] = free_position($level, array($x, $y));
}

function generate_weapon(&$level) {
	$letter = (mt_rand(0, 1) ? "(" : ")");
	$names = array("knife", "axe", "sword", "saber", "mace", "dagger", "hammer");
	$color = array(
		"h"=>array(0, 360),
		"s"=>array(0, 10),
		"v"=>array(70, 100)
	);

	$level["weapon"] = array(
		"id" => id(),
		"letter" => $letter,
		"name" => $names[array_rand($names)],
		"color" => expand_color($color),
		"position" => free_position($level)
	);
}

function generate_walls(&$level) {
	$walls = array();
	$size = $level["size"];

	$walls[] = array(
		"id" => id(),
		"position" => free_position($level, array(0, 0))
	);
	$walls[] = array(
		"id" => id(),
		"position" => free_position($level, array($size[0]-1, 0))
	);
	$walls[] = array(
		"id" => id(),
		"position" => free_position($level, array(0, $size[1]-1))
	);
	$walls[] = array(
		"id" => id(),
		"position" => free_position($level, array($size[0]-1, $size[1]-1))
	);

	$count = $level["number"];
	for ($i=0;$i<$count;$i++) {
		$walls[] = array(
			"id" => id(),
			"position" => free_position($level)
		);
	}

	$level["walls"] = $walls;
}

function generate_intro(&$level) {
	$str = "";
	switch ($level["number"]) {
		case 1:
			$str = "<p>The evil General Sibling Combinator has locked you in his underground prison. You are now located in the first dungeon level.</p>
					<p>To escape, you need to <strong>find a key</strong>. Keys are always held by monsters, so to get one, you will need to fight.</p>
					<p>But before you can find, you need to <strong>pick up a weapon</strong>. Furtunately, these cells always contain some type of weapon somewhere...</p>
					<p>Please click/touch the navigation controls to move around, pick stuff and attack monsters. (Remember: this is a no-JavaScript game, so the interaction is severly limited.)</p>";
		break;

		case 2:
			$hp = $level["hp"];
			$str = "<p>You managed to get to the second level. You will need to get two keys to continue.</p>
					<p>You might have already noticed that <strong>not all monsters drop keys</strong>; some can damage you instead. You only have {$hp} lives, so take care.</p>";
		break;

		case 3:
			$str = "<p>Further levels will always require three keys to escape.</p>
					<p>The General Sibling Combinator has locked more enemy creatures in these cells, so be careful when deciding what to fight.</p>";
		break;

		case 4:
			$str = "<p>We are currently running out of tutorial topics. You seem to be completely familiar with all aspects of this game.</p>
					<p>If you are feeling adventurous, try to pick as many <strong>gold pieces</strong> as you can when roaming through the level.</p>";
		break;

		case 5:
			$str = "<p>This could be a good time for a <strong>little snack</strong>, what do you think? Do yourself a favor and prepare a cup of tea or coffee; maybe with a cookie or an apple or a small piece of lutefisk?</p>";
		break;

		case 6:
			$str = "<p>You might be interested in the fact that this game can be actually won, so the number of levels created by the General Sibling Combinator is finite.</p>
					<p>The end is near!</p>";
		break;

		case 7:
			$str = "<p>Fun fact: this page contains more than 300 procedurally-generated CSS rulesets.</p>";
		break;
	}
	$level["intro"] = $str;
}

function generate_level($number, $seed) {
	$number = min($number, LEVELS);
	mt_srand($seed);
	init_creatures();

	mt_srand($seed + $number);
	$level = array();

	$level["used"] = array();
	$level["number"] = $number;
	$level["hp"] = 3;
	$level["keys"] = min($number, 3);
	$level["size"] = array(7 + 2*$number, 5 + floor($number/2));

	generate_intro($level);
	generate_pc($level);
	generate_walls($level);
	generate_weapon($level);
	generate_gold($level);
	generate_creatures($level);

	$url_reload = "?seed={$seed}&amp;level={$number}";
	$level["gameover"] = "<p><strong>Game over!</strong> You have lost all your health. Try to fight those creatures that leave keys in order to finish the level.</p>
							<p>You can try <a href='{$url_reload}'>reloading</a> this level or starting a <a href='.'>new game</a>.</p>";

	$next = $number+1;
	if ($next > LEVELS) {
		$level["victory"] = "<p><strong>Congratulations!</strong> You managed to escape all levels designed by the mad General Sibling Combinator and won the game! This is a heroic achievement indeed. We hope you liked the game!</p>
							<p>Your quest is over now, but if you seek additional thrill and adventure, you are free to study this game's <a href='http://github.com/ondras/7drl-2019'>source code</a>.</p>";
	} else {
		$url_next = "?seed={$seed}&amp;level={$next}";
		$level["victory"] = "<p>Good job! You managed to retrieve all keys, gathering <span class='gold-count'></span>&nbsp;gold in the process. Please continue to the <a href='{$url_next}'>next level</a> immediately.";
	}

	return $level;
}

?>
