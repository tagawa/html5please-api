<?php

$featuresNames = array();
$featuresBrowsers = array();

$featuresLength = count($features);

foreach ($features as $featureName) {
	$feature = $jsonArrNew[$featureName];
	$featureTitle = $feature['title'];
	$featureRequired = $feature['required'];

	array_push($featuresNames, $featureTitle);

	foreach ($featureRequired as $browserName => $browserVersion) {
		array_push($featuresBrowsers, $jsonArrAgs[$browserName]['browser'] . ' v' . $browserVersion);
	}
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
		<p>
			
		</p>
	</body>
</html>