<?php

namespace Uplab\Editor;


use Bitrix\Main\Context;


class Events
{
	/**
	 * добавляет скрипт, инициализирующий кнопку в визуальном редакторе
	 */
	public static function addElementSearchButton()
	{
		// SimpleCode::addElementSearchButton();
		Surrogates::addAdminButtons();
	}

	public static function removeCodesFromIndex($arFields)
	{
		// SimpleCode::removeCodesFromIndex($arFields);
		Surrogates::removeCodesFromIndex($arFields);

		return $arFields;
	}

	public static function replaceSnippetsOnBuffer(&$content)
	{
		global $APPLICATION;

		Surrogates::replaceOnBuffer($content);

		if (!Context::getCurrent()->getRequest()->isAdminSection()) {
			$val = $APPLICATION->GetTitle() ?: "";
			$content = str_replace("#META_H1#", $val, $content);

			$val = $APPLICATION->GetPageProperty("title") ?: "";
			$content = str_replace("#META_TITLE#", $val, $content);

			$val = $APPLICATION->GetPageProperty("description") ?: "";
			$content = str_replace("#META_DESCRIPTION#", $val, $content);
		}
	}
}