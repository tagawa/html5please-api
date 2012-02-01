<?php

$callback = isset($_GET['callback']) ? $_GET['callback'] : null;
$type     = isset($_GET['type']) ? $_GET['type'] : 'js';
$features = isset($_GET['features']) ? explode(' ', $_GET['features']) : array();

$header = 'Content-Type: ' . ($type === 'js' ? 'text/javascript' : ($type === 'json' ? 'text/json' : 'text/html'));

header($header);

if (!$features) {
	exit();
}

$jsonName = 'data.json';

$jsonText = file_get_contents($jsonName);

$jsonData = json_decode($jsonText, true);

$jsonDataData =& $jsonData['data'];

$jsonDataDataCustom = array();

foreach ($features as &$featureName) {
	if (isset($jsonDataData[$featureName])) {
		$featureData  =& $jsonDataData[$featureName];

		$jsonDataDataCustom[$featureName] = $featureData;
	}
}

if ($type === 'js' && $callback) {
	exit($callback . '(' . json_encode($jsonDataDataCustom) . ')');
}

if ($type === 'json') {
	exit(json_encode($jsonDataDataCustom));
}

?>