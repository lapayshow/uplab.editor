<?php
/**
 * Created by PhpStorm.
 * User: geffest
 * Date: 14.09.2018
 * Time: 11:00
 */

namespace Uplab\Editor;


class SimpleCode
{

	public static function addElementSearchButton()
	{
		?>
		<script>
            BX.addCustomEvent('OnEditorInitedBefore', function (editor) {

                window.selectElement = function (elementId, iblockId) {
                    editor.InsertHtml('[ELEMENT IBLOCK=' + iblockId + ']' + elementId + '[/ELEMENT]', editor.selection.GetRange())
                };

                this.AddButton({
                    src: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAABqklEQVQ4T2NkoDJgpLJ5DGADlxbNeyHJxiyKzfDPf/4z3uZhZGQQZmH4ysbwD5cD2N/9eVZRGS8LNnBx8fxvfrzMnNgUP/v9n2GLJAvDXw02hifc/3F6SOb8z7cVWbEiYAMXlM774Mv8mx+b6ud/GRkWyrMwvNDAbRhIn+F1lodFufEKw8TAA/xsDBySzHgTxJ+Xf+9mVCWoEOXl90a8DGY27HgN3LHh60H/3FgH2hloyvAHayy/+8vIwGDKQ5oLJzes+vDZ2AKrgUzv3jPIMbxgCHb6SryXQQZ+8vbDaiDji1cMCu+uD0cDP9s58//n5WZgfPueAUSDAOPP3wwMX7+CvWyn/YlBVJyZ4dMHSPnAJ8AEZ7NzMDLs3/ENkWwmNKz5/FZFneeXuAgD1807DN8V5cGaWN9/ZGDg5WGwfH+SgfX3NwYbm/8MN65DSjwNzf8Mp08xMvDyMjAIi/xnuHSZ9aRPdpwFWLanecOLb+JiWIsvli/fGGW/P/4vK/3vF75ofvaE4U5UTbIuvIDtyppvwMjEIEBOgfv/H8OHsmmJF8DBRI4B+PQAABcX7hXIDmSvAAAAAElFTkSuQmCC',
                    id: 'add-item',
                    name: 'Привязать элемент',
                    title: 'Привязать элемент',
                    toolbarSort: 1,
                    handler: function () {
                        jsUtils.OpenWindow('/bitrix/tools/uplab.editor_element_search.php', 900, 700);
                    },
                });

            });
		</script>
		<?
	}

	/**
	 * TODO
	 * добавить скрипт uplab_section_search.php для привязки разделов
	 * и зарегистрировать обработчик в install/index.php
	 */
	public static function addSectionSearchButton()
	{
		?>
		<script>
            BX.addCustomEvent('OnEditorInitedBefore', function (editor) {

                window.selectSection = function (sectionId, iblockId) {
                    editor.InsertHtml('[SECTION IBLOCK=' + iblockId + ']' + sectionId + '[/SECTION]', editor.selection.GetRange())
                };

                this.AddButton({
                    src: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAABqklEQVQ4T2NkoDJgpLJ5DGADlxbNeyHJxiyKzfDPf/4z3uZhZGQQZmH4ysbwD5cD2N/9eVZRGS8LNnBx8fxvfrzMnNgUP/v9n2GLJAvDXw02hifc/3F6SOb8z7cVWbEiYAMXlM774Mv8mx+b6ud/GRkWyrMwvNDAbRhIn+F1lodFufEKw8TAA/xsDBySzHgTxJ+Xf+9mVCWoEOXl90a8DGY27HgN3LHh60H/3FgH2hloyvAHayy/+8vIwGDKQ5oLJzes+vDZ2AKrgUzv3jPIMbxgCHb6SryXQQZ+8vbDaiDji1cMCu+uD0cDP9s58//n5WZgfPueAUSDAOPP3wwMX7+CvWyn/YlBVJyZ4dMHSPnAJ8AEZ7NzMDLs3/ENkWwmNKz5/FZFneeXuAgD1807DN8V5cGaWN9/ZGDg5WGwfH+SgfX3NwYbm/8MN65DSjwNzf8Mp08xMvDyMjAIi/xnuHSZ9aRPdpwFWLanecOLb+JiWIsvli/fGGW/P/4vK/3vF75ofvaE4U5UTbIuvIDtyppvwMjEIEBOgfv/H8OHsmmJF8DBRI4B+PQAABcX7hXIDmSvAAAAAElFTkSuQmCC',
                    id: 'add-item',
                    name: 'Привязать раздел',
                    title: 'Привязать раздел',
                    toolbarSort: 1,
                    handler: function () {
                        jsUtils.OpenWindow('/bitrix/tools/uplab.editor_section_search.php', 900, 700);
                    },
                });

            });
		</script>
		<?
	}

	public static function removeCodesFromIndex(&$arFields)
	{
		if ($arFields["MODULE_ID"] == "iblock") {
			$arFields["BODY"] = preg_replace(
				"/.*\[ELEMENT IBLOCK=([0-9])+\][\s]*([0-9]+)[\s]*\[\/ELEMENT\].*/",
				"",
				$arFields["BODY"]
			);
			$arFields["BODY"] = preg_replace(
				"/.*\[SECTION IBLOCK=([0-9])+\][\s]*([0-9]+)[\s]*\[\/SECTION\].*/",
				"",
				$arFields["BODY"]
			);
		}
	}

}