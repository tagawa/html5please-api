<?php

$featuresNames = array();
$featuresBrowsers = array();

foreach ($features as &$featureName) {
	$feature = $jsonArrNew[$featureName];
	$featureTitle = $feature['title'];

	array_push($featuresNames, $featureTitle);
}

foreach ($jsonArrNew['required'] as $browserId => &$browserVersion) {
	$browserName = $jsonArrAgs[$browserId]['browser'];
	$browserUrl  = $jsonArrAgs[$browserId]['url'];
	$browserFull = $browserName . ' ' . $browserVersion;

	$browserIcon = '<img alt="' . $browserName . '" src="i/' . $browserId . '-128.png">';
	$browserHtml = '<a href="' . $browserUrl . '" rel="external" target="_blank">' . $browserIcon . '<br><span>' . $browserFull . '</span></a>';

	array_push($featuresBrowsers, $browserHtml);
}

$featuresNamesHtml = implode(' &amp; ', $featuresNames);
$featuresBrowsersHtml = implode('', $featuresBrowsers);

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Which browsers can use <?php print($featuresNamesHtml); ?>?</title>
		<style>
		* { vertical-align: top; }
		html { background: #444; color: #EEE; font: 100%/1 sans-serif; }
		body { text-align: center; }
		h1 { font-size: 2em; margin: 1em; }
		a { color: #CB0; display: inline-block; margin: 1em; text-align: center; text-decoration: none; -webkit-transition: all 200ms ease-out; -webkit-transform: scale(0.9); width: 132px; white-space: nowrap; }
		a:focus, a:hover { color: #CF0; -webkit-transform: scale(0.9999999); }
		a img { border: 0; height: 128px; margin: 0 0 0.5em; width: 128px; }
		a span { clear: both; }
		</style>
	</head>
	<body>
		<h1><?php print($featuresNamesHtml); ?></h1>
		<p>
			<?php print($featuresBrowsersHtml); ?>
		</p>
	</body>
</html>