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

function is_assoc($array) {
	return is_array($array) && (count( $array )==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
}

function first_set() {
	$array = func_get_args();

	return reset(array_filter($array, 'is_set'));
}

function first_filled () {
	$array = func_get_args();

	return reset(array_filter($array, 'is_filled'));
}

function first_exists () {
	$array = func_get_args();

	return reset(array_filter($array, 'file_exists'));
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

function current_url( $hasProtocol = true , $serverName = 'localhost' ) {
	$url = '';

	if ($hasProtocol) {
		$url .= 'http' . ( $_SERVER['HTTPS'] ? 's' : '');
	}

	$url .= '//:' . first_set(array($_SERVER['SERVER_NAME'], $serverName)) . $_SERVER['REQUEST_URI'];

	return $url;
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

function caniuse_link($browserName, $browserUrl) {
	return '<a class="caniuse-link" href="' . $browserUrl . '" rel="external" target="_blank">' . $browserName . '</a>';
}

function caniuse_icon($browserId, $browserName, $size = 'normal') {
	$size = $size === 'large' ? '64' : $size === 'small' ? '16' : '32';

	return '<img class="caniuse-image" alt="' . $browserName . '" src="i/' . $browserId . '-' . $size . '.png" width="' . $size . '" height="' . $size . '">';
}

function html_encode($array = array(), $agents = array(), $features = array(), $actions = array()) {
	$required = $array['required'];

	$display  = first_set(@$actions['display'], true);
	$detect   = first_set(@$actions['detect'], false);
	$fail     = first_set(@$actions['fail'], false);
	$size     = first_set(@$actions['size'], 'normal');
	$text     = first_set(@$actions['text'], false);
	$icon     = first_set(@$actions['icon'], false);
	$texticon = first_set(@$actions['texticon'], true);

	$method   = ($fail ? 'fail' : ($detect ? 'detect' : 'display')) . '.' . ($text ? 'text' : ($icon ? 'icon' : 'texticon'));

	$requested_feature_names                = array();
	$supported_browser_names                = array();
	$supported_browser_names_versions       = array();
	$supported_browser_icons                = array();
	$supported_browser_icons_names          = array();
	$supported_browser_icons_names_versions = array();

	foreach ($features as &$feature_name) {
		$feature = $array[$feature_name];
		$feature_title = $feature['title'];

		array_push($requested_feature_names, '<strong>' . $feature_title . '</strong>');
	}

	foreach ($required as $browser_id => &$browser_version) {
		$browser_name = $agents[$browser_id]['browser'];
		$browser_url  = $agents[$browser_id]['url'];
		$browser_name_version = $browser_name . ' ' . $browser_version;
		$browser_icon = caniuse_icon($browser_id, $browser_name, $size);

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

	$html = file_get_contents('tpl.' . $method . '.html');

	$html = preg_replace('/<%= requested_feature_names %>/', implode(' &amp; ', $requested_feature_names), $html);
	$html = preg_replace('/<%= supported_browser_names %>/', implode(' or ', $supported_browser_names), $html);
	$html = preg_replace('/<%= supported_browser_names_versions %>/', implode(' or ', $supported_browser_names_versions), $html);
	$html = preg_replace('/<%= supported_browser_icons %>/', implode('', $supported_browser_icons), $html);
	$html = preg_replace('/<%= supported_browser_icons_names %>/', implode('', $supported_browser_icons_names), $html);
	$html = preg_replace('/<%= supported_browser_icons_names_versions %>/', implode('', $supported_browser_icons_names_versions), $html);

	exit($html);

	return $html;
}

?>