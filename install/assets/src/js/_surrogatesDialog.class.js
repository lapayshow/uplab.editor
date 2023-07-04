export default class UplabSurrogatesDialog {

    constructor(_editor, _params, _tag) {
        this._editor = _editor;
        this._params = _params;

        // если мы передали тег в констуртор то получаем параметры тега
        // там у нас в json хранится содеражание всего аккордеона
        this._tag = _tag || {};
        this._tag.params = this._tag.params || {};
        this._tag.params.data = this._tag.params.data || {};
        this._tag.params.data.attributes = this._tag.params.attributes || {};
        this.data = this._tag.params.data || {};

        this.initDialog();
    }

    initDialog() {
        const self = this;
        let html;

        self._dialog = new BX.CDialog({ //Инициализаци окна добавления/редактирования аккордеона
            title: self._params.dialogTitle,
            // min_width: 400,
            // min_height: 400,
            icon: 'head-block',
            resizable: false,
            width: 500,
            height: 310,
            content_url: self._params.getDialogUrl(self.data.attributes),
            buttons: [
                // Кнопки в окне

                { // Кнопка применяет изменения или добавляет аккордеон
                    title: "OK",
                    name: 'saveUplabSurrogate',
                    className: 'adm-btn-save',
                    id: 'saveUplabSurrogate',
                    action: function () {
                        var _thisBtn = this; // Сама кнопка
                        html = self.getSnippetHTML(); //Метод получает html аккордеона

                        if (self._tag.params.html) {
                            self._tag.params.html = html;  // заменить конетент, если сниппет уже есть,
                        } else {
                            console.log('insert', html);
                            self._editor.selection.InsertHTML(html); // вставить контент в редактор
                        }

                        setTimeout(function () {
                            // Спустя немного времени обновляем содержииме
                            // чтобы применился новый аккордеон
                            self._editor.synchro.FullSyncFromIframe();
                        }, 50);

                        this.parentWindow.Close(); //Закрываем окно
                        $(this.parentWindow.DIV).remove(); //Удаляем окно из DOM-a
                    }
                },

                { //Кнопка добавляем блок для редактирования элемента аккордеона
                    title: "Еще элемент",
                    className: "adm-btn-add",
                    action: function () {
                        const $form = $('.uplab-surrogate-editor-form');
                        // console.log('array: ', $form.serializeArray());
                        $.get(
                            uplabEditorSnippetsParam.dialogUrl + '?ADD',
                            $form.serializeArray(),
                            function (res) {
                                $form.parent().html(res);
                            }
                        );
                    }
                },

                { //Отмена
                    title: BX.message('JS_CORE_WINDOW_CANCEL'),
                    id: 'cancel',
                    name: 'cancel',
                    action: function () {
                        this.parentWindow.Close(); //Закрываем окно
                        $(this.parentWindow.DIV).remove(); //Удаляем DOM окна
                    }
                }
            ]
        });
    }

    getContent() {
        // Метотд получает сожержимое окна редактора аккордеона
        // Заменено на ссылку
    }

    refreshContent() { //Метотд обновляет содержимое окна редактора аккордеона
        const self = this;
        self._dialog.SetContent(self.getContent());
    }

    getSnippetHTML() { //Метотд получает html аккордеона по данным из this.data
        const self = this;
        let html = self._params.htmlTpl;
        let formData = $('.uplab-surrogate-editor-form').serializeArray();
        let data = {};
        let values = {};

        $.each(formData, function (key, value) {
            let name = value.name.match(/((.+)\[([\w\d_-]*)]|.+)/);

            if (!value.value) return;

            if (name[1] === 'TYPE') {

                data[name[1]] = value.value;

            } else {

                if (name[2]) {
                    if (name[3]) {
                        values[name[2]][name[3]] = value.value;
                    } else {
                        values[name[2]] = values[name[2]] || [];
                        values[name[2]].push(value.value);
                    }
                } else {
                    values[name[1]] = value.value;
                }

            }
            // console.log(name, name[2], name[3]);
        });

        // console.log(data, 'FORM!');

        html = html.replace(/#TYPE#/g, data['TYPE'] || '');
        html = html.replace(/#VALUES#/g, JSON.stringify(values || ''));

        // console.log('html', html);

        return html;
    }

    show() { //Метотд показывает окно редактора аккордеона
        this._dialog.Show();
    }

}
