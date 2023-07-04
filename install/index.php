<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\Directory;


Loc::loadMessages(__FILE__);

class uplab_editor extends CModule
{
	private $excludeAdminFiles = array(
		"..",
		".",
		"menu.php",
	);

	public function __construct()
	{
		$arModuleVersion = array();

		include __DIR__ . "/version.php";

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_ID = "uplab.editor";
		$this->MODULE_NAME = Loc::getMessage("UPLAB_EDITOR_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("UPLAB_EDITOR_MODULE_DESCRIPTION");

		$this->PARTNER_NAME = Loc::getMessage("UPLAB_EDITOR_MODULE_PARTNER_NAME");
		$this->PARTNER_URI = 'http://www.uplab.ru';

		$this->MODULE_GROUP_RIGHTS = "N";
		$this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = "N";

	}

	public function DoInstall()
	{
		if ($this->IsVersionD7()) {
			$this->InstallDB();
			$this->InstallEvents();
			$this->InstallFiles();

			ModuleManager::registerModule($this->MODULE_ID);
		} else {
			$GLOBALS["APPLICATION"]->ThrowException(Loc::getMessage("UPLAB_EDITOR_INSTALL_EXCEPTION"));
		}
	}

	public function DoUninstall()
	{
		$request = Application::getInstance()->getContext()->getRequest();

		ModuleManager::unRegisterModule($this->MODULE_ID);

		$this->UnInstallEvents();
		$this->UnInstallFiles();

		if ($request->get("savedata") !== "Y") {
			$this->UnInstallDB();
		}

	}

	public function UnInstallDB()
	{
		Option::delete($this->MODULE_ID);
	}

	public function InstallEvents()
	{
		EventManager::getInstance()->registerEventHandler(
			"fileman", "OnBeforeHTMLEditorScriptRuns",
			$this->MODULE_ID,
			"\\Uplab\\Editor\\Events",
			"addElementSearchButton"
		);

		EventManager::getInstance()->registerEventHandler(
			"search", "BeforeIndex",
			$this->MODULE_ID,
			"\\Uplab\\Editor\\Events",
			"removeCodesFromIndex"
		);

		EventManager::getInstance()->registerEventHandler(
			"main", "OnEndBufferContent",
			$this->MODULE_ID,
			"\\Uplab\\Editor\\Events",
			"replaceSnippetsOnBuffer"
		);
	}

	public function UnInstallEvents()
	{
		EventManager::getInstance()->unRegisterEventHandler(
			"fileman", "OnBeforeHTMLEditorScriptRuns",
			$this->MODULE_ID,
			"\\Uplab\\Editor\\Events",
			"addElementSearchButton"
		);

		EventManager::getInstance()->unRegisterEventHandler(
			"search", "BeforeIndex",
			$this->MODULE_ID,
			"\\Uplab\\Editor\\Events",
			"removeCodesFromIndex"
		);

		EventManager::getInstance()->unRegisterEventHandler(
			"main", "OnEndBufferContent",
			$this->MODULE_ID,
			"\\Uplab\\Editor\\Events",
			"replaceSnippetsOnBuffer"
		);
	}

	public function InstallFiles($arParams = array())
	{
		$documentRoot = Application::getInstance()->getContext()->getServer()->getDocumentRoot();

		CopyDirFiles(
			__DIR__ . "/assets/dist/css",
			"{$documentRoot}/bitrix/css/{$this->MODULE_ID}",
			true, true
		);
		CopyDirFiles(
			__DIR__ . "/assets/dist/js",
			"{$documentRoot}/bitrix/js/{$this->MODULE_ID}",
			true, true
		);
		CopyDirFiles(
			__DIR__ . "/assets/images",
			"{$documentRoot}/bitrix/images/{$this->MODULE_ID}",
			true, true
		);

		$this->recursiveCopyFiles("admin");
		$this->recursiveCopyFiles("tools");

		// Скопировать компоненты
		CopyDirFiles(
			__DIR__ . "/components",
			"{$documentRoot}/bitrix/components/{$this->MODULE_ID}",
			true, true
		);

		// Создать симлинк на компоненты модуля в папке /local/components/
		$localComponentsFolder = "/local/components";
		$localComponentsPath = Application::getDocumentRoot() . "$localComponentsFolder";
		$thisComponentsPath = "$localComponentsPath/$this->MODULE_ID";

		$moduleComponentsSrc = getLocalPath("modules/$this->MODULE_ID/install/components");
		$moduleComponentsPath = Application::getDocumentRoot() . $moduleComponentsSrc;

		if (DIRECTORY_SEPARATOR === "/" && !file_exists($thisComponentsPath) && file_exists($moduleComponentsPath)) {
			exec(implode(" && ", [
				"mkdir -p " . $localComponentsPath,
				"cd " . $localComponentsPath,
				"ln -s ../..$moduleComponentsSrc $this->MODULE_ID",
			]));
		}

		return true;
	}

	public function UnInstallFiles()
	{
		$documentRoot = Application::getInstance()->getContext()->getServer()->getDocumentRoot();

		Directory::deleteDirectory("{$documentRoot}/bitrix/js/{$this->MODULE_ID}");
		Directory::deleteDirectory("{$documentRoot}/bitrix/css/{$this->MODULE_ID}");
		Directory::deleteDirectory("{$documentRoot}/bitrix/images/{$this->MODULE_ID}");
		Directory::deleteDirectory("{$documentRoot}/bitrix/components/{$this->MODULE_ID}");

		$this->recursiveRemoveFiles("admin");
		$this->recursiveRemoveFiles("tools");

		return true;
	}

	public function IsVersionD7()
	{
		return CheckVersion(ModuleManager::getVersion("main"), "14.00.00");
	}

	public function getModulePath() {
		return $_SERVER["DOCUMENT_ROOT"] . getLocalPath("modules/$this->MODULE_ID");
	}

	public function GetPath($notDocumentRoot = false)
	{
		return $notDocumentRoot
			? str_ireplace(Application::getDocumentRoot(), "", dirname(__DIR__))
			: dirname(__DIR__);
	}

	private function recursiveCopyFiles($prefix)
	{
		CopyDirFiles(
			$this->getPath() . "/install/{$prefix}/",
			"{$_SERVER["DOCUMENT_ROOT"]}/bitrix/{$prefix}/",
			false,
			true
		);

		if (Directory::isDirectoryExists($path = $this->getPath() . "/{$prefix}")) {
			if ($dir = opendir($path)) {
				while (false !== $item = readdir($dir)) {
					if (in_array($item, $this->excludeAdminFiles)) {
						continue;
					}
					if (strpos($item, "_") === 0) continue;
					file_put_contents(
						$file =
							"{$_SERVER['DOCUMENT_ROOT']}/bitrix/{$prefix}/" .
							"{$this->MODULE_ID}_{$item}",

						"<" . "?" . PHP_EOL .

						"if (empty(\$" . "_SERVER[\"DOCUMENT_ROOT\"])) {" . PHP_EOL .
						"    " .
						"\$" . "_SERVER[\"DOCUMENT_ROOT\"] = " .
						"realpath(__DIR__ . \"/../..\");" . PHP_EOL .
						"}" . PHP_EOL . PHP_EOL .

						"require(\$" . "_SERVER[\"DOCUMENT_ROOT\"] . \"" .
						getLocalPath("modules/{$this->MODULE_ID}/{$prefix}/{$item}") .
						'");'
					);
				}
				closedir($dir);
			}
		}
	}

	private function recursiveRemoveFiles($prefix)
	{
		DeleteDirFiles(
			$this->getPath() . "/install/{$prefix}/",
			"{$_SERVER["DOCUMENT_ROOT"]}/bitrix/{$prefix}/"
		);

		if (Directory::isDirectoryExists($path = $this->getPath() . "/{$prefix}")) {
			if ($dir = opendir($path)) {
				while (false !== $item = readdir($dir)) {
					if (in_array($item, $this->excludeAdminFiles)) {
						continue;
					}
					\Bitrix\Main\IO\File::deleteFile(
						"{$_SERVER['DOCUMENT_ROOT']}/bitrix/{$prefix}/" .
						"{$this->MODULE_ID}_{$item}"
					);
				}
				closedir($dir);
			}
		}
	}
}