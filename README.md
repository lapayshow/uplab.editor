# Uplab.Editor

Модуль расширяет функционал визуального редактора: на панель инструментов добавляется кнопка, позволяющая вставить в любом месте сайта сниппет, использующий выводящий информацию из элемента / раздела инфоблока с помощью одного из заренее заданных шаблонов.
 
## Использование

После установки модуля в визуальном редакторе появляется иконка "гаечного ключа". 
При нажатии на иконку, открывается попап выбора настроек сниппета.

Доступно два сценария использоания:

1. Привязка к сниппету одного или нескольких элементов инфоблока (используется компонент uplab.editor:snippet.element)
2. Привзка одного или нескольких разделов инфоблока (uplab.editor:snippet.section)

Чтобы создать сниппет, необходимо создать шаблон соответствующего компонента (в папке шаблона сайта /local/templates/.default/). Важно, создавать шаблоны компонентов именно в дефолтном шаблоне сайта, так как попап с выбором параметров сниппета грузится в административной части, а не в публичной и информации о существующих в публичной части шаблонах там нет.

В шаблонах в $arResult доступна информация о соответствующих элементах. Если необходимо дополнить выборку, это можно сделать, используя файл .description.php в шаблоне (см исходный код компонента для получения дополнительной информации).

После того, как шаблоны будут созданы, в окне привязки сниппета необходимо будет выбрать элемент(ы)/раздел(ы) для привязки и соответствующий шаблон компонента. После добавления сниппета, результат работы компонента появится на странице вместо кода сниппета.

# ВНИМАНИЕ! Если на сайте использовалась версия 1.0, переключитесь на ветку v1!


## Версия 2.0 несовместима с более ранними версиями! Для перевода уже рабочего сайта потребуется внести ряд изменений!
