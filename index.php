<?php
	include "lib/generate.php";
	include "lib/serialize.php";

	$level = (isset($_GET["level"]) ? (int) $_GET["level"] : 0);
	$seed = (isset($_GET["seed"]) ? (int) $_GET["seed"] : 0);
	$name = "The Curse of the General Sibling Selector";
	if ($level) {
		$title = "Level {$level} &ndash; {$name}";
		$level = generate_level($level, $seed);

		include "lib/header.html";

		echo "<body>";
		serialize_level($level);
		echo "<style>";
		echo file_get_contents("app.css");
		echo "</style></body></html>";

	} else {
		$seed = mt_rand();
		$title = $name;
		include "lib/header.html";

		echo "<body>";
		include "lib/welcome.html";
		echo "<style>";
		echo file_get_contents("app.css");
		echo "</style></body></html>";
	}

?>

