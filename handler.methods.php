<?php

/* =============================================================================
   Helper Methods
   ========================================================================== */

function is_function() {
	return reset(array_filter(func_get_args(), 'is_callable'));
}

function is_set($val = undefined) {
	return isset($val);
}

function first_set() {
	return reset(array_filter(func_get_args(), 'is_set'));
}

function first_set_in_array($array = array()) {
	return reset(array_filter($array, 'first_set'));
}

function first_match($pattern = '', $subject = '', $else_string = null) {
	preg_match($pattern, $subject, $matches);

	array_shift($matches);
	array_push($matches, $else_string);

	return first_set_in_array($matches);
}

function array_unset(& $array = array(), $propertyList = array()) {
	foreach ($propertyList as $property) {
		unset($array[$property]);
	}
}

function file_get_json($filename = '', $associative = true) {
	return json_decode(file_get_contents($filename), $associative);
}

function file_put_php($filename = '', $php_string = '') {
	file_put_contents($filename, '<?php' . "\n\n" . $php_string . "\n\n" . '?>');
}

function file_set_array($filename = '', & $array = array(), $array_name = 'array') {
	file_put_php($filename, '$' . $array_name . ' = ' . var_export($array, true) . ';');
}

function file_get_cached_json($json_filename = '', $json_fn = null, $rebuild = false) {
	$php_filename  = 'cache/' . $json_filename . '.php';

	$json_filetime = @filemtime($json_filename);
	$php_filetime  = @filemtime($php_filename);

	if ($json_filetime !== $php_filetime || $rebuild) {
		$json_array = @file_get_json($json_filename);

		if ($json_fn) {
			$json_array = $json_fn($json_array);
		}

		file_set_array($php_filename, $json_array, 'json_array');

		touch($php_filename, $json_filetime);
	} else {
		include $php_filename;
	}

	return $json_array;
}

function json_readable($json = null) {
	$tabcount = 0;
	$return = '';
	$inquote = false;

	$tab = "\t";
	$newline = "\n";

	$json = is_string($json) ? $json : json_encode($json);

	for ($i = 0; $i < strlen($json); $i++)  {
		$char = $json[$i];

		if ($char == '"' && $json[ $i-1] != '\\') {
			$inquote = !$inquote;
		}

		if ($inquote) {
			$return .= $char;
			continue;
		}

		switch ($char) {
			case '{':
				if ($i) {
					$return .= $newline;
				}

				$return .= str_repeat($tab, $tabcount) . $char . $newline . str_repeat( $tab, ++$tabcount);

				break;
			case '}':
				$return .= $newline . str_repeat( $tab, --$tabcount) . $char;

				break;
			case ',':
				$return .= $char;

				if ($json[ $i+1] != '{') {
					$return .= $newline . str_repeat($tab, $tabcount);
				}

				break;
			case ':':
				$return .= $char . ' ';

				break;
			default:
				$return .= $char;
		}
	}

	return $return;
}

/* =============================================================================
   Filters
   ========================================================================== */

// returns filtered data from data.json

function filter_datajson(& $json_array) {
	$array =& $json_array['data'];

	foreach ($array as & $value) {
		$value['partial'] = filter_datajsonstats($value['stats'], '/[ay]/');
		$value['supported'] = filter_datajsonstats($value['stats'], '/y/');


		array_unset($value, explode(' ', 'keywords categories description links spec notes stats status usage_perc_y usage_perc_a'));
	}

	return $array;
}

// returns filtered stats from data.json stats

function filter_datajsonstats($array = array(), $filter = '') {
	$return_array = array();

	foreach ($array as $agent_id =>& $agent_array) {
		foreach ($agent_array as $agent_version =>& $agent_support) {
			if (preg_match($filter, $agent_support)) {
				if (!isset($return_array[$agent_id])) {
					$return_array[$agent_id] = $agent_version;
				} else {
					$return_array[$agent_id] = version_compare($return_array[$agent_id], $agent_version) < 0 ? $return_array[$agent_id] : $agent_version;
				}
			}
		}
	}

	return $return_array;
}

// returns filtered keywords from keywords.json

function filter_keywords(& $json_array) {
	$return_array = array();

	foreach ($json_array as $keyword_name => &$keyword_words) {
		$return_array[$keyword_name] = '/^(' . preg_replace('/ /', '[s]*|', $keyword_words) . '[s]*)$/';
	}

	return $return_array;
}

// returns filtered features from successful searches in keywords

function filter_features(& $request_features_array = array(), & $keywords_array = array()) {
	$return_array = array();

	foreach ($request_features_array as $request_feature_name) {
		$request_feature_name = strtolower($request_feature_name);

		if (isset($keywords_array[$request_feature_name])) {
			array_push($return_array, $request_feature_name);
		} else {
			$request_feature_name = preg_replace('/[^A-z0-9]/', '', $request_feature_name);

			foreach ($keywords_array as $keyword_name =>& $keyword_words) {
				if (preg_match($keyword_words, $request_feature_name)) {
					array_push($return_array, $keyword_name);
				}
			}
		}
	}

	$return_array = array_unique($return_array);

	return $return_array;
}

// returns filtered support metrics from features, agents, and data

function filter_supportmetrics(& $option_features = array(), $agents_array = array(), & $data_array = array(), $state) {
	$return_array = array(
		'agents' =>& $agents_array,
		'features' => array(),
		'results' => array(),
		'result' => array()
	);

	foreach ($agents_array as & $agent_array) {
		unset($agent_array['sniffer']);
	}

	foreach ($option_features as $feature_name) {
		$property_array = first_set(@$data_array[$feature_name][$state], array());

		$return_array['result'][$feature_name] = $property_array;

		$return_array['features'][$feature_name] = $data_array[$feature_name]['title'];

		$all_array =& $return_array['results'];

		foreach ($agents_array as $agent_name =>& $unused_array) {
			$agent_array = @$property_array[$agent_name];

			if ($agent_array && (version_compare($agents_array[$agent_name]['currentVersion'], $agent_array) > -1)) {
				$all_array[$agent_name] = (version_compare(@$all_array[$agent_name], $agent_array) < 1) ? $agent_array : $all_array[$agent_name];

			} else {
				unset($return_array['agents'][$agent_name]);
				unset($return_array['result'][$feature_name][$agent_name]);
				unset($agents_array[$agent_name]);
				unset($all_array[$agent_name]);
			}
		}
	}

	return $return_array;
}

// returns filtered options from the get method

function filter_options() {
	// Set array
	$array = array();
	$key = 'option_';

	// Set variables
	$array[$key . 'callback']   = @$_GET['callback'];
	$array[$key . 'features']   = explode(' ', @$_GET['features']);
	$array[$key . 'format']     = first_match('/(html|js|json|php|xml)/', @$_GET['format'], 'js');
	$array[$key . 'html']       = isset($_GET['html']);
	$array[$key . 'readable']   = isset($_GET['readable']);
	$array[$key . 'noagent']    = isset($_GET['noagent']);
	$array[$key . 'noagents']   = isset($_GET['noagents']);
	$array[$key . 'nofeatures'] = isset($_GET['nofeatures']);
	$array[$key . 'nocss']      = isset($_GET['nocss']);
	$array[$key . 'noresult']   = isset($_GET['noresult']);
	$array[$key . 'noresults']  = isset($_GET['noresults']);
	$array[$key . 'template']	  = isset($_GET['template']);
	$array[$key . 'style']      = (
		isset($_GET['texticon']) || (isset($_GET['text']) && isset($_GET['icon'])) ? 'texticon' : (
			isset($_GET['icon']) ? 'icon' : (
				isset($_GET['text']) ? 'text' : 'button'
			)
		)
	);
	$array[$key . 'barebones'] = isset($_GET['barebones']);

	return $array;
}

// returns user agent details

function filter_useragent(& $agents_array) {
	$user_agent_string = $_SERVER['HTTP_USER_AGENT'];
	$return_array = array();

	foreach($agents_array as $agent_string => &$agent_array) {
		$agent_sniffer = $agent_array['sniffer'];

		$agent_boolean = preg_match($agent_sniffer, $user_agent_string, $agent_matches);

		if ($agent_boolean) {
			unset($agents_array[$agent_string]['sniffer']);

			$return_array = array_merge(
				array('id' => $agent_string),
				$agent_array,
				array('version' => first_set(@$agent_matches[1], @$agent_matches[2]))
			);
		}
	}

	return $return_array;
}

function filter_agents(&$support_array = array(), &$useragent_array = array()) {
	foreach ($support_array['agents'] as $agent_id =>& $agent_array) {
		if ($agent_array['type'] !== $useragent_array['type']) {
			unset($support_array['agents'][$agent_id]);
			unset($support_array['results'][$agent_id]);

			foreach ($support_array['result'] as & $result_array) {
				unset($result_array[$agent_id]);
			}
		}
	}
}



/* =============================================================================
   HTML Encode
   ========================================================================== */

function html_encode_agents(&$agents_array, $requested_style_string = '') {
	$html = '';

	if ($agents_array) {
		foreach ($agents_array as $agent_string => &$agent_array) {
			$html .= html_encode_agent($agent_string, $agent_array, $requested_style_string);
		}
	}

	return $html;
}

function html_encode_agent(&$agent_string = '', &$agent_array, $requested_style_string = '') {
	$html = '';
  $anchor_classes = '';


	if ($requested_style_string !== 'text') {
    $anchor_classes = ' caniuse-agt-ico caniuse-ico-' . @$agent_string;
	}

	$html .= '<a class="caniuse-agt' . $anchor_classes. '" href="' . @$agent_array['url'] . '" rel="external" target="_blank">';

  if ($requested_style_string !== 'icon') {
		$html .= '<span class="caniuse-agt-ttl">' . @$agent_array['name'] . '</span>';
	}
	
	$html .= '</a> ';

	return $html;
}

function html_encode_feature($feature_string = '', $feature_name_string = '', $supported = true) {
	$html = '';
	$html .= $feature_name_string;

	return $html;
}

function html_encode_features(&$return_array) {
	$features_array	    = $return_array['features'];
	$unsupported_string = @implode(' ', $return_array['unsupported']);

	$html_array = array();

	foreach($features_array as $feature_string => &$feature_name_string) {
		array_push($html_array, html_encode_feature($feature_string, $feature_name_string, !preg_match('/' . $feature_string . '/', $unsupported_string)));
	}

	return implode(', ', $html_array);
}

function html_encode(&$return_array = array(), $requested_style_string = '', $requested_style_boolean = true, $requested_templating = false) {
	$html = '';
  $styles = '';

	if (isset($return_array['error'])) {
		return $html;
	}

	// Just template-variables
	$template_file = ($requested_templating) ? 'tpl/locale.html' : 'tpl/tpl.html';

	// Append file-contents to output
	$html .= file_get_contents($template_file);

  if (!$return_array['agent']) {
		$html = preg_replace('/\s*<% noagent %>|<% \/noagent %>/', '', $html);
		$html = preg_replace('/\s*<% supported %>[\W\w]*?<% \/supported %>/', '', $html);
		$html = preg_replace('/\s*<% supported_with_upgrade %>[\W\w]*?<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/\s*<% unsupported %>[\W\w]*?<% \/unsupported %>/', '', $html);
		$html = preg_replace('/\s*<% partial %>[\W\w]*?<% \/partial %>/', '', $html);
  } else if ($return_array['supported']) {
		$html = preg_replace('/\s*<% noagent %>[\W\w]*?<% \/noagent %>/', '', $html);
		$html = preg_replace('/\s*<% supported %>|<% \/supported %>/', '', $html);
		$html = preg_replace('/\s*<% supported_with_upgrade %>[\W\w]*?<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/\s*<% unsupported %>[\W\w]*?<% \/unsupported %>/', '', $html);
		$html = preg_replace('/\s*<% partial %>[\W\w]*?<% \/partial %>/', '', $html);
	} elseif ($return_array['upgradable']) {
		$html = preg_replace('/\s*<% noagent %>[\W\w]*?<% \/noagent %>/', '', $html);
		$html = preg_replace('/\s*<% supported %>[\W\w]*?<% \/supported %>/', '', $html);
		$html = preg_replace('/\s*<% supported_with_upgrade %>|<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/\s*<% unsupported %>[\W\w]*?<% \/unsupported %>/', '', $html);
		$html = preg_replace('/\s*<% partial %>[\W\w]*?<% \/partial %>/', '', $html);
	} elseif ($return_array['partial']) {
		$html = preg_replace('/\s*<% noagent %>[\W\w]*?<% \/noagent %>/', '', $html);
		$html = preg_replace('/\s*<% supported %>[\W\w]*?<% \/supported %>/', '', $html);
		$html = preg_replace('/\s*<% supported_with_upgrade %>[\W\w]*?<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/\s*<% unsupported %>[\W\w]*?<% \/unsupported %>/', '', $html);
		$html = preg_replace('/\s*<% partial %>|<% \/partial %>/', '', $html);
	} 
	else {
		$html = preg_replace('/\s*<% noagent %>[\W\w]*?<% \/noagent %>/', '', $html);
		$html = preg_replace('/\s*<% supported %>[\W\w]*?<% \/supported %>/', '', $html);
		$html = preg_replace('/\s*<% supported_with_upgrade %>[\W\w]*?<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/\s*<% unsupported %>|<% \/unsupported %>/', '', $html);
		$html = preg_replace('/\s*<% partial %>[\W\w]*?<% \/partial %>/', '', $html);
	}

	$html = preg_replace('/<%= browserid %>/', $return_array['agent']['id'], $html);
	$html = preg_replace('/<%= browserurl %>/', $return_array['agent']['url'], $html);
	$html = preg_replace('/<%= alternatives %>/', html_encode_agents($return_array['agents'], $requested_style_string), $html);
	$html = preg_replace('/<%= features %>/', html_encode_features($return_array), $html);

	$html = preg_replace('/[\s]+/', ' ', $html);

	if ($requested_style_boolean && !$return_array['supported']) {
		if ($requested_style_string == 'text') {
			$styles = @file_get_contents('css/text.css');
		} else {
			$styles = @file_get_contents('css/text.css') . @file_get_contents('css/icon.css');
		}

		$styles = '<style scoped>' . preg_replace('/[\s]+/', ' ', $styles) . '</style>';
		$html = preg_replace('/<%= styles %>/', $styles, $html);
	}

	return $html;
}



/* =============================================================================
   XML Encode
   ========================================================================== */

function is_assoc($array) {
	return is_array($array) && (count( $array )==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
}

function is_array_with_numeric ($array) {
	foreach ($array as $key => $value) {
		$keyFirstChar = substr($key, 0);
		$keyFirstCharIsAlpha = preg_match('/[A-z]/', $keyFirstChar);

		if (!$keyFirstCharIsAlpha) {
			return true;
		}
	}

	return false;
}

function xml_node_encode(&$array = array(), $node_name = 'node', $tab_indent = "") {
	$xml = '';
	$isArray = is_array($array) || is_object($array);
	$isAssoc = $isArray && is_assoc($array);
	$isAssocWithNumeric = $isAssoc && is_array_with_numeric($array);

	if ($isArray) {
		$xml .= "\n";

		foreach ($array as $key => &$value) {
			$isValueArray = is_array($value) || is_object($value);

			if ($isAssocWithNumeric) {
				$tagOpen = $node_name . ' name="' . $key . '"';
				$tagClose = $node_name;
			} else {
				$tagOpen = $isAssoc ? $key : $node_name;
				$tagClose = $tagOpen;
			}

			if ($isValueArray) {
				$xml .= $tab_indent . '<' . $tagOpen . '>';
				$xml .= xml_node_encode($value, $node_name, $tab_indent . "\t");
				$xml .= $tab_indent . '</' . $tagClose . '>';
			} else {
				$xml .= $tab_indent . '<' . $tagOpen . '>' . $value . '</' . $tagClose . '>';
			}

			$xml .= "\n";
		}
	} else {
		$xml .= htmlspecialchars($array, ENT_QUOTES);
	}

	return $xml;
}

function xml_encode($array, $node_block = 'nodes', $node_name = 'node') {
	$xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

	$xml .= '<' . $node_block . '>';
	$xml .= xml_node_encode($array, $node_name, "\t");
	$xml .= '</' . $node_block . '>';

	return $xml;
}

?>
