<?php

function input($type, $id, $attrs = array()) {
	echo "<input autocomplete='off' type='{$type}' id='{$id}' ";
	foreach ($attrs as $k=>$v) { echo "{$k}='{$v}' "; }
	echo "/>";
}

function wall($len) {
	echo "<span class='wall' title='Solid wall'>";
	for ($i=0;$i<$len;$i++) { echo "#"; }
	echo "</span>";
}

function serialize_state(&$level) {
	input("radio", "intro");

	foreach ($level["creatures"] as &$creature) {
		$id = $creature["id"];
		$key = ($creature["key"] ? "key" : "");
		input("radio", "cs{$id}", array("class" => "cs {$key}"));
	}

	foreach ($level["gold"] as &$gold) {
		$id = $gold["id"];
		input("radio", "gs{$id}");
	}

	$size = $level["size"];
	for ($i=0;$i<$size[0];$i++) {
		$attrs = array("name"=>"x");
		if ($i == $level["pc"][0]) { $attrs["checked"] = "checked"; }
		input("radio", "x{$i}", $attrs);
	}
	for ($i=0;$i<$size[1];$i++) {
		$attrs = array("name"=>"y");
		if ($i == $level["pc"][1]) { $attrs["checked"] = "checked"; }
		input("radio", "y{$i}", $attrs);
	}
}

function serialize_intro(&$level) {
	echo "<header>";
	echo $level["intro"];
	echo "<label for='intro'>Play</label>";
	echo "</header>";
}

function serialize_map(&$level) {
	$size = $level["size"];
	echo "<section id='map'>";

	wall($size[0]+2);
	echo "<br/>";
	
	for ($y=0; $y<$size[1]; $y++) {
		wall(1);
		echo "<span class='cell'>";
		for ($x=0; $x<$size[0]; $x++) { echo "."; }
		echo "</span>";
		wall(1);
		echo "<br/>";
	}

	wall($size[0]+2);

	foreach ($level["gold"] as &$gold) {
		$id = $gold["id"];
		echo "<span class='g' id='g{$id}'>$</span>";
	}

	foreach ($level["creatures"] as &$creature) {
		$id = $creature["id"];
		$name = $creature["name"];
		echo "<span class='c' id='c{$id}' title='{$name}'>{$creature['letter']}</span>";
	}

	echo "<span id='pc' title='You!'>@</span>";
	echo "</section>";
}

function serialize_inv(&$level) {

	echo "<section id='inv'>";
	echo "<div>Health: ";
	$hp = $level["hp"];
	for ($i=0;$i<$hp;$i++) { echo "<span class='hp'>♥</span>"; }
	echo "</div><div>";

	echo "Keys: ";
	$keys = $level["keys"];
	for ($i=0;$i<$keys;$i++) { echo "<span class='key'>⚷♂♀</span>"; }
	echo "</div><div>";
	echo "Gold: ";
	echo "<span class='gold-count'></span></div>";
	echo "</section>";
}

function serialize_nav(&$level) {
	echo "<section id='nav'>";
	$size = $level["size"];
	$label = "Move";

	for ($i=0;$i<$size[0];$i++) {
		$prev = $i-1;
		$next = $i+1;
		if ($prev >= 0) { echo "<label for='x{$prev}' class='left'>{$label}</label>"; }
		if ($next < $size[0]) { echo "<label for='x{$next}' class='right'>{$label}</label>"; }
	}

	for ($i=0;$i<$size[1];$i++) {
		$prev = $i-1;
		$next = $i+1;
		if ($prev >= 0) { echo "<label for='y{$prev}' class='up'>{$label}</label>"; }
		if ($next < $size[1]) { echo "<label for='y{$next}' class='down'>{$label}</label>"; }
	}

	foreach ($level["creatures"] as &$creature) {
		$id = $creature["id"];
		$name = $creature["name"];
		$label = "{$name}<br/>Attack!";
		echo "<label for='cs{$id}' class='left'>{$label}</label>";
		echo "<label for='cs{$id}' class='right'>{$label}</label>";
		echo "<label for='cs{$id}' class='up'>{$label}</label>";
		echo "<label for='cs{$id}' class='down'>{$label}</label>";
	}

	foreach ($level["gold"] as &$gold) {
		$id = $gold["id"];
		echo "<label for='gs{$id}' class='pick'>Pick up gold</label>";
	}

	echo "<div id='victory'>" . $level["victory"] . "</div>";
	echo "<div id='gameover'>" . $level["gameover"] . "</div>";
	echo "</section>";
}

function serialize_level_style(&$level) {
	$size = $level["size"];
	for ($i=0;$i<$size[0];$i++) {
		$x = $i+1;
		$prev = $i-1;
		$next = $i+1;
		echo "#x{$i}:checked ~ #map #pc { left: {$x}ch }";  // pc horizontal
		echo "#x{$i}:checked ~ #nav .left[for=x{$prev}] { display: initial }";  // left buttons
		echo "#x{$i}:checked ~ #nav .right[for=x{$next}] { display: initial }";  // right buttons
	}
	for ($i=0;$i<$size[1];$i++) {
		$y = $i+1;
		$prev = $i-1;
		$next = $i+1;
		echo "#y{$i}:checked ~ #map #pc { top: {$y}em }";   // pc vertical
		echo "#y{$i}:checked ~ #nav .up[for=y{$prev}] { display: initial }";  // up buttons
		echo "#y{$i}:checked ~ #nav .down[for=y{$next}] { display: initial }";  // down buttons
	}
}

function serialize_gold_style(&$level) {
	foreach ($level["gold"] as &$gold) {
		$id = $gold["id"];
		echo "#gs{$id}:checked ~ #map #g{$id} { display: none }"; // picked gold not visible
		echo "#gs{$id}:checked { counter-increment: gold }"; // gold counter

		$pos = $gold["position"];
		$x = $pos[0];
		$y = $pos[1];
		echo "#gs{$id}:not(:checked) ~ #x{$x}:checked ~ #y{$y}:checked ~ #nav [for=gs{$id}] { display: initial }"; // gold pick

		$x = $pos[0]+1;
		$y = $pos[1]+1;
		echo "#g{$id} { left: {$x}ch; top: {$y}em; }"; // gold position
	}
}

function serialize_creature_style(&$level) {
	foreach ($level["creatures"] as &$creature) {
		$id = $creature["id"];
		$pos = $creature["position"];
		$alive = "#cs{$id}:not(:checked)";
		$dead = "#cs{$id}:checked";

		$x = $pos[0]+1; $y = $pos[1]+1;
		echo "#c{$id} { left: {$x}ch; top: {$y}em; }"; // creature position
		echo "{$dead} ~ #map #c{$id} { display: none }"; // dead creatures not visible

		$diffs = array(
			"right" => array(-1, 0),
			"left" => array(1, 0),
			"down" => array(0, -1),
			"up" => array(0, 1),
		);

		foreach ($diffs as $dir => $delta) {
			$x = $pos[0]+$delta[0];
			$y = $pos[1]+$delta[1];

			// cannot move here
			echo "{$alive} ~ #x{$x}:checked ~ #y{$y}:checked ~ #nav .{$dir} { display: none }"; 

			// can attack here
			echo "{$alive} ~ #x{$x}:checked ~ #y{$y}:checked ~ #nav [for=cs{$id}].{$dir} { display: initial }"; 
		}
	}

	$key_ok = ".cs.key:checked";
	$key_ko = ".cs:not(.key):checked";
	$keys = $level["keys"];
	$hp = $level["hp"];

	for ($i=0;$i<$keys;$i++) {  // key fade in
		$num = $i+1;
		for ($j=0;$j<=$i;$j++) { echo "{$key_ok} ~ "; }
		echo "#inv .key:nth-child({$num}) { animation: key-add 800ms both; }";
	}

	for ($i=0;$i<$hp;$i++) {  // hp fade out
		$num = $hp-$i;
		for ($j=0;$j<=$i;$j++) { echo "{$key_ko} ~ "; }
		echo "#inv .hp:nth-child({$num}) { animation: hp-remove 800ms both; }";
	}

	// victory
	for ($i=0;$i<$keys;$i++) { echo "{$key_ok} ~ "; }  // hide labels
	echo "#nav label { display: none !important }";
	for ($i=0;$i<$keys;$i++) { echo "{$key_ok} ~ "; }  // show victory
	echo "#nav #victory { display: initial }";

	// gameover
	for ($i=0;$i<$hp;$i++) {  echo "{$key_ko} ~ "; }
	echo "#nav label { display: none !important }";  // hide labels
	for ($i=0;$i<$hp;$i++) {  echo "{$key_ko} ~ "; }  // show gameover
	echo "#nav #gameover { display: initial }";
}

function serialize_style(&$level) {
	echo "<style>";

	serialize_level_style($level);
	serialize_gold_style($level);
	serialize_creature_style($level);

	echo "</style>";
}

function serialize_level(&$level) {
	echo "<section id='game'>";
	serialize_state($level);
	serialize_intro($level);
	serialize_map($level);
	serialize_nav($level);
	serialize_inv($level);
	echo "</section>";
	serialize_style($level);
}

?>
