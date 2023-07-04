<?

namespace Uplab\Editor\Module;


use Uplab\Core;


class Options extends Core\Module\OptionsBase
{
	public $moduleId = "uplab.editor";

	public function onPostEvents()
	{
		parent::onPostEvents();
		$this->updateModuleFiles();
	}

}