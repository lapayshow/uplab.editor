<?
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;
use Uplab\Editor\Surrogates;
use Uplab\Editor\Tools;


/** @noinspection PhpIncludeInspection */
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";


Loader::includeModule("fileman");
Loader::includeModule("uplab.editor");


$isSectionType = $_REQUEST["TYPE"] == "SECTION";


if ($isSectionType) {
	$searchUrl = "/bitrix/admin/iblock_section_search.php";
	$componentName = "snippet.section";
} else {
	$searchUrl = "/bitrix/admin/iblock_element_search.php";
	$componentName = "snippet.element";
}


$template = htmlspecialchars($_REQUEST["TEMPLATE"]) ?: "";
$componentTemplates = Surrogates::getTemplatesList(["uplab.editor", $componentName]);


if (!is_array($_REQUEST["ID"])) {
	$idList = preg_split("~\s*,\s*~", $_REQUEST["ID"]);
} else {
	$idList = $_REQUEST["ID"];
}
$idList = array_filter(array_map("intval", $idList));


$arItems = [];
if (!empty($idList)) {
	if (!Loader::includeModule("iblock")) return;
	$arOrder = ["sort" => "asc", "date_active_from" => "desc"];
	$arFilter = [
		"ID" => $idList,
	];
	$arSelect = ["ID", "IBLOCK_ID", "NAME"];

	if ($isSectionType) {
		$res = CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect);
	} else {
		$res = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
	}

	while ($item = $res->Fetch()) {
		$arItems[$item["ID"]] = $item;
	}
}


if (array_key_exists("ADD", $_REQUEST) || count($idList) < 1) {
	$idList[] = [];
}


?>


<form class="uplab-surrogate-editor-form" action="">

	<label>
		<select name="TYPE" style="width: 100%;" data-form-input>
			<option value="ELEMENT"
				<?= !$isSectionType ? "selected" : "" ?>>Элемент инфоблока
			</option>
			<option value="SECTION"
				<?= $isSectionType ? "selected" : "" ?>>Раздел инфоблока
			</option>
			<option value="GALLERY">Галерея</option>
		</select>
	</label>

	<hr style="margin: 20px 0;">

	<? foreach ($idList as $i => $id): ?>
		<?
		$item = $arItems[$id];
		$inp["n"] = "UPLAB_SNP";
		$inp["k"] = randString(3);

		if ($isSectionType) {
			$editLink = Tools::buildAdminSectionLink($item["IBLOCK_ID"], $item["ID"]);
		} else {
			$editLink = Tools::buildAdminElementLink($item["IBLOCK_ID"], $item["ID"]);
		}
		?>

		<? if ($i === 0) {
			if ($isSectionType) {
				include __DIR__ . "/__inc_popup_section_header.php";
			} else {
				include __DIR__ . "/__inc_popup_element_header.php";
			}
		} ?>

		<div class="uplab-surrogate-row">
			<div class="uplab-surrogate-col-1">
				<?
				echo "<input " .
					"   name=\"ID[]\" " .
					"   id=\"{$inp["n"]}[{$inp["k"]}]\" " .
					"   data-form-input " .
					"   size=\"5\" " .
					"   value=\"{$item["ID"]}\" " .
					"   type=\"text\" >";

				echo "&nbsp;";

				$url = (new Uri($searchUrl))->addParams([
					"lang" => LANGUAGE_ID,
					"n"    => $inp["n"],
					"k"    => $inp["k"],
				])->getUri();

				echo "<input " .
					"   type=\"button\" " .
					"   value=\"...\" " .
					"   onclick=\"jsUtils.OpenWindow('{$url}', 900, 700);\">";
				?>
			</div>

			<div class="uplab-surrogate-col-2">
				<span id="sp_<?= md5($inp["n"]) ?>_<?= $inp["k"] ?>" style=""><?= $item["NAME"] ?: "" ?></span>
			</div>

			<? if ($item["IBLOCK_ID"]): ?>
				<div class="uplab-surrogate-col-3">
					<a href="<?= $editLink ?>"
					   target="_blank"
					   class="uplab-surrogate-btn"><?= file_get_contents(
							$_SERVER["DOCUMENT_ROOT"] .
							"/bitrix/images/uplab.editor/i-pencil.svg"
						) ?></a>
				</div>
			<? endif; ?>
		</div>
	<? endforeach; ?>


	<hr style="margin: 30px 0;">


	<label>
		<span style="display: block;margin-bottom: 10px;">
			Выберите шаблон для отображения:
		</span>

		<br>

		<select name="TEMPLATE" style="width: 100%;">
			<? foreach ($componentTemplates as $tpl): ?>
				<option value="<?= $tpl["NAME"] ?>"
					<?= $template == $tpl["NAME"] ? "selected" : "" ?>
				><?= $tpl["DISPLAY_NAME"] ?></option>
			<? endforeach; ?>
		</select>
	</label>


</form>
