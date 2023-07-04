<?
use Bitrix\Main\Loader;


defined("B_PROLOG_INCLUDED") && B_PROLOG_INCLUDED === true || die();
/**
 * @global CMain $APPLICATION
 */


if (!Loader::includeModule("uplab.core")) {
	throw new Exception("Module 'uplab.core' is required");
}
Loader::includeModule("uplab.editor");


$options = new Uplab\Editor\Module\Options(__FILE__, [
	[
		"DIV"     => "tab1",
		"TAB"     => "Обновление модуля",
		"OPTIONS" => ["Сохранить настройки модуля для обновления стилей, скриптов, компонентов модуля"],
	],
]);


$options->drawOptionsForm();
