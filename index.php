<?php header("Cache-Control: max-age=1"); ?>

<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="css/global.css" type="text/css">
		<?php 
			if(isset($_GET['print'])){
				echo '<link rel="stylesheet" href="css/print.css" type="text/css">'."\n";
			}
		?>
		<title>Lego® Pixel Art Generator - Create LEGO® mosaics based on your pictures !</title>
		<script src="js/modernizr.js"></script>
	</head>
	<body>
		<header>
			<h1>
				<a href="/">
					<img src="img/logo.png" alt="Lego Pixel Art Generator">
				</a>
			</h1>
		</header>
		
		<div id="global">
			<?php 
				if(isset($_GET['print'])){
					echo '<div class="print-enabled"></div>'."\n";
				}

			?>
			<section id="intro">
				<div class="text">
					<h2>How does it work?</h2>
					<p>LEGO® Pixel Art Generator takes any image and transforms it into a pixel art.</p>
					<small>It only uses real brick colors, and you can print the whole scheme.</small><br>
					<em>You still wonder why this astronaut is so happy? Try it out for yourself!</em>
				</div>
			</section>
			<section id="upload">
				
				<div class="text">
					<h2>Let's get started!</h2>
					<p>Start by uploading your image to our servers.</p>
					<small>Note that the image should be big enough to show best results.</small>
					<em>Jpegs, Pngs and Gifs only are accepted, 10mo maximum.</em>
				</div>
				
			</section>

			<form id="upload-image" method="post" action="" enctype="multipart/form-data">
				<fieldset>
					<p>
						<div class="upload">
							<p>Browse</p>
							<em>or</em>
							<p>drag & drop here</p>
							<input type="hidden" name="MAX_FILE_SIZE" value="10485760">
							<input class="file" type="file" name="uploaded-image" id="uploaded-image">
							<button>OK !</button>
						</div>
						
					</p>
				</fieldset>
			</form>
			<div class="form-bg"></div>

			<?php

				//Connect to mysql
				$login = "dbo516943819";
				$pass = "xngcsek";
				$dbName = "db516943819";
				$svName = "db516943819.db.1and1.com";

				$login2 = "root";
				$pass2 = "root";
				$dbName2 = "legopixel";
				$svName2 = "localhost";
				
				$db = mysql_connect($svName, $login, $pass);
				mysql_select_db($dbName,$db);

				//---LEGO SYSTEM
				include('ressources/getlego.php');

				//---SEED SYSTEM
				if(isset($_GET['art'])){

					echo "<div class='saved'></div> \n";
					

					$urlSeed = $_GET['art'];

					$sql = 'SELECT path,type FROM legopixel WHERE seed ="'.$urlSeed.'" LIMIT 1';
					$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());

					while($data = mysql_fetch_assoc($req)){
						$imageFile = (object) array('name' => $data['seed'], 'path' => $data['path'], 'type' => $data['type']);
						echo "<div class='generated'></div> \n";

						if(isset($_GET['complexity'])){
							getLego($imageFile, $_GET['complexity']);
						} else {
							getLego($imageFile, 25);
						}
						
					}
					

				} else {
					//-----------------------
					//-----------------------
					//-----------------------
					//---IMAGES

					//Security !!!
					$extension_upload = strtolower(  substr(  strrchr($_FILES['uploaded-image']['name'], '.')  ,1)  );                           

					
					$imgDirName = "imgtoprocess/";

					if($_FILES['uploaded-image']['name'] != ""){

						$text = $_FILES['uploaded-image']['name'];
						$nbTimes = substr_count($text, ".") -1;

						$text2 = preg_replace("/\./","-",$text, $nbTimes);

						$imgName = explode(".", $text2);
						$imgNewName = md5($imgName[0]);
						$imgNewPath = $imgDirName.$imgNewName.".".$imgName[1];

						$imageFile = (object) array('name' => $imgNewName, 'path' => $imgNewPath, 'type' => $imgName[1]);

						$result = move_uploaded_file($_FILES['uploaded-image']['tmp_name'],$imgNewPath);

					}			

					//-----------------------
					//-----------------------
					//-----------------------
					//Generate the lego pixel art
					
					if(isset($imageFile)){
						echo "<div class='generated'></div> \n";
						getLego($imageFile, 25);


						$sql = "INSERT INTO legopixel(path, type, seed) VALUES('$imageFile->path','$imageFile->type','$imageFile->name')"; 

						$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());

					} else if(isset($_GET['complexity'])){
						$imageFile = (object) array('name' => $_POST['previous-name'], 'path' => $_POST['previous-path'], 'type' => $_POST['previous-type']);
						echo "<div class='generated' data-size='".$_GET['complexity']."'></div> \n";
						getLego($imageFile, $_GET['complexity']);
					}
				}

				mysql_close();

				
				

			?>
			
			
			<div class="size hidden">
				<p>
					Estimated size : <strong><?php echo ($legoSize->width /10); ?>cm x <?php echo ($legoSize->height /10); ?>cm (W x H) </strong>
				</p>
				<p>
					You will need a  <strong><?php echo ($legoSize->brickWidth); ?> x <?php echo ($legoSize->brickHeight); ?> (W x H) LEGO® Baseplate </strong>
				</p>
			</div>
			
			<div class="control hidden">

				

				<div class="save-reset">
					<button class="save">Save</button>
					<button class="print">Print</button>
					<button class="reset">Reset</button>
				</div>

				<div class="change-size">
						<p>
							<label for="complexity">Change complexity</label>
							<select id="complexity" name="complexity">
								<option value="25">Normal</option>
								<option value="35">Simple</option>
								<option value="15">Complex</option>
								<option value="10">I said COMPLEX !</option>
							</select>
							<button>OK</button>
						</p>
						<input type="hidden" name="previous-name" value="<?php echo $imageFile->name; ?>">
						<input type="hidden" name="previous-path" value="<?php echo $imageFile->path; ?>">
						<input type="hidden" name="previous-type" value="<?php echo $imageFile->type; ?>">
				</div>
			</div>
			
			<div class="brick-helper">
				<p></p>
			</div>
			

			<div class="control-panel">
				<div class="actions">
					<span class="close"></span>
					<span class="remove">Remove</span>
					<span class="add">Add</span>
					<span class="remove-color">Remove all bricks that have this color</span>
					<span class="putback-color">Put back all bricks that have this color</span>
					<span class="change-color">Change color</span>
				</div>
				<div class="colorpicker">
					<svg id="svg" width="280" height="280" version = "1.1"></svg>
					<span class="cancel"></span>
				</div>
			</div>
			
			<br><br>
		</div>
		
		<div class="helper bricks hidden">
			<p>Scroll down to see the bricks you need for your LEGO® Pixel Art!</p>
		</div>

		<div class="helper printer hidden">
			<p>Select File > Print in your browser in order to print your LEGO® Pixel Art!</p>
		</div>

		<div class="pieces hidden">
			<h2>List of all the pieces you'll need : </h2>
			<div class="list">
				
			</div>
		</div>
		
		<div class="tm">
			<strong>LEGO® Pixel Art Generator was assembled brick by brick by <a href="http://www.mathieuartu.net/dev">Mathieu Artu</a></strong>
			<br><strong>If you like LEGO® Pixel Art Generator and want to retribute my work, feel free to make a donation :)</strong>
			<form style="margin:10px 0;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="YUSKCZMANYGRN">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
</form>

			<p>LEGO® is a trademark owned by LEGO® company</p>
			<p>This website is not connected in any way to the LEGO® company</p> 
		</div>

		<script src="js/jquery.js"></script>
		<script src="js/global.js"></script>

		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', 'UA-39085555-2', 'legopixelartgenerator.com');
		  ga('send', 'pageview');

		</script>
	</body>
</html>