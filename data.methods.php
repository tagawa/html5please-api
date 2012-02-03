<?php

/* --------------------------------------------------------------------------
   Helper Methods
   -------------------------------------------------------------------------- */

function is_set($val = undefined) {
	return isset($val);
}

function is_empty($val = undefined) {
	return !isset($val) || (strcmp(trim((string) $val), '') == 0);
}

function is_filled($val = undefined) {
	return !is_empty($val);
}

function first_set() {
	return reset(array_filter(func_get_args(), 'is_set'));
}

function first_filled() {
	return reset(array_filter(func_get_args(), 'is_filled'));
}

function first_exists() {
	return reset(array_filter(func_get_args(), 'file_exists'));
}

function get_file_json($filename = '', $assoc = true) {
	return json_decode(file_get_contents($filename), $assoc);
}

/* --------------------------------------------------------------------------
   Specific Methods
   -------------------------------------------------------------------------- */

function filter_data($features_array = array(), $data_array = array()) {
	// create a return array
	$return = array('data' => array(), 'supported' => array());

	// loop through the features
	foreach ($features_array as $index => $feature_name) {
		// if feature does exist in data
		if (isset($data_array[$feature_name])) {
			// set feature data and supported
			$feature_data    =& $data_array[$feature_name];
			$feature_supported  =& $feature_data['supported'];

			// add to return these feature data
			$return['data'][$feature_name] = $feature_data;

			// loop through the feature supported
			foreach ($feature_supported as $browser_id => &$browser_version) {
				// if return does not have a supported section then add it
				if (empty($return['supported'])) {
					$return['supported'] = $feature_supported;
				}

				// if return already has a supported section for this browser
				if (isset($data_array_new['supported'][$browser_id])) {
					// compare browser versions between the return and the feature supported and use the newest
					$current_version = $data_array_new['supported'][$browser_id];
					$return_version  = $return['supported'][$browser_id];
					$return['supported'][$browser_id] = version_compare($current_version, $return_version) ? $return_version : $current_version;
				}
			}
		}
	}

	// return
	return $return;
}

function filter_supported($supported_array = array(), $agents_array = array(), $ua_array = array()) {
	$browsers_array = array();
	$client_array   = array_merge_recursive($ua_array, array('supported' => false));

	foreach ($supported_array as $browser_id => $browser_version) {
		$filtered_agent_array = array(
			'id' => $browser_id,
			'name' => $agents_array[$browser_id]['browser'],
			'version' => $supported_array[$browser_id],
			'url' => $agents_array[$browser_id]['url']
		);

		if ($browser_id === $ua_array['id']) {
			$client_array['supported'] = version_compare($ua_array['version'], $filtered_agent_array['version']) > 0;

			if (!$client_array['supported']) {
				$client_array['supported_version'] = $filtered_agent_array['version'];
				$client_array['url'] = $filtered_agent_array['url'];
			}
		}

		array_push($browsers_array, $filtered_agent_array);
	}

	return array('client' => $client_array, 'browsers' => $browsers_array);
}

function ua_array($id, $name, $version) {
	return array('id' => $id, 'name' => $name, 'version' => $version);
}

function ua_detect($ua = null) {
	$ua = isset($ua) ? $ua : $_SERVER['HTTP_USER_AGENT'];

	$ua_chrome  = '/chrome\/([\d\.]+)/i';
	$ua_firefox = '/firefox\/([\d\.]+)/i';
	$ua_ie      = '/msie[\W\w]*?([\d\.]+)/i';
	$ua_safari  = '/version\/([\d\.]+)[\W\w]*?safari/i';
	$ua_opera   = '/opera[\W\w]*?version\/([\d\.]+)/i';
	$ua_oopera  = '/opera[\W\w]*?([\d\.]+)/i';

	$ua_mobile  = '/blackberry|mobile|tablet/i';

	$is_chrome  = preg_match($ua_chrome, $ua, $vs_chrome);
	$is_firefox = preg_match($ua_firefox, $ua, $vs_firefox);
	$is_ie      = preg_match($ua_ie, $ua, $vs_ie);
	$is_safari  = preg_match($ua_safari, $ua, $vs_safari);
	$is_opera   = preg_match($ua_opera, $ua, $vs_opera);
	$is_oopera  = preg_match($ua_oopera, $ua, $vs_oopera);

	$is_mobile  = !!preg_match($ua_mobile, $ua);

	$ua_array   = $is_chrome ? ua_array('chrome', 'Chrome', $vs_chrome[1]) : (
		$is_firefox ? ua_array('firefox', 'Firefox', $vs_firefox[1]) : (
			$is_ie ? ua_array('ie', 'Internet Explorer', $vs_ie[1]) : (
				$is_safari ? ua_array('safari', 'Safari', $vs_safari[1]) : (
					$is_opera ? ua_array('opera', 'Opera', $vs_opera[1]) : (
						$is_oopera ? ua_array('opera', 'Opera', $vs_oopera[1]) : ua_array('unknown', 'Unknown', '0')
					)
				)
			)
		)
	);

	return array_merge_recursive($ua_array, array('mobile' => $is_mobile));
}

function mime_type($extension = null) {
	if ($extension == 'html') {
		return 'text/html';
	}
	if ($extension == 'js') {
		return 'text/javascript';
	}
	if ($extension == 'json') {
		return 'text/json';
	}
	if ($extension == 'xml') {
		return 'text/xml';
	}
}

/* --------------------------------------------------------------------------
   JSON Methods
   -------------------------------------------------------------------------- */

function json_escape_html($html = '') {
	$html = preg_replace('/\n/', '\\n', $html);
	$html = preg_replace('/\r/', '\\r', $html);
	$html = preg_replace('/\t/', '\\t', $html);
	$html = preg_replace('/"/', '\\"', $html);

	return $html;
}

/* --------------------------------------------------------------------------
   HTML Methods
   -------------------------------------------------------------------------- */

function caniuse_link($browser_name, $browser_url) {
	return '<a class="caniuse-link" href="' . $browser_url . '" rel="external" target="_blank">' . $browser_name . '</a>';
}

function caniuse_icon($browser_id, $browser_name, $icon_size = 'normal') {
	$icon_size = $icon_size === 'large' ? '64' : $icon_size === 'small' ? '16' : '32';

	return '<img class="caniuse-image" alt="' . $browser_name . '" src="i/' . $browser_id . '-' . $icon_size . '.png" width="' . $icon_size . '" height="' . $icon_size . '">';
}

function html_encode($supported_array = array(), $actions = array()) {
	$icon_size = first_set(@$actions['size'], 'normal');
	$text      = isset($actions['text']);
	$icon      = isset($actions['icon']);
	$method    = $text ? 'text' : ($icon ? 'icon' : 'texticon');

	$requested_feature_names                = array();
	$supported_browser_names                = array();
	$supported_browser_names_versions       = array();
	$supported_browser_icons                = array();
	$supported_browser_icons_names          = array();
	$supported_browser_icons_names_versions = array();

	$client_array  = $supported_array['client'];
	$browser_array = $supported_array['browsers'];

	$supported               = !!@$client_array['supported'];
	$supported_with_upgrade  = !!@$client_array['supported_version'];
	$unsupported             = !$supported && !$supported_with_upgrade;

	$client_id      = $client_array['id'];
	$client_name    = $client_array['name'];
	$client_version = @$client_array['supported_version'];
	$client_url     = @$client_array['url'];
	$client_icon = caniuse_icon($client_id, $client_name, $icon_size);
	$client_name_version = $client_name . ' ' . $client_version;

	$updated_browser_name = caniuse_link($client_name, $client_url);
	$updated_browser_name_version = caniuse_link($client_name_version, $client_url);
	$updated_browser_icon = caniuse_link($client_icon, $client_url);
	$updated_browser_icon_name = caniuse_link($client_icon . '<br><span class="caniuse-text">' . $client_name . '</span>', $client_url);
	$updated_browser_icon_name_version = caniuse_link($client_icon . '<br><span class="caniuse-text">' . $client_name_version . '</span>', $client_url);

	foreach ($browser_array as &$browser) {
		$browser_id      = $browser['id'];
		$browser_name    = $browser['name'];
		$browser_url     = @$browser['url'];
		$browser_version = $browser['version'];
		$browser_name_version = $browser_name . ' ' . $browser_version;
		$browser_icon = caniuse_icon($browser_id, $browser_name, $icon_size);

		$link_browser_name = caniuse_link($browser_name, $browser_url);
		$link_browser_name_version = caniuse_link($browser_name_version, $browser_url);
		$link_browser_icon = caniuse_link($browser_icon, $browser_url);
		$link_browser_icon_name = caniuse_link($browser_icon . '<br><span class="caniuse-text">' . $browser_name . '</span>', $browser_url);
		$link_browser_icon_name_version = caniuse_link($browser_icon . '<br><span class="caniuse-text">' . $browser_name_version . '</span>', $browser_url);

		array_push($supported_browser_names, $link_browser_name);
		array_push($supported_browser_names_versions, $link_browser_name_version);
		array_push($supported_browser_icons, $link_browser_icon);
		array_push($supported_browser_icons_names, $link_browser_icon_name);
		array_push($supported_browser_icons_names_versions, $link_browser_icon_name_version);
	}

	$html = preg_replace('/[\n\r\t]/', '', file_get_contents('tpl.' . $method . '.html'));

	if ($supported) {
		$html = preg_replace('/<% supported %>|<% \/supported %>/', '', $html);
		$html = preg_replace('/<% supported_with_upgrade %>[\W\w]*?<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/<% unsupported %>[\W\w]*?<% \/unsupported %>/', '', $html);
	}
	else if ($supported_with_upgrade) {
		$html = preg_replace('/<% supported %>[\W\w]*?<% \/supported %>/', '', $html);
		$html = preg_replace('/<% supported_with_upgrade %>|<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/<% unsupported %>[\W\w]*?<% \/unsupported %>/', '', $html);
	}
	else {
		$html = preg_replace('/<% supported %>[\W\w]*?<% \/supported %>/', '', $html);
		$html = preg_replace('/<% supported_with_upgrade %>[\W\w]*?<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/<% unsupported %>|<% \/unsupported %>/', '', $html);
	}

	$html = preg_replace('/<%= requested_feature_names %>/', implode(' &amp; ', $requested_feature_names), $html);

	$html = preg_replace('/<%= updated_browser_name %>/', $updated_browser_name, $html);
	$html = preg_replace('/<%= updated_browser_name_version %>/', $updated_browser_name_version, $html);
	$html = preg_replace('/<%= updated_browser_icon %>/', $updated_browser_icon, $html);
	$html = preg_replace('/<%= updated_browser_icon_name %>/', $updated_browser_icon_name, $html);
	$html = preg_replace('/<%= updated_browser_icon_name_version %>/', $updated_browser_icon_name_version, $html);

	$html = preg_replace('/<%= supported_browser_names %>/', implode(' or ', $supported_browser_names), $html);
	$html = preg_replace('/<%= supported_browser_names_versions %>/', implode(' or ', $supported_browser_names_versions), $html);
	$html = preg_replace('/<%= supported_browser_icons %>/', implode('', $supported_browser_icons), $html);
	$html = preg_replace('/<%= supported_browser_icons_names %>/', implode('', $supported_browser_icons_names), $html);
	$html = preg_replace('/<%= supported_browser_icons_names_versions %>/', implode('', $supported_browser_icons_names_versions), $html);

	return $html;
}

/* --------------------------------------------------------------------------
   XML Methods
   -------------------------------------------------------------------------- */

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