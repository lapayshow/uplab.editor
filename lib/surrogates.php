<?php
/**
 * Created by PhpStorm.
 * User: geffest
 * Date: 14.09.2018
 * Time: 10:59
 */

namespace Uplab\Editor;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;
use CComponentParamsManager;
use CJSCore;


class Surrogates
{
	// const REGEXP_LEGACY = "/\[(ELEMENT|SECTION)_SNIPPET ID=\"([#\w\s\._,-]*)\" TEMPLATE=\"([#\w\s\._-]*)\"\]\[\/(ELEMENT|SECTION)_SNIPPET\]/";
	const REGEXP          = "/\[UP_EDITOR_SNIPPET TYPE=\"([#\w\s._-]*)\"](.*?)\[\/UP_EDITOR_SNIPPET]/";
	const COMPONENT_TYPES = ["element", "section"];

	public static function addAdminButtons()
	{
		global $APPLICATION;

		if (
			!$APPLICATION->GetShowIncludeAreas() &&
			!Context::getCurrent()->getRequest()->isAdminSection()
		) {
			return;
		}

		$assets = Asset::getInstance();

		CJSCore::Init(array("jquery"));

		echo "<style>.bx-plane-button i { background-size: contain !important; /*transform: scale(0.8);*/ }</style>";

		$assets->addJs("/bitrix/js/uplab.editor/surrogates.js");
		// $assets->addCss("/bitrix/css/uplab.editor/surrogates.min.css");
		$APPLICATION->SetAdditionalCSS("/bitrix/css/uplab.editor/surrogates.css");
	}

	public static function replaceOnBuffer(&$content, $i = 0)
	{
		if ($i > 5) return;

		$content = preg_replace_callback(
			self::REGEXP,
			function ($matches) use ($i) {
				global $APPLICATION;

				$replaceContent = "";

				try {
					$parsedSnippetData = Json::decode(!empty($matches[2]) ? $matches[2] : "");
				} catch (ArgumentException $e) {
				}

				$componentType = mb_strtolower($matches[1]);

				if ($componentType && in_array($componentType, self::COMPONENT_TYPES)) {
					ob_start();
					$APPLICATION->IncludeComponent(
						"uplab.editor:snippet." . mb_strtolower($matches[1]),
						!empty($parsedSnippetData["TEMPLATE"]) ? $parsedSnippetData["TEMPLATE"] : "",
						[
							"ID"   => !empty($parsedSnippetData["ID"]) ? $parsedSnippetData["ID"] : [],
							"CODE" => !empty($parsedSnippetData["CODE"]) ? $parsedSnippetData["CODE"] : [],
						],
						false, ["HIDE_ICONS" => "Y"]
					);
					$replaceContent = ob_get_clean();
				}

				self::replaceOnBuffer($replaceContent, $i + 1);

				return $replaceContent;
			},
			$content
		);
	}

	public static function getTemplatesList($component = [])
	{
		$componentData = CComponentParamsManager::GetComponentProperties(
			implode(":", $component),
			"",
			SITE_TEMPLATE_ID
		);
		$componentTemplates = $componentData["templates"];

		foreach ($componentTemplates as $i => &$tpl) {
			$arComponentDescription = false;

			if (!empty($tpl["TEMPLATE"])) {
				$path = getLocalPath(
					"templates/{$tpl["TEMPLATE"]}/components/{$component[0]}/{$component[1]}/{$tpl["NAME"]}"
				);

				/** @noinspection PhpIncludeInspection */
				include $_SERVER["DOCUMENT_ROOT"] . $path . "/.description.php";

				if (!empty($arComponentDescription) && !empty($arComponentDescription["NAME"])) {
					$tpl["DISPLAY_NAME"] = $arComponentDescription["NAME"];
				}
			}

			$tpl["SORT"] = $arComponentDescription["SORT"] ?: 500;

			unset($tpl);
		}
		usort($componentTemplates, function ($a, $b) {
			if ($a["SORT"] == $b["SORT"]) {
				return strcmp($a["DISPLAY_NAME"], $b["DISPLAY_NAME"]);
			} else {
				return $a["SORT"] < $b["SORT"] ? -1 : 1;
			}
		});

		return $componentTemplates;
	}

	public static function removeCodesFromIndex(&$arFields)
	{
		$arFields["BODY"] = preg_replace(self::REGEXP, "", $arFields["BODY"]);
	}

}
