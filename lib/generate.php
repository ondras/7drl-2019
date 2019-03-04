<?php

define("LEVELS", 2);

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

	$level["hp"] = 1;
	$level["keys"] = 1;
	$level["intro"] = generate_intro($number);
	$level["size"] = array(11, 5);
	$level["pc"] = array(floor($level["size"][0]/2), floor($level["size"][1]/2));
	$level["creatures"] = generate_creatures($number);
	$level["items"] = generate_items($number);

	$url_reload = "?seed={$seed}&level={$number}";
	$url_new = ".";
	$level["gameover"] = "<p><strong>Game over!</strong> You have lost all your health. Try to fight those creatures that leave keys in order to finish the level.</p>
							<p>You can try <a href='{$url_reload}'>reloading</a> this level or starting a <a href='{$url_new}'>new game</a>.</p>";

	$next = $number+1;
	if ($next > LEVELS) {
		$level["victory"] = "<p><strong>Congratulations!</strong> You managed to escape all levels designed by the mad General Sibling Combinator and won the game! This is a heroic achievement indeed. We hope you liked the game!</p>
							<p>Your quest is over now, but if you seek additional thrill and adventure, you are free to study this game's <a href='http://github.com/ondras/7drl-2019'>source code</a>.</p>";
	} else {
		$url_next = "?seed={$seed}&level={$next}";
		$level["victory"] = "<p>Good job! You managed to retrieve all keys, gathering <span class='gold'></span> gold in the process. Please continue to the <a href='{$url_next}'>next level</a> immediately.";
	}

	return $level;
}

?>
