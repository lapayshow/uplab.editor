import UplabSurrogatesParser from "./_surrogatesParser.class";


window.uplabEditorSnippetsParam = {};


uplabEditorSnippetsParam.parse = {
    regex: /\[UP_EDITOR_SNIPPET TYPE="([#\w\s._-]*)"](.*?)\[\/UP_EDITOR_SNIPPET]/g,
    attrs: ['TYPE', 'VALUES'],
};


uplabEditorSnippetsParam.htmlTpl = '[UP_EDITOR_SNIPPET TYPE="#TYPE#"]#VALUES#[/UP_EDITOR_SNIPPET]';


uplabEditorSnippetsParam.surrogate = {
    code: 'up_edit_snippet',
    getTitle: function (v) {
        let t = 'Сниппет [#TYPE##ID##TEMPLATE#]';

        t = t.replace(/#TYPE#/, v.TYPE);

        t = t.replace(/#TEMPLATE#/, v.hasOwnProperty('TEMPLATE') && v.TEMPLATE
            ? ` / ${v.TEMPLATE}`
            : ''
        );

        t = t.replace(/#ID#/, v.hasOwnProperty('ID') && v.ID
            ? `#${v.ID}`
            : ''
        );

        return t;
    }
};


uplabEditorSnippetsParam.dialogUrl = '/bitrix/tools/uplab.editor_surrogates_popup.php';


uplabEditorSnippetsParam.getDialogUrl = function (v) {
    let t = uplabEditorSnippetsParam.dialogUrl + '?TYPE=#TYPE#&#VALUES#';

    t = t.replace(/#TYPE#/, encodeURIComponent(v.TYPE || ''));
    t = t.replace(/#VALUES#/, $.param(v.VALUES || {}));

    return t;
};


uplabEditorSnippetsParam.dialogTitle = '[Uplab.Editor] Сниппеты';


//Событие до иницализации самого редактора, тут мы и будем писать
BX.addCustomEvent('OnEditorInitedBefore', function () {
    // Загоняем обьект редактора в переменную чтобы в любой момент получить к нему доступ
    const _editor = this;

    new UplabSurrogatesParser(
        uplabEditorSnippetsParam,
        _editor
    );
});


// noinspection JSValidateTypes
BX.ready(function () {
    // noinspection JSUnresolvedFunction
    $(document).on('change', '.uplab-surrogate-editor-form [data-form-input]', function () {
        const $form = $(this).closest('form');
        console.log('array: ', $form.serializeArray());

        $.get(
            uplabEditorSnippetsParam.dialogUrl,
            $form.serializeArray(),
            function (res) {
                $form.parent().html(res);
            }
        );
    });
});
