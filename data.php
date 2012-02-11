<?php

require_once 'data.methods.php';

/* =============================================================================
   Requests
   ========================================================================== */

// Get Format String
$format_string              = first_match('/(html|js|json|php|xml)/', @$_GET['format'], 'js');

// Get Callback String
$callback_string            = @$_GET['callback'];

// Get Requested Features List
$requested_features_array   = explode(' ', @$_GET['features']);

// Get Requested Style String
$requested_style_string     = (
	isset($_GET['texticon']) ? 'texticon' : (
		isset($_GET['text']) ? 'text' : (
			isset($_GET['icon']) ? 'icon' : 'button'
		)
	)
);

// Get Requested CSS Boolean
$requested_style_boolean    = !isset($_GET['nostyle']);

// Get Requested Pretty Boolean
$requested_readable_boolean = isset($_GET['readable']);

// Get Requested Agent Boolean
$requested_agent_boolean    = !isset($_GET['noagent']);

// Get Supported CSS Boolean
$requested_support_boolean  = isset($_GET['supported']);



/* =============================================================================
   Main
   ========================================================================== */

// Get JSON Arrays
$agents_array       = get_cached_array('agents',    $requested_features_array);
$support_array      = get_cached_array('support',   $requested_features_array);
$features_array     = get_cached_array('features',  $requested_features_array);

if ($requested_agent_boolean) {
	// Get User Agent Array
	$user_agent_array   = get_user_agent_array($agents_array);

	// Get Extended Arrays
	$alternatives_array = get_alternatives_array($agents_array, $support_array, $user_agent_array);
	$upgradable_array   = get_upgradable_array($support_array, $user_agent_array);
	$unsupported_array  = get_unsupported_array($support_array, $user_agent_array);

	// Get Supporting Variables
	$supported_string   = @$support_array['agents'][$user_agent_array['id']];
	$supported_boolean  = $supported_string && version_compare($supported_string, $user_agent_array['version']) < 1;
	$error_boolean      = isset($support_array['error']);

	// Set Return Array
	if (!$error_boolean) {
		if ($requested_support_boolean) {
			$return_array   = array('supported' => $supported_boolean);
		} else {
			$return_array   = array('features' => $features_array, 'current' => $user_agent_array, 'supported' => $supported_boolean);
		}
	} else {
		$return_array   = $support_array;
	}

	// Extend Return Array
	if ($supported_boolean && !$requested_support_boolean) {
		$return_array['alternatives'] = $alternatives_array;
	} else if (!$error_boolean && !$requested_support_boolean) {
		if (isset($upgradable_array)) {
			$return_array['upgradable'] = $upgradable_array;
		}

		$return_array['unsupported'] = $unsupported_array;
		$return_array['alternatives'] = $alternatives_array;
	}
} else {
	$return_array   = array('features' => $features_array, 'support' => $support_array);
}


// Ouput
if ($format_string === 'js' && $callback_string) {
	$return_string = json_encode($return_array);

	if ($requested_readable_boolean) {
		$return_string = readable_json($return_string);
	}

	header('Content-Type: text/javascript');

	exit($callback_string . '(' . $return_string . ')');
} elseif ($format_string === 'js') {
	$return_string = json_encode($return_array);

	if ($requested_readable_boolean) {
		$return_string = readable_json($return_string);
	}

	header('Content-Type: text/javascript');

	exit($return_string);
} elseif ($format_string === 'html' && $callback_string) {
	$html = html_encode($return_array, $requested_style_string);

	$return_array['html'] = $html;

	$return_string = json_encode($return_array);

	if ($requested_readable_boolean) {
		$return_string = readable_json($return_string);
	}

	header('Content-Type: text/javascript');

	exit($callback_string . '(' . $return_string . ')');
} elseif ($format_string === 'html') {

	$html = html_encode($return_array, $requested_style_string, $requested_style_boolean);

	$html_container = file_get_contents('tpl.html.html');

	$html_container = preg_replace('/<%= title %>/', 'Can I Use API', $html_container);
	$html_container = preg_replace('/<%= content %>/', $html, $html_container);

	header('Content-Type: text/html');

	print($html_container);
	exit();
} elseif ($format_string === 'php') {
	header('Content-Type: text/plain');

	exit('<?php' . "\n\n" . '$response = ' . var_export($return_array, true) . ';' . "\n\n" . '?>');
} elseif ($format_string === 'xml') {
	header('Content-Type: text/xml');

	$xml = xml_encode($return_array);

	exit($xml);
}

?>