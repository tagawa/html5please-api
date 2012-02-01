<?php

$featuresNames = array();
$featuresBrowsers = array();

foreach ($features as &$featureName) {
	$feature = $jsonArrNew[$featureName];
	$featureTitle = $feature['title'];

	array_push($featuresNames, $featureTitle);
}

foreach ($jsonArrNew['required'] as $browserName => &$browserVersion) {
	$browserHtml = $jsonArrAgs[$browserName]['browser'] . ' ' . $browserVersion;
	$browserHtml = '<a href="' . $jsonArrAgs[$browserName]['url'] . '" rel="external" target="_blank">' . $browserHtml . '</a>';

	array_push($featuresBrowsers, $browserHtml);
}

$featuresNamesHtml = implode(' &amp; ', $featuresNames);
$featuresBrowsersHtml = implode(' &amp; ', $featuresBrowsers);

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Can I use <?php print($featuresNamesHtml); ?>?</title>
	</head>
	<body>
		<h1>Can I use <?php print($featuresNamesHtml); ?>?</h1>
		<p>
			Yes, in <?php print($featuresBrowsersHtml); ?>.
		</p>
	</body>
</html>