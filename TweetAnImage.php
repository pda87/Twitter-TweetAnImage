<?php

ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');

$settings = array(
'oauth_access_token' => "MyTwitterOAuthToken",
'oauth_access_token_secret' => "MyTwitterAccessTokenSecret",
'consumer_key' => "MyTwitterConsumerKey",
'consumer_secret' => "MyTwitterConsumerSecret"
);

//Image path
$path = "https://static3.comicvine.com/uploads/scale_medium/11/113509/4369415-1506033357-54cfd.jpg";

//Need to do the INIT first to get a Media ID
$url = 'https://upload.twitter.com/1.1/media/upload.json';
$requestMethod = 'POST';

$postfields = array(
	'media_type' => "image/jpeg",
	'command' => "INIT",
	'total_bytes' => strlen(file_get_contents($path))
);

$twitter = new TwitterAPIExchange($settings);
$fullResponse = $twitter->buildOauth($url, $requestMethod)
		->setPostfields($postfields)
		->performRequest();

$json = json_decode($fullResponse);

$mediaID = $json->media_id_string;

//Next need to convert the media data
//http://stackoverflow.com/questions/3967515/how-to-convert-image-to-base64-encoding
$type = "jpg";
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

$mediaData = base64_encode($data);

//Need to do the APPEND next to send the data. Sort of
$url = 'https://upload.twitter.com/1.1/media/upload.json';

$requestMethod = 'POST';

$postfields = array(
	'command' => "APPEND",
	'media_id' => "$mediaID",
	'segment_index' => "0",
	'media_data' => "$mediaData"
);

$twitter = new TwitterAPIExchange($settings);
$fullResponse = $twitter->buildOauth($url, $requestMethod)
		->setPostfields($postfields)
		->performRequest();

//Lastly, need to FINALIZE
$url = 'https://upload.twitter.com/1.1/media/upload.json';

$requestMethod = 'POST';

$postfields = array(
	'command' => "FINALIZE",
	'media_id' => "$mediaID",
);

$twitter = new TwitterAPIExchange($settings);
$fullResponse = $twitter->buildOauth($url, $requestMethod)
		->setPostfields($postfields)
		->performRequest();
		
//Finally, Tweet the image-to-base64-encoding
$url = 'https://api.twitter.com/1.1/statuses/update.json';

$output = "My Tweet with an image attached";

$postfields = array(
  'status' => "$output",
  'media_ids' => "$mediaID"
);	

$requestMethod = 'POST';
$twitter = new TwitterAPIExchange($settings);
$fullResponse = $twitter->buildOauth($url, $requestMethod)
				->setPostfields($postfields)
				->performRequest();

echo $fullResponse;
	
?>
