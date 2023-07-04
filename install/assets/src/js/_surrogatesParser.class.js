import UplabSurrogatesDialog from "./_surrogatesDialog.class";

export default class UplabSurrogatesParser {
    constructor(params, editor) {
        this._params = params;
        this._editor = editor;

        this.initEditorHandler();
    }

    initEditorHandler() {
        const self = this;

        self.addEditorParser();
        self.addButtons();
        self.surrogatesHandlers();
    }

    addEditorParser() {
        const self = this;

        self._editor.AddCustomParser(function (content) {
            // А а это событие которое парсит содержимое и добавляет суррогаты
            const parser = self._editor.phpParser; //Переменная  в которой хранится парсер виз. редактора
            let index = 0;

            content = content || '';

            content = content.replace(
                self._params.parse.regex,
                function (str) {
                    const _args = arguments;
                    let res;
                    let title;
                    let item = {};

                    // console.log('arguments', arguments);

                    $.each(self._params.parse.attrs, function (i, attrKey) {
                        let param = _args[i + 1];

                        if (attrKey === 'VALUES') {
                            param = JSON.parse(param);
                        }

                        item[attrKey] = param;
                    });

                    if (item.hasOwnProperty('VALUES') && item.VALUES) {
                        if (item.VALUES.TEMPLATE) item.TEMPLATE = item.VALUES.TEMPLATE;
                        if (item.VALUES.ID) item.ID = item.VALUES.ID;
                    }

                    title = self._params.surrogate.getTitle(item);

                    res = self._editor.phpParser.GetSurrogateHTML(
                        self._params.surrogate.code, title, title,
                        {
                            html: str,
                            attributes: item
                        }
                    );


                    return res || str;
                }
            );

            return content; //Возвращаем новое содержимое виз. редактора
        });
    }

    addButtons() {
        const self = this;

        //Метотд для добавления кнопки на панель инструментов редактора
        self._editor.AddButton({
            iconClassName: 'bxhtmled-button bx-plane-button',
            src: '/bitrix/images/uplab.editor/i-tools.svg',
            id: 'typograf',
            title: 'typograf',
            toolbarSort: 1,
            //action: "insertAnchor"
            handler: function (event) {
                const _dialog = new UplabSurrogatesDialog(self._editor, self._params);
                _dialog.show();
            }
        });
    }

    surrogatesHandlers() {
        const self = this;

        BX.addCustomEvent('OnGetBxNodeList', function (e, t) {
            const parser = this.phpParser;
            parser.arBxNodes.up_edit_snippet = {
                Parse: function (params) {
                    return parser._GetUnParsedContent(params.html);
                }
            };
        });

        BX.addCustomEvent('OnSurrogateDblClick', function (e, t) { // Событие на двойной клик по суррогату
            let html = "";

            if (t.tag !== 'up_edit_snippet') return;

            if (typeof self._editor.bxTags[t.id] !== 'undefined') {
                const _dialog = new UplabSurrogatesDialog(self._editor, self._params, t);
                _dialog.show();
            }
        });
    }
}
