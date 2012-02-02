<?php

require_once 'data.methods.php';

// caniuse/[ features ].[ format ][ ? [ actions ] ]

$features  = explode(' ', first_set(@$_GET['features'], '')); unset($_GET['features']);
$method   = first_set(@$_GET['method'], 'display'); unset($_GET['method']);
$callback = first_set(@$_GET['callback']); unset($_GET['callback']);
$format   = first_set(@$_GET['format'], 'js'); unset($_GET['format']);
$actions  = $_GET;
$mime     = mime_type($callback ? 'js' : $format);

header('Content-Type: ' . $mime);

$data_name  = 'data.json';
$data_json  = file_get_contents($data_name);
$data       = json_decode($data_json, true);

$data_agents   =& $data['agents'];
$data_features =& $data['data'];
$data_custom   = array( 'required' => array() );

// loop through the features
foreach ($features as $index => $feature_name) {
	// if feature does exist in data
	if (isset($data_features[$feature_name])) {
		// set feature statistics and requirements
		$feature_statistics    =& $data_features[$feature_name];
		$feature_requirements  =& $feature_statistics['required'];

		// add to custom data these feature statistics
		$data_custom[$feature_name] = $feature_statistics;

		// loop through the feature requirements
		foreach ($feature_requirements as $browser_id => &$browser_version) {
			// if custom data does not have a required section then add it
			if (empty($data_custom['required'])) {
				$data_custom['required'] = $feature_requirements;
			}

			// if custom data already has a required section for this browser
			if (isset($data_array_new['required'][$browser_id])) {
				// compare browser versions between the custom data and the feature requirements and use the newest
				$data_custom['required'][$browser_id] = max(floatval($data_custom['required'][$browser_id]), floatval($browser_version));
			}
		}
	}
	// if feature does not exist in data
	else {
		// remove feature from features
		unset($features[$index]);
	}
}

/*
 * Output
 */

if ($format === 'html') {
	$data_html_custom  = html_encode($data_custom, $data_agents, $features, $actions);

	// if method defines a callback
	if (isset($callback)) {
		// exit as html wrapped in a callback
		exit($callback . '("' . json_escape_html($data_html_custom) . '")');
	}
	// if method does not define a callback
	else {
		// exit as html
		exit($data_html_custom);
	}
}

// if format is xml
if ($format === 'xml') {
	$data_xml_custom = xml_encode($data_custom);

	// exit as xml
	exit($data_xml_custom);
}

// if format is javascript or json
if ($format === 'js' || $format === 'json') {
	$data_json_custom = json_encode($data_custom);

	// if format is javascript
	if ($format === 'js') {
		// if method defines a callback
		if (isset($callback)) {
		// exit as javascript wrapped in a callback
			exit($callback . '(' . $data_json_custom . ')');
		}
		// if method does not define a callback
		else {
			exit($data_json_custom);
		}
	}

	// if format is json
	if ($type === 'json') {
		exit($jsonNewText);
	}
}

?>