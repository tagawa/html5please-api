<?php

function is_assoc($array) {
	return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
}

function is_array_with_numeric (&$array) {
	foreach ($array as $key => &$value) {
		$keyFirstChar = substr($key, 0);
		$keyFirstCharIsAlpha = preg_match('/[A-z]/', $keyFirstChar);

		if (!$keyFirstCharIsAlpha) {
			return true;
		}
	}

	return false;
}

function generate_xml_from_array(&$array = array(), $node_name = 'node', $tab_indent = "") {
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
				$xml .= generate_xml_from_array($value, $node_name, $tab_indent . "\t");
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

function generate_valid_xml_from_array($array, $node_block = 'nodes', $node_name = 'node') {
	$xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

	$xml .= '<' . $node_block . '>';
	$xml .= generate_xml_from_array($array, $node_name, "\t");
	$xml .= '</' . $node_block . '>';

	return $xml;
}

?>