<?php
/*
Params
    &filePath: Path where the calendar file will be saved, defaults to assets/events directory (define path like /example/)
    &fileName: defaults to pagetitle
    &tpl: name of chunk to use. Defaults to eventCalendar
    &summary: to add a summary of the event
    &startDate: start date of the event, format like 2015-05-14 15:53:00 (default output of date TV)
    &endDate: end date of the event, format like 2015-05-14 15:53:00 (default output of date TV)
    &address: comma delimited list of the addres as street,housenumber,zipcode,city,country
    &link: add link to the event
    &attachment: add an attachment to the event
	&coordinates: latitude,longitude
	&geocode: 0 defaults to 1
	
	use the placeholder "calendarLink" to output the download link to the calendar file
*/

$eventsDir 				= $filePath ?  $_SERVER['DOCUMENT_ROOT'] . $filePath : $modx->getOption('assets_path').'events/';
$tpl                    = $tpl ? $tpl : 'eventCalendar';
$eventID                = $modx->resource->get('id');
$eventName 				= $modx->resource->get('longtitle') ? $modx->resource->get('longtitle') : $modx->resource->get('pagetitle');
$eventSummary 			= wordwrap($summary, 50, "\n ", true);

$eventStartDate         = strtotime($startDate);
$eventEndDate           = strtotime($endDate);

$prodid 				= mt_rand(1000000000, 9999999999) . $eventID;

$addressArray           = explode(",", $address);
$street                 = trim($addressArray[0]);
$housenumber            = trim($addressArray[1]);
$zipcode                = trim($addressArray[2]);
$city                   = trim($addressArray[3]);
$country                = trim($addressArray[4]);

$eventLocation 			= $street . ' ' . $housenumber . ',' . $zipcode . ' ' . $city . ';' . $country;

$geocode = ($geocode == 0) ? 0 : 1;
//check if a file with this name already exists
$file = $fileName ? $fileName . ".ics" : $modx->resource->get('pagetitle') . ".ics";
$eventsFile = fopen($eventsDir . $file, "w");

if(!$coordinates && $geocode == 1){

	$reverseGeocodeUrl = strip_tags('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($eventLocation));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $reverseGeocodeUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = json_decode(curl_exec($ch), true);
	
	if ($response['status'] != 'OK') {
		//failed
		continue;
	}

	$geoCodeResults = array(
		'street' 		=> $response['results'][0]['address_components'][1]['long_name'],
		'housenumber'	=> $response['results'][0]['address_components'][0]['long_name'],
		'zipcode'		=> $response['results'][0]['address_components'][6]['long_name'],
		'city'			=> $response['results'][0]['address_components'][2]['long_name'],
		'state'			=> $response['results'][0]['address_components'][4]['long_name'],
		'country'		=> $response['results'][0]['address_components'][5]['long_name'],
		'latitude'		=> str_replace(",", ".", $response['results'][0]['geometry']['location']['lat']),
		'longitude'		=> str_replace(",",".", $response['results'][0]['geometry']['location']['lng'])
	);
	
	$coordinates        = $geoCodeResults['latitude'].','.$geoCodeResults['longitude'];
}

$eventLocation 			= str_replace(",", "\\n", $eventLocation);
$eventLocation 			= str_replace(";", "\\n", $eventLocation);

function dateToCal($timestamp) {
  return date('Ymd\THis\Z', $timestamp);
}

function escapeString($string) {
  return preg_replace('/([\,;])/','\\\$1', $string);
}


if($attachment){
	$getAttachment	= file_get_contents($attachment);
	$getFileName	= pathinfo($attachment);
	$fileName 		= $getFileName['basename'];

	$eventAttachment .= "ATTACH;ENCODING=BASE64;VALUE=BINARY;X-APPLE-FILENAME=" .  $fileName . ":";
	$b64vcard = base64_encode($getAttachment);
	$b64mline = chunk_split($b64vcard,74,"\n");
	$b64final = preg_replace('/(.+)/', ' $1', $b64mline);

	$eventAttachment .= $b64final;
}
else {
    $eventAttachment = '';
}

if($coordinates && $geocode == 1){
$coordinatesOutput .= "X-APPLE-PROXIMITY:DEPART\n";
    
$coordinatesOutput .= "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;
 X-ADDRESS=" . $eventLocation . ";
 X-APPLE-RADIUS=49.91307587029686;X-TITLE=Zoom 1:geo:
 ".$coordinates;
}

$vCalendar = $modx->getChunk($tpl, array(
                                        'id'                => $prodid,
                                        'eventStartDate'    => dateToCal($eventStartDate),
                                        'eventEndDate'      => dateToCal($eventEndDate),
                                        'eventID'           => $eventID,
                                        'attachment'        => $eventAttachment,
                                        'dtstamp'           => dateToCal(time()),
                                        'location'          => $eventLocation,
                                        'description'       => $eventSummary,
                                        'url'               => $link,
                                        'name'              => $eventName,
                                        'coordinates'       => $coordinatesOutput
                                    )
                                );
                                
if (!file_exists($eventsDir)) {
    mkdir($eventsDir, 0777, true);
}

$eventsFile = fopen($eventsDir . $file, "w");
fwrite($eventsFile, $vCalendar);
fclose($eventsFile);

$modx->toPlaceholder('calendarLink', $filePath . $file,'');
