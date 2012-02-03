<?php

require_once 'data.methods.php';

// caniuse/[ features ].[ format ][ ? [ actions ] ]

$features = explode(' ', first_set(@$_GET['features'], '')); unset($_GET['features']);
$method   = first_set(@$_GET['method'], 'display'); unset($_GET['method']);
$callback = first_set(@$_GET['callback']); unset($_GET['callback']);
$format   = first_set(@$_GET['format'], 'js'); unset($_GET['format']);
$required = isset($_GET['required']); unset($_GET['required']);
$actions  = $_GET;
$mime     = mime_type($callback ? 'js' : $format);
$ua       = ua_detect();

header('Content-Type: ' . $mime);

$ua_array = ua_detect();

$data_array = get_file_json('data.json');

$filtered_data_array = filter_data($features, $data_array['data']);

$filtered_supported_array = filter_supported($filtered_data_array['supported'], $data_array['agents'], $ua_array);

$return = '';

// if format is html
if ($format === 'html') {
	$return = html_encode($filtered_supported_array);

	if ($callback) {
		$return_json = json_encode(array_merge_recursive($filtered_supported_array, array('html' => json_escape_html($return))));
		$return = $callback . '(' . $return_json . ')';
	}
}

// if format is js or json
if ($format === 'js' || $format === 'json') {
	$return = json_encode($filtered_supported_array);

	if ($callback) {
		$return = $callback . '(' . $return . ')';
	}
}

// if format is xml
if ($format === 'xml') {
	$return = xml_encode($filtered_supported_array);
}

exit($return);

?>