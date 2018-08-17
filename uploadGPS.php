<?php
//error_reporting(E_ALL); 
//ini_set('display_errors', 1);
ini_set('post_max_size', '6M');
ini_set('upload_max_filesize', '6M');

        if(isset($_FILES["fileToUpload"])){
            $file = $_FILES['fileToUpload'];

            $fileName = $_FILES["fileToUpload"]["name"];
            $fileTmpName = $_FILES["fileToUpload"]["tmp_name"];
            $fileSize = $_FILES["fileToUpload"]["size"];
            $fileError = $_FILES["fileToUpload"]["error"];
            $fileType = $_FILES["fileToUpload"]["type"];

            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));
			$fn = time();
			$fileName = $fn.'.'.$fileActualExt;			
            $allowed = array('jpg', 'jpeg', 'png');

            if(in_array($fileActualExt, $allowed)){
                //Image code
                if($fileError === 0){
                    if($fileSize < 4500000){

                        $fileDestination = 'upload/'.$fileName;
						$fileDestinationThumb = 'upload/t_'.$fileName;
						$fileDestinationSize = 'upload/s_'.$fileName;


                        move_uploaded_file($fileTmpName, $fileDestination);
						make_thumb($fileDestination,$fileDestinationThumb,30);
						make_thumb($fileDestination,$fileDestinationSize,600);
						//header("Location: server.php?uploadsuccess");
                        //Display image here <----------
						//header("Location:cam.php?filename=$fileDestination");

						
						?>
						<a href=cam.php><img src="https://kopaonik-smestaj.net/gps/upload/s_<?php echo $fileName;?>"></a>
						<?php
						

                    }else{
                        echo "Your file is too big!";
                    }
                }else{
                    echo "There was an error while uploading your file!";
                }
            }else{

                if(isset($_FILES["fileToUpload"])){
                    $file = $_FILES["fileToUpload"]["name"];
                    echo "File: ".$file;
                }
            }

        }

    ?>
	
	
	<?php
 // Only process POST reqeusts.
 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the form fields and remove whitespace.
        $Lat = strip_tags(trim($_POST["Lat"]));
		$Lon = strip_tags(trim($_POST["Lon"]));
		$Acc = strip_tags(trim($_POST["Acc"]));
		$Dsc = strip_tags(trim($_POST["Description"]));
		$UA = $_SERVER['HTTP_USER_AGENT'];	

        // Set the recipient email address.
        // FIXME: Update this to your desired email address.
        $recipient = "sinisa@jovanovic.co.za";
        $subject = "geoLoc $Lat-$Lon";
		
        $email_content = "Lat: $Lat\n";
        $email_content .= "Lon: $Lon\n\n";		
		$email_content .= "https://kopaonik-smestaj.net/gps/upload/s_".$fileName;
		
        $email_headers = "From: GeoLoc <info@kopaonik-apartmani.com>";

        // Send the email.
        if (mail($recipient, $subject, $email_content, $email_headers)) {
            // Set a 200 (okay) response code.
             http_response_code(200);            
        } else {
            // Set a 500 (internal server error) response code.
            http_response_code(500);
            //echo "Oops! Something went wrong and we couldn't send your message.";
        }
		
		//DB
		$link = mysql_connect('mysql', 'sinisa_g30l0c', 'sinisag30l0c');
		mysql_select_db('sinisa_geoLoc', $link) or die('Could not select database.');	
		if (!$link) {
			die('Could not connect: ' . mysql_error());
		}
		
		$sql = "INSERT INTO geoLoc (Lat, Lon, Acc, UA, Type, Description) VALUES ('".$Lat."', '".$Lon."', '".$Acc."', '".$UA."', '".$fileName."', '".$Dsc."');";
		
		$result = mysql_query($sql);
		if (!$result) {
			die("lid query: " . mysql_error());
		}
		
		mysql_close($link);

    } else {
        // Not a POST request, set a 403 (forbidden) response code.
        http_response_code(403);
        echo "There was a problem with your submission, please try again.";
    }
	sleep(10);
	header('Location: cam.php');
	exit;
?>
<?php
	function make_thumb($src, $dest, $desired_width) {
		/* read the source image */
		$source_image = imagecreatefromjpeg($src);
		$width = imagesx($source_image);
		$height = imagesy($source_image);

		if ($width>$height){
			$source_image = imagerotate($source_image, 270, 0);
			$w = $width; $width = $height; $height = $w;			
		}
		
		/* find the "desired height" of this thumbnail, relative to the desired width  */
		$desired_height = floor($height * ($desired_width / $width));
		
		/* create a new, "virtual" image */
		$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
		
		/* copy source image at a resized size */
		imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
		
		/* create the physical thumbnail image to its destination */
		imagejpeg($virtual_image, $dest);
	}
?>