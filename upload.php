
<html>
<head><title>Das n&auml;chste gro&szlig;e Startup</title>
<link rel="stylesheet" type="text/css" href="css/zp-base.css">
</head>
<body>
<h1>Upload your fridge photo here</h2>
<h2>Imagine a beautiful upload page here</h2>
<?php
require 'tools/image_check.php';

require 'vendor/autoload.php';

use Aws\S3\S3Client;

$s3client = S3Client::factory();

$msg='';
$currentuser = 'skrause';
$bucket = 'scanmyfridge-upload';

if($_SERVER['REQUEST_METHOD'] == "POST")
{
	$name = $_FILES['file']['name'];
	$size = $_FILES['file']['size'];
	$tmp = $_FILES['file']['tmp_name'];
	$ext = getExtension($name);

	if(strlen($name) > 0)
	{
		// File format validation
		if(in_array($ext,$valid_formats))
		{
			//Rename image name. 
			$actualphp_image_name = rand() . time().".".$ext;
			$exmsg = "";
			try
			{
				$result = $s3client->putObject(array(
    				'Bucket'     => $bucket,
    				'Key'        => $actual_image_name,
    				'SourceFile' => $tmp,
    				'Metadata'   => array(
        				'User' => $currentuser,
        				'Source' => 'webupload'
					)
				));	
			}
			catch (Exception $e)
			{
				$exmsg = $e;
			}
			
			if($result)
			{
				$msg = "S3 Upload Successful."; 
				$s3file='http://'.$bucket.'.s3.amazonaws.com/'.$actual_image_name;
				echo "<img src='$s3file'/>";
				echo 'S3 File URL:'.$s3file;
			}
			else
				$msg = "S3 Upload Fail:" . $exmsg;
		}
		else
			$msg = "Invalid file, please upload image file.";
	}
	else
		$msg = "Please select image file.";
}
?>
<form action="" method='post' enctype="multipart/form-data">
Upload fridge image file here
<input type='file' name='file'/> <input type='submit' value='Upload Image'/>
<?php echo $msg; ?>
</form>

