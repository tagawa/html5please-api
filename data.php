<?php

require_once 'data.xml.php';

$callback    = isset($_GET['callback']) ? $_GET['callback'] : null;
$type        = isset($_GET['type']) ? $_GET['type'] : 'js';
$features    = isset($_GET['features']) ? explode(' ', $_GET['features']) : array();
$header      = 'Content-Type: ' . ($type === 'js' ? 'text/javascript' : ($type === 'json' ? 'text/json' : ($type === 'html' ? 'text/html' : 'text/xml')));
$jsonName    = 'data.json';
$jsonText    = file_get_contents($jsonName);
$jsonArr     = json_decode($jsonText, true);
$jsonArrData =& $jsonArr['data'];
$jsonArrNew  = array('caniuse' => true);

foreach ($features as &$featureName) {
	if (isset($jsonArrData[$featureName])) {
		$featureData  =& $jsonArrData[$featureName];

		$jsonArrNew[$featureName] = $featureData;
	}
}

header($header);

if ($type === 'js' || $type === 'json') {
	$jsonNewText = json_encode($jsonArrNew);

	if ($type === 'js') {
		if ($callback) {
			$jsonTextCustom = $callback . '(' . $jsonNewText . ')';
		}

		exit($jsonNewText);
	}

	if ($type === 'json') {
		exit($jsonNewText);
	}
}

if ($type === 'xml') {
	$jsonNewXml  = generate_valid_xml_from_array($jsonArrNew);

	exit($jsonNewXml);
}

?>