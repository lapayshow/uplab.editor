<?
use Bitrix\Main\Application;
use Bitrix\Main\Loader;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


class UPLAB_EDITOR_SNIPPET_ELEMENT_COMPONENT extends CBitrixComponent
{
	protected $cacheKeys = [
		"ID",
		"NAME",
		"__RETURN_VALUE",
	];

	protected $dependModules = ["uplab.editor", "iblock"];

	/**
	 * дополнительные параметры, от которых должен зависеть кеш
	 *
	 * @var array
	 */
	protected $additionalCacheID = [];

	public function onPrepareComponentParams($params)
	{
		global $APPLICATION;

		$params["ORDER"] = [];
		$params["SELECT"] = [];
		$params["FILTER"] = [];

		if ($this->initComponentTemplate()) {
			$templateDescriptionPath = implode("", [
				Application::getDocumentRoot(),
				$this->__template->__folder,
				"/.description.php",
			]);

			if (file_exists($templateDescriptionPath)) {
				/** @noinspection PhpIncludeInspection */
				include $templateDescriptionPath;

				if (!empty($arComponentDescription["COMPONENT_PARAMETERS"])) {
					$templateParams = isset($arComponentDescription["COMPONENT_PARAMETERS"])
						? (array)$arComponentDescription["COMPONENT_PARAMETERS"]
						: [];

					$params = array_merge(
						(array)$params,
						(array)$templateParams
					);

					if (!empty($templateParams["ORDER"])) $params["ORDER"] = $templateParams["ORDER"];
					if (!empty($templateParams["SELECT"])) $params["SELECT"] = $templateParams["SELECT"];
					if (!empty($templateParams["FILTER"])) $params["FILTER"] = $templateParams["FILTER"];

					// Выводить ли редактируемую область, либо управление будет на уровне шаблона
					if (empty($params["CUSTOMIZED_EDITABLE_AREA"])) {
						$params["CUSTOMIZED_EDITABLE_AREA"] = "N";
					}
				}
			}
		}

		$params["ID"] = array_filter(array_map("intval", (array)$params["ID"]));
		$params["DESIGN_MODE"] = $APPLICATION->GetShowIncludeAreas();
		$params["SINGULAR_ITEM"] = count($params["ID"]) == 1;

		if ($params["DESIGN_MODE"]) {
			$params["CACHE_TYPE"] = "N";
			$params["CACHE_TIME"] = 0;
		} else {
			if (!isset($params["CACHE_TYPE"])) {
				$params["CACHE_TYPE"] = "A";
			}

			if (!isset($params["CACHE_TIME"])) {
				if (defined("CACHE_TIME")) {
					$params["CACHE_TIME"] = CACHE_TIME;
				} else {
					$params["CACHE_TIME"] = 360000;
				}
			}
		}

		return array_filter($params);
	}

	public function executeComponent()
	{
		try {
			$this->executeProlog();
			$this->__includeComponent();

			if (!$this->readDataFromCache()) {
				$this->getResult();
				$this->putDataToCache();
				if (!$this->includeEditableTemplate()) {
					$this->includeComponentTemplate();
				}
				$this->endResultCache();
			}

			$this->executeEpilog();

			return $this->arResult["__RETURN_VALUE"];
		} catch (Exception $e) {
			$this->abortResultCache();
			ShowError($e->getMessage());
		}

		return false;
	}

	protected function includeEditableTemplate()
	{
		$arParams = &$this->arParams;

		if (
			!$arParams["DESIGN_MODE"] ||
			!$arParams["SINGULAR_ITEM"] ||
			$arParams["CUSTOMIZED_EDITABLE_AREA"] == "Y" ||
			empty($this->arResult["ITEM"]["EDIT_ATTR"])
		) {
			return false;
		}

		echo "<div {$this->arResult["ITEM"]["EDIT_ATTR"]}>";
		$this->includeComponentTemplate();
		echo "</div><!-- / uplab.editor:snippet.element -->";

		return true;
	}

	/**
	 * определяет читать данные из кеша или нет
	 *
	 * @return bool
	 */
	protected function readDataFromCache()
	{
		if ($this->arParams["CACHE_TYPE"] == "N") {
			return false;
		}

		return !($this->StartResultCache(false, $this->additionalCacheID));
	}

	/**
	 * кеширует ключи массива arResult
	 */
	protected function putDataToCache()
	{
		if (is_array($this->cacheKeys) && sizeof($this->cacheKeys) > 0) {
			$this->SetResultCacheKeys($this->cacheKeys);
		}
	}

	/**
	 * выполяет действия перед кешированием
	 */
	protected function executeProlog()
	{
	}

	/**
	 * выполняет действия после выполения компонента, например установка заголовков из кеша
	 */
	protected function executeEpilog()
	{
	}

	protected function getResult()
	{
		if (!Loader::includeModule("iblock")) return;

		if (!empty($this->arParams["ID"])) {

			$arItems = [];
			$arOrder = $this->arParams["ORDER"] ?: [];

			$arFilter = array_merge([
				"ACTIVE"      => "Y",
				"ACTIVE_DATE" => "Y",
				"ID"          => $this->arParams["ID"],
			], (array)$this->arParams["FILTER"]);

			$arSelect = array_merge([
				"ID",
				"IBLOCK_ID",
				"NAME",
			], (array)$this->arParams["SELECT"]);

			$res = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
			while ($ob = $res->GetNextElement()) {
				$item = $ob->GetFields();
				$item["PROPERTIES"] = $ob->GetProperties();
				$arItems[$item["ID"]] = $item;

				if ($this->arParams["DESIGN_MODE"]) {
					$buttons = CIBlock::GetPanelButtons(
						$item["IBLOCK_ID"],
						$item["ID"],
						0,
						["SECTION_BUTTONS" => false, "SESSID" => false]
					);
					$item["EDIT_LINK"] = $buttons["edit"]["edit_element"]["ACTION_URL"];
					$item["DELETE_LINK"] = $buttons["edit"]["delete_element"]["ACTION_URL"];
					$item["EDIT_ATTR"] = " data-hermitage-link='{$item["EDIT_LINK"]}' ";
				}

				if (empty($this->arResult["ITEM"])) $this->arResult["ITEM"] = $item;
			}

			foreach ($this->arParams["ID"] as $id) {
				$this->arResult["ITEMS"][] = $arItems[$id];
			}
		}
	}

}
