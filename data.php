<?php

header('Content-Type: text/javascript');

$features = isset($_GET['features']) ? explode(' ', $_GET['features']) : array();

if (!$features)
{
	exit();
}

$jsonName = 'data.json';

$jsonText = file_get_contents($jsonName);

$jsonData = json_decode($jsonText, true);

$jsonDataData = $jsonData['data'];

$jsonDataDataCustom = array();

foreach ($features as $featureName)
{
	if (isset($jsonDataData[$featureName]))
	{
		$featureData = $jsonDataData[$featureName];
		$featureStats = $featureData['stats'];

		$jsonDataDataCustom[$featureName] = $featureData;
	}
}

print_r(json_encode($jsonDataDataCustom));

?>