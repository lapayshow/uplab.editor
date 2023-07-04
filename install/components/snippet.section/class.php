<?
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


class UPLAB_EDITOR_SNIPPET_SECTION_COMPONENT extends CBitrixComponent
{
	protected $cacheKeys = [
		"ID",
		"IBLOCK_ID",
		"IBLOCK_TYPE",
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

	private static function getIblockData($iblock)
	{
		return IblockTable::getByPrimary($iblock, [
			"select" => ["ID", "IBLOCK_TYPE_ID"],
		])->fetch();
	}

	public function onPrepareComponentParams($params)
	{
		$params["ORDER"] = [];
		$params["SELECT"] = [];
		$params["FILTER"] = [];

		$params["ID"] = array_filter(array_map("intval", $params["ID"]));

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
					$templateParams = $arComponentDescription["COMPONENT_PARAMETERS"];
					$params = array_merge((array)$params, (array)$templateParams);
				}
			}
		}

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

		$params["ID"] = array_filter(array_map("intval", (array)$params["ID"]));
		if ($params["IS_SINGULAR_ITEM"] == "Y") {
			$params["ID"] = current($params["ID"]);
		}

		return $params;
	}

	public function executeComponent()
	{
		try {
			$this->executeProlog();
			$this->__includeComponent();

			if (!$this->readDataFromCache()) {
				$this->getResult();
				$this->putDataToCache();
				$this->includeComponentTemplate();
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

	protected function getChildrenItems(&$arSections)
	{
		if (empty($arSections)) return;
		if (!Loader::includeModule("iblock")) return;

		$arOrder = array("sort" => "asc", "date_active_from" => "desc");
		$arFilter = array(
			"ACTIVE"            => "Y",
			"ACTIVE_DATE"       => "Y",
			"IBLOCK_SECTION_ID" => array_keys($arSections),
		);
		$arSelect = array_merge(
			array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME"),
			(array)$this->arParams["ELEMENTS_SELECT"]
		);

		$res = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);

		while ($ob = $res->GetNextElement()) {
			$item = $ob->GetFields();
			$item["PROPERTIES"] = $ob->GetProperties();

			if (!empty($item["PREVIEW_PICTURE"])) {
				$item["~PREVIEW_PICTURE"] = CFile::GetFileArray($item["PREVIEW_PICTURE"]);
			}

			if (!empty($item["DETAIL_PICTURE"])) {
				$item["~DETAIL_PICTURE"] = CFile::GetFileArray($item["DETAIL_PICTURE"]);
			}

			$arSections[$item["IBLOCK_SECTION_ID"]]["ITEMS"][] = $item;
		}
	}

	protected function getResult()
	{
		if (!Loader::includeModule("iblock")) return null;
		if (empty($this->arParams["ID"])) return null;

		$arIblocks = [];
		$arItems = [];
		$arOrder = [];
		$arFilter = array_merge([
			// "ACTIVE"      => "Y",
			// "ACTIVE_DATE" => "Y",
			"ID" => $this->arParams["ID"],
		], (array)$this->arParams["FILTER"]);
		$arSelect = array_merge([
			"ID",
			"IBLOCK_ID",
			"NAME",
			"PICTURE",
			"DESCRIPTION",
		], (array)$this->arParams["SELECT"]);

		$res = CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect);

		while ($item = $res->GetNext()) {
			if (!isset($arIblocks[$item["IBLOCK_ID"]])) {
				$arIblocks[$item["IBLOCK_ID"]] = self::getIblockData($item["IBLOCK_ID"]);
			}
			$item["IBLOCK_TYPE"] = $arIblocks[$item["IBLOCK_ID"]]["IBLOCK_TYPE_ID"];

			if (!empty($item["PICTURE"])) {
				$item["~PICTURE"] = CFile::GetFileArray($item["PICTURE"]);
			}

			$arItems[$item["ID"]] = $item;

		}

		self::getChildrenItems($arItems);
		$this->arResult["ITEM"] = current($arItems);

		if (!is_array($this->arParams["ID"])) {
			$this->arResult = array_merge(
				(array)$this->arResult,
				(array)current($arItems)
			);
		} else {
			foreach ($this->arParams["ID"] as $id) {
				$this->arResult["ITEMS"][] = $arItems[$id];
			}
		}
	}

}
