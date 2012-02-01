<?php

require_once 'data.xml.php';

$callback    = isset($_GET['callback']) ? $_GET['callback'] : null;
$type        = isset($_GET['type']) ? $_GET['type'] : 'js';
$features    = isset($_GET['features']) ? explode(' ', $_GET['features']) : array();
$header      = 'Content-Type: ' . ($type === 'js' ? 'text/javascript' : ($type === 'json' ? 'text/json' : ($type === 'html' ? 'text/html' : 'text/xml')));
$jsonName    = 'data.json';
$jsonText    = file_get_contents($jsonName);
$jsonArr     = json_decode($jsonText, true);
$jsonArrAgs  =& $jsonArr['agents'];
$jsonArrData =& $jsonArr['data'];
$jsonArrNew  = array('required' => array());

foreach ($features as &$featureName) {
	if (isset($jsonArrData[$featureName])) {
		$featureData =& $jsonArrData[$featureName];
		$featureReq  =& $featureData['required'];

		$jsonArrNew[$featureName] = $featureData;

		foreach ($featureReq as $browserName => & $browserVersion) {
			if (empty($jsonArrNew['required'])) {
				$jsonArrNew['required'] = $featureReq;
			}

			if (isset($jsonArrNew['required'][$browserName])) {
				$jsonArrNew['required'][$browserName] = max(floatval($jsonArrNew['required'][$browserName]), floatval($browserVersion));
			}
		}
	} else {
		unset($features[$featureName]);
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

if ($type === 'html') {
	include "data.html.php";

	exit();
}

if ($type === 'xml') {
	$jsonNewXml  = generate_valid_xml_from_array($jsonArrNew);

	exit($jsonNewXml);
}

?>