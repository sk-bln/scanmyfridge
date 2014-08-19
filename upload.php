
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

$queueURL = 'https://sqs.eu-west-1.amazonaws.com/812162077765/ScanmyfridgeQueue';

use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;

$msg='';
$currentuser = 'skrause';
$bucket = 'scanmyfridge-upload';
$awsregion = 'eu-west-1';
try
{
	$s3client = S3Client::factory(array('region' => $awsregion));
	$sqsclient = SqsClient::factory(array('region' => $awsregion));
}
catch (Exception $e)
{
	echo 'Exception: ' . $e;
	exit();
}


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
			$actual_image_name = rand() . time().".".$ext;
			$exmsg = "";
			$result = "";
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
				$msg = "<b>S3 Upload Successful.</b><br/>"; 
				$s3file='http://'.$bucket.'.s3.amazonaws.com/'.$actual_image_name;
				echo '<b>S3 File URL:</b>'.$s3file . '<br/>';
				$exmsg = "";
				try
				{
					$message = array(
						'User' => $currentuser,
						'S3File' => $s3file);

					$result = $sqsclient->sendMessage(array(
						'QueueUrl' => $queueURL,
						'MessageBody' => json_encode($message)
					));					

				}
				catch (Exception $e)
				{
					$exmsg = $e;
					echo '<b>SQS Error:</b> ' . $exmsg . '<br/>';

				}

			}
			else
				$msg = "<b>S3 Upload Fail:</b>" . $exmsg . '<br/>';
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

