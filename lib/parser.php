<?php

namespace Uplab\Editor;


class Parser
{
	public static function getData($string)
	{
		$data = [];

		preg_match_all("~\[ELEMENT IBLOCK=([0-9]+)\][\s]*([0-9]+)[\s]*\[/ELEMENT\]~", $string, $match);
		foreach ($match[0] as $i => $matchStr) {
			$data["elements"][] = [
				"match"  => $matchStr,
				"iblock" => $match[1][$i],
				"id"     => $match[2][$i],
			];
			$data["elements_id"][$match[2][$i]] = $match[2][$i];
		}

		preg_match_all("~\[SECTION IBLOCK=([0-9]+)\][\s]*([0-9]+)[\s]*\[/SECTION\]~", $string, $match);
		foreach ($match[0] as $i => $matchStr) {
			$data["sections"][] = [
				"match"  => $matchStr,
				"iblock" => $match[1][$i],
				"id"     => $match[2][$i],
			];
			$data["sections_id"][$match[2][$i]] = $match[2][$i];
		}

		return $data;
	}
}