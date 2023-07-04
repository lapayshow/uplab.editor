<?
defined("B_PROLOG_INCLUDED") && B_PROLOG_INCLUDED === true || die();
/**
 * @global CMain $APPLICATION
 * @var array    $item
 */
?>

<? if ($item["IBLOCK_ID"]): ?>
	<a href="<?= \Uplab\Editor\Tools::buildAdminElementNewLink($item["IBLOCK_ID"]) ?>"
	   style="float:right;"
	   target="_blank">Создать раздел</a>
<? endif; ?>

<p style="margin: 0 0 30px 0;">Выберите раздел инфоблока</p>