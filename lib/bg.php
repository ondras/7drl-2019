<?php
	function create_bg() {
		$w = 16;
		$h = 16;
		$cell = 3;
		$image = imagecreatetruecolor($w*$cell, $h*$cell);

		for ($x=0;$x<$w;$x++) {
			for ($y=0;$y<$h;$y++) {
				$b = mt_rand(0, 20);
				$color = imagecolorallocate($image, $b, $b, $b);
				imagefilledrectangle($image, $x*$cell, $y*$cell, ($x+1)*$cell, ($y+1)*$cell, $color);
			}
		}
		ob_start();
		imagepng($image);
		$imagedata = ob_get_contents();
		ob_end_clean();
		$src = "data:image/png;base64," . base64_encode($imagedata);
		echo "body { background-image: url({$src}) }";
	}
?>
