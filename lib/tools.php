<?php

namespace Uplab\Editor;


use CIBlock;
use CIBlockElement;


class Tools
{
	public static function getIblockType($iblock)
	{
		$res = CIBlock::GetByID($iblock);
		if ($item = $res->Fetch()) {
			return $item["IBLOCK_TYPE_ID"];
		} else {
			return "";
		}
	}

	public static function buildAdminSectionLink($iblock, $id)
	{
		if (strlen($id) <= 0) return "";
		$id = intval($id);

		$iblock = intval($iblock);
		if (empty($iblock)) {
			$res = \CIBlockSection::GetList([], ["ID" => $id], false, ["IBLOCK_ID", "ID"]);
			if ($item = $res->Fetch()) {
				$iblock = $item["IBLOCK_ID"];
				$id = $item["ID"];
			} else {
				return "";
			}
		}

		$type = self::getIblockType($iblock);
		if (empty($type)) return "";

		return "/bitrix/admin/iblock_section_edit.php?IBLOCK_ID={$iblock}&type={$type}&ID={$id}";
	}

	public static function buildAdminElementLink($iblock, $id)
	{
		if (strlen($id) <= 0) return "";
		$id = intval($id);

		$iblock = intval($iblock);
		if (empty($iblock)) {
			$res = CIBlockElement::GetList([], ["ID" => $id], false, ["nTopCount" => 1], ["IBLOCK_ID", "ID"]);
			if ($item = $res->Fetch()) {
				$iblock = $item["IBLOCK_ID"];
				$id = $item["ID"];
			} else {
				return "";
			}
		}

		$type = self::getIblockType($iblock);
		if (empty($type)) return "";

		return "/bitrix/admin/iblock_element_edit.php?IBLOCK_ID={$iblock}&type={$type}&ID={$id}";
	}

	public static function buildAdminElementNewLink($iblock)
	{
		return self::buildAdminElementLink($iblock, 0);
	}
}