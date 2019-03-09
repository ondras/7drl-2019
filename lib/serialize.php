<?php

/*

Inputs:

#intro = intro state

#cs*, .cs, .key = creature state
#x* = x position
#y* = y position
#gs* = gold state
#ws*, .ws = weapon state

Visuals:

#c*, .c = creature
#w*, .w = weapon
#w* = wall
#g*, .g = gold
.left, .right, .up, .down, .pick = navigation buttons

*/

define("DIFFS", array(
	"right" => array(-1, 0),
	"left" => array(1, 0),
	"down" => array(0, -1),
	"up" => array(0, 1),
));

define("HIDE", "display:none");
define("DISABLE", "opacity:0.5; pointer-events:none;");


function block_movement($condition, $position, $style) {
	foreach (DIFFS as $dir => $delta) {
		$x = $position[0]+$delta[0];
		$y = $position[1]+$delta[1];
		echo "{$condition} #x{$x}:checked ~ #y{$y}:checked ~ #nav .{$dir} { {$style} }"; 
	}
}

function position($id, $position, $attrs = array()) {
	$x = $position[0]+1;
	$y = $position[1]+1;
	echo "#{$id} { left: {$x}ch; top: {$y}em; ";
	foreach ($attrs as $k=>$v) { echo "{$k}: {$v}; "; }
	echo "}";
}

function input($type, $id, $attrs = array()) {
	echo "<input autocomplete='off' type='{$type}' id='{$id}' ";
	foreach ($attrs as $k=>$v) { echo "{$k}='{$v}' "; }
	echo "/>";
}

function wall($len, $id = false) {
	echo "<span ";
	if ($id) { echo "id='{$id}' "; }
	echo "class='wall' title='Solid wall'>";
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

	$weapon = $level["weapon"];
	$id = $weapon["id"];
	input("radio", "ws{$id}", array("class" => "ws"));

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
	echo "<h1>Level " . $level["number"] . "</h1>";
	echo $level["intro"];
	echo "<p><strong><label for='intro'>Let's go!</label></strong></p>";
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

	$weapon = $level["weapon"];
	$id = $weapon["id"];
	echo "<span class='w' id='w{$id}' title='{$weapon['name']}'>{$weapon['letter']}</span>";

	foreach ($level["gold"] as &$gold) {
		$id = $gold["id"];
		echo "<span class='g' id='g{$id}' title='Gold'>$</span>";
	}

	foreach ($level["walls"] as &$wall) {
		$id = $wall["id"];
		wall(1, "w{$id}");
	}

	foreach ($level["creatures"] as &$creature) {
		$id = $creature["id"];
		echo "<span class='c' id='c{$id}' title='{$creature['name']}'>{$creature['letter']}{$creature['corpse']}</span>";
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

	echo "Keys:   ";
	$keys = $level["keys"];
	for ($i=0;$i<$keys;$i++) { echo "<span class='key'>⚷</span>"; }
	echo "</div><div>";

	echo "Gold:   ";
	echo "<span class='gold-count'></span>";
	echo "</div><div>";

	echo "<div>Weapon: ";
	echo "<span class='weapon'></span>";
	echo "</div>";

	echo "</section>";
}

function serialize_nav(&$level) {
	echo "<section id='nav'>";
	$size = $level["size"];
	$label = "Move";

	for ($i=0;$i<$size[0];$i++) {
		$prev = $i-1;
		$next = $i+1;
		echo "<label for='x{$prev}' class='left'>{$label}</label>";
		echo "<label for='x{$next}' class='right'>{$label}</label>";
	}

	for ($i=0;$i<$size[1];$i++) {
		$prev = $i-1;
		$next = $i+1;
		echo "<label for='y{$prev}' class='up'>{$label}</label>";
		echo "<label for='y{$next}' class='down'>{$label}</label>";
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

	$weapon = $level["weapon"];
	$id = $weapon["id"];
	echo "<label for='ws{$id}' class='pick'>Pick up the {$weapon['name']}</label>";

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

	// extreme buttons always disabled
	$disable = DISABLE;
	$x = $size[0];
	$y = $size[1];
	echo "[for=x-1], [for=y-1], [for=x{$x}], [for=y{$y}] { {$disable}; }";

	$pad = $size[0] + 2 + 1;
	echo "#nav { width: calc(100% - {$pad}ch) }";
}

function serialize_gold_style(&$level) {
	foreach ($level["gold"] as &$gold) {
		$id = $gold["id"];
		$hide = HIDE;
		echo "#gs{$id}:checked ~ #map #g{$id} { {$hide} }"; // picked gold not visible
		echo "#gs{$id}:checked { counter-increment: gold }"; // gold counter

		$pos = $gold["position"];
		position("g{$id}", $pos);

		$x = $pos[0];
		$y = $pos[1];
		echo "#gs{$id}:not(:checked) ~ #x{$x}:checked ~ #y{$y}:checked ~ #nav [for=gs{$id}] { display: initial }"; // gold pick
	}
}

function serialize_weapon_style(&$level) {
	$weapon = $level["weapon"];
	$id = $weapon["id"];
	$hide = HIDE;
	echo "#ws{$id}:checked ~ #map #w{$id} { {$hide} }"; // picked weapon not visible
	echo "#ws{$id}:checked ~ #inv .weapon::after { content: '{$weapon['name']}' }"; // picked weapon in inventory

	$pos = $weapon["position"];
	position("w{$id}", $pos);

	$x = $pos[0];
	$y = $pos[1];
	echo "#ws{$id}:not(:checked) ~ #x{$x}:checked ~ #y{$y}:checked ~ #nav [for=ws{$id}] { display: initial }"; // weapon pick
}

function serialize_wall_style(&$level) {
	foreach ($level["walls"] as &$wall) {
		$id = $wall["id"];
		$pos = $wall["position"];
		position("w{$id}", $pos);
		block_movement("", $pos, DISABLE);
	}
}

function serialize_creature_style(&$level) {
	foreach ($level["creatures"] as &$creature) {
		$id = $creature["id"];
		$pos = $creature["position"];
		$color = $creature["color"];
		$alive = "#cs{$id}:not(:checked)";
		$dead = "#cs{$id}:checked";

		echo "{$dead} ~ #map #c{$id} { animation: corpse 5000ms both }"; // dead creatures not visible
		position("c{$id}", $pos, array("color" => $color));
		block_movement("{$alive} ~", $pos, HIDE); // cannot move here

		foreach (DIFFS as $dir => $delta) { // can attack here
			$x = $pos[0]+$delta[0];
			$y = $pos[1]+$delta[1];
			$disable = DISABLE;
			echo "{$alive} ~ #x{$x}:checked ~ #y{$y}:checked ~ #nav [for=cs{$id}].{$dir} { display: initial }"; 
			echo ".ws:not(:checked) ~ #nav [for=cs{$id}].{$dir} { {$disable} }"; 
		}
	}

	$key_ok = ".cs.key:checked";
	$key_ko = ".cs:not(.key):checked";
	$keys = $level["keys"];
	$hp = $level["hp"];

	for ($i=0;$i<$keys;$i++) {  // key fade in
		$num = $i+1;
		for ($j=0;$j<=$i;$j++) { echo "{$key_ok} ~ "; }
		echo "#inv .key:nth-child({$num}) { animation: key-add 800ms both }";
	}

	for ($i=0;$i<$hp;$i++) {  // hp fade out
		$num = $hp-$i;
		for ($j=0;$j<=$i;$j++) { echo "{$key_ko} ~ "; }
		echo "#inv .hp:nth-child({$num}) { animation: hp-remove 800ms both }";
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
	serialize_wall_style($level);
	serialize_weapon_style($level);
	serialize_gold_style($level);
	serialize_creature_style($level);

	echo "</style>";
}

function serialize_level(&$level) {
	echo "<section id='game'>";
	serialize_state($level);
	serialize_intro($level);
	serialize_nav($level);
	serialize_map($level);
	serialize_inv($level);
	echo "</section>";
	serialize_style($level);
}

?>
