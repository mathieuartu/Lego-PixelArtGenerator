<?php 
$legoSize = 0;

function getLego($imageFile, $pixelFactor){

	//---Analyze Image & draw grid

	//Get data for GD
	if($imageFile->type == "jpg" || $imageFile->type == "jpeg"){
		$im = imagecreatefromjpeg($imageFile->path);
	} else if($imageFile->type == "png"){
		$im = imagecreatefrompng($imageFile->path);
	} else if($imageFile->type == "gif"){
		$im = imagecreatefromgif($imageFile->path);
	}

	//image SIZE - $imgSize
	$imgDims = getimagesize($imageFile->path);
	$imgSize = (object) array('width' => $imgDims[0], 'height' => $imgDims[1]);
	



	//Pixellate the image
	//Create a X% version the image and then spit it back
	//$pixelFactor = 25;



	//Ensure that the image is dividable by the pixel factor
	$imgSize->newHeight = ($imgSize->height - ($imgSize->height % $pixelFactor));
	$imgSize->newWidth = ($imgSize->width - ($imgSize->width % $pixelFactor));

	global $legoSize;
	$legoSize = (object) array('width' => ($imgSize->newWidth/$pixelFactor) *8 , 'height' => ($imgSize->newHeight/$pixelFactor)*8, 'brickWidth' => ($imgSize->newWidth/$pixelFactor), 'brickHeight' => ($imgSize->newHeight/$pixelFactor),  );

	//Then create the pixellated image
	$newImg = imagecreatetruecolor($imgSize->newWidth,$imgSize->newHeight);
	imagecopyresized($newImg,$im,0,0,0,0,round($imgSize->newWidth / $pixelFactor),round($imgSize->newHeight / $pixelFactor),$imgSize->newWidth,$imgSize->newHeight);

	$finalImg = imagecreatetruecolor($imgSize->newWidth,$imgSize->newHeight);
	imagecopyresized($finalImg,$newImg,0,0,0,0,$imgSize->newWidth,$imgSize->newHeight, round($imgSize->newWidth / $pixelFactor),round($imgSize->newHeight / $pixelFactor));



	//Get colors
	include('colors.php');


	//---Loop through the image and analyze/replace colors with real Lego colors
	$a = 0;
	$e = 0;


	while($e <= $imgSize->newHeight){

		$colorAt = imagecolorat ($finalImg , $a , $e );
		$r = ($colorAt >> 16) & 0xFF;
		$g = ($colorAt >> 8) & 0xFF;
		$b = $colorAt & 0xFF;

		$thisRgb = $r.", ".$g.", ".$b;
		
		$colorDiffs = array();

		foreach ($legoColorArray as $key) {
			$rgbSet =  explode(", ", $key);

			$diff = abs( pow($r - $rgbSet[0],2) + pow($g - $rgbSet[1],2) + pow($b - $rgbSet[2],2));

			$colorDiffs[$key] = $diff;
			$colorDiffsRgb[$diff] = $rgbSet[0].", ".$rgbSet[1].", ".$rgbSet[2];

		}

		sort($colorDiffs);
		$minDiff =  min($colorDiffs);
		$newColor =  $colorDiffsRgb[$minDiff];

		$newWidth = ($a + $pixelFactor)-1;
		$newHeight = ($e + $pixelFactor)-1;

		foreach ($legoPalette as $key2 => $value) {

			if($key2 == $newColor){

				imagefilledrectangle($finalImg , $a , $e , $newWidth , $newHeight , $value->rgb);
				break;
			}
		}	

		$a = $a+$pixelFactor;

		if($a == $imgSize->newWidth){
			$a = 0;
			$e = $e + $pixelFactor;
		}
	}

	


	//---Analyse color proximity and create bricks

	$a = 0;
	$e = 0;
	$colorRow = 0;

	echo "\t \t<div class='hidden lego-image lego-pixel-".$pixelFactor."' style='width:".$imgSize->newWidth."px;height:".$imgSize->newHeight."px;'> \n";

		while($e <= $imgSize->newHeight){


			$colorAt = imagecolorat ($finalImg , $a , $e );
    		$r = ($colorAt >> 16) & 0xFF;
			$g = ($colorAt >> 8) & 0xFF;
			$b = $colorAt & 0xFF;

			$thisRgb = $r.", ".$g.", ".$b;
			
			//1 2 3 4 6 8 10 12 16

			if($thisRgb == $oldRgb){
				//If the next color is the same as before

				if($colorRow == 0){
					$startX = ($a - $pixelFactor)+1;
					$startY = $e+1;
				}

				//If it's the end of the line AND there was a row
				if($a == $imgSize->newWidth - $pixelFactor){
					
					$realBrickRow = $colorRow + 2;

					//Have to break down here too
					$brickModulo3 = $realBrickRow % 3;								
					$brick3Number = $realBrickRow / 3;


					//***BRICKS OF 3***
					if($brickModulo3 == 0){

						for($it = 0; $it <= $brick3Number; $it++){


							$newStartX = $startX + (($it *3)*$pixelFactor);
							$newBrickWidth = $pixelFactor *3;
							$brickHeight = $pixelFactor-1;

							echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-3' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$newBrickWidth."px;height:".$brickHeight."px;'></div> \n";
						
						}
					}


					elseif($brickModulo3 == 1){

						for($it = 0; $it <= $brick3Number; $it++){


							$newStartX = $startX + (($it *3)*$pixelFactor);
							$newBrickWidth = $pixelFactor *3;
							$brickHeight = $pixelFactor-1;
							$thisBrickWidth = $brickHeight;

							if($it < $brick3Number - 1){
								echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-3' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$newBrickWidth."px;height:".$brickHeight."px;'></div> \n";
							} else {
								echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-1' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$thisBrickWidth."px;height:".$brickHeight."px;'></div> \n";
							}
							
						}
					}
					elseif($brickModulo3 == 2){

						for($it = 0; $it <= $brick3Number; $it++){

							$newStartX = $startX + (($it *3)*$pixelFactor);
							$newBrickWidth = $pixelFactor *3;
							$brickHeight = $pixelFactor-1;
							$thisBrickWidth = $brickHeight *2 +1;

							if($it < $brick3Number - 1){
								echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-3' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$newBrickWidth."px;height:".$brickHeight."px;'></div> \n";
							} else {
								echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-2' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$thisBrickWidth."px;height:".$brickHeight."px;'></div> \n";
							}
							
						}
					}


				}
				
				$colorRow++;
			} else {
				//If the color has changed

				//But there was a row before
				if($colorRow > 0){


					//If it was a classic brick size
					$realBrickRow = $colorRow + 1;				

					if($realBrickRow == 2 || $realBrickRow == 3 || $realBrickRow == 4 || $realBrickRow == 6 || $realBrickRow == 8 || $realBrickRow == 10 || $realBrickRow == 12 || $realBrickRow == 16 ){
						$endX = $a;

						$brickWidth = ($endX - $startX);
						$brickHeight = $pixelFactor-1;

						echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-".$realBrickRow."' style='background-color:rgba(".$oldRgb.",1);left:".$startX."px;top:".$startY."px;width:".$brickWidth."px;height:".$brickHeight."px;'></div> \n";
					


					//NON STANDARD SIZE
					//If it's a non standard brick size
					} else {

						$brickModulo3 = $realBrickRow % 3;								
						$brick3Number = $realBrickRow / 3;

						$brickModulo4 = $realBrickRow % 4;								
						$brick4Number = $realBrickRow / 4;


						//***BRICKS OF 3***
						if($brickModulo3 == 0){

							for($it = 0; $it <= $brick3Number; $it++){

								$newStartX = $startX + (($it *3)*$pixelFactor);
								$newBrickWidth = $pixelFactor *3;
								$brickHeight = $pixelFactor-1;

								echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-3' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$newBrickWidth."px;height:".$brickHeight."px;'></div> \n";
							
							}
						}


						elseif($brickModulo3 == 1){

							for($it = 0; $it <= $brick3Number; $it++){


								$newStartX = $startX + (($it *3)*$pixelFactor);
								$newBrickWidth = $pixelFactor *3;
								$brickHeight = $pixelFactor-1;
								$thisBrickWidth = $brickHeight;

								if($it < $brick3Number - 1){
									echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-3' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$newBrickWidth."px;height:".$brickHeight."px;'></div> \n";
								} else {
									echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-1' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$thisBrickWidth."px;height:".$brickHeight."px;'></div> \n";
								}
								
							}
						}
						elseif($brickModulo3 == 2){

							for($it = 0; $it <= $brick3Number; $it++){

								$newStartX = $startX + (($it *3)*$pixelFactor);
								$newBrickWidth = $pixelFactor *3;
								$brickHeight = $pixelFactor-1;
								$thisBrickWidth = $brickHeight *2 +1;

								if($it < $brick3Number - 1){
									echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-3' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$newBrickWidth."px;height:".$brickHeight."px;'></div> \n";
								} else {
									echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-2' style='background-color:rgba(".$oldRgb.",1);left:".$newStartX."px;top:".$startY."px;width:".$thisBrickWidth."px;height:".$brickHeight."px;'></div> \n";
								}
								
							}
						}
						
						
					}

				
				} else {
				//If it was a single brick

					if($colorRow == 0 && isset($oldRgb)){



						$startX = ($a - $pixelFactor)+1;
						$startY = $e+1;

						$brickWidth = $pixelFactor-1;
						$brickHeight = $pixelFactor-1;

						echo "\t \t \t <div data-colorname='".$legoPalette->{$oldRgb}->name."' class='brick brick-1' style='z-index:10;position:absolute;background-color:rgba(".$oldRgb.",1);left:".$startX."px;top:".$startY."px;width:".$brickWidth."px;height:".$brickHeight."px;'></div> \n";

					}

				}
				
				$colorRow = 0;
			}

			$oldRgb = $thisRgb;


			$a = $a+$pixelFactor;

			if($a == $imgSize->newWidth){

				$colorRow = 0;
				unset($oldRgb);
				$a = 0;
				$e = $e + $pixelFactor;
			}
		}

	echo "</div> \n";


	imagedestroy($finalImg);
}
?>