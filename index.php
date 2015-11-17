<?php

//Configuration
$taskDescription = getenv('TASK_DESCRIPTION');

//The username and password must match what you enter in your HubSpot workflow webhook
$webhookUsername = getenv('USERNAME');
$webhookPassword = getenv('PASSWORD');

$hubspotApiKey = getenv('HUBSPOT_API_KEY');







if ($_SERVER['PHP_AUTH_USER'] != $webhookUsername || $_SERVER['PHP_AUTH_PW'] != $webhookPassword || $_SERVER['REQUEST_METHOD'] != 'POST'){
	exit;
}


$json = $HTTP_RAW_POST_DATA;

$array = json_decode($json, true);

$contactId = $array['vid'];



//set the company id if it exists
if($array['properties']['associatedcompanyid']['value'] != null){
	$companyId = $array['properties']['associatedcompanyid']['value'];
}
else{
	$companyId = null;
}



//lookup owner id
$url = "https://api.hubapi.com/contacts/v1/contact/vid/$contactId/profile?hapikey=$hubspotApiKey";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$response = json_decode($response, true);


//set the owner id if it exists
if($response['properties']['hubspot_owner_id']['value'] != null){
	$ownerId = $response['properties']['hubspot_owner_id']['value'];
}
else{
	$ownerId = null;
}


$taskPostDataArray = array(
	"engagement" => array(
		"type"=>"TASK",
		"ownerId"=>$ownerId
	),
	"associations"=>array(
		"contactIds"=>array(intval($contactId)),
		"companyIds"=>array(intval($companyId))
	),
	"metadata" => array(
		"body"=>$taskDescription
	)
);

$taskPostDataJson = json_encode($taskPostDataArray);

$url = "https://api.hubapi.com/engagements/v1/engagements?hapikey=$hubspotApiKey";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $taskPostDataJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

?>
