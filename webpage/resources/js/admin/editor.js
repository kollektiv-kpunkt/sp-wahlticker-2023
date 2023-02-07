import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';


if (document.querySelector(".ticker-admin-announcement-form")) {
    const editor = new EditorJS({
        /**
         * Id of Element that should contain Editor instance
         */
        holder: 'editor',
        placeholder: 'Hier klicken und Inhalt schreiben...',
        inlineToolbar: true,
        tools: {
            header: Header,
            list: List,
        },
        onChange: (event) => {
            editor.save().then((outputData) => {
                document.querySelector("#content").value = JSON.stringify(outputData);
            })
        },
    });

    if (document.querySelector("#editor-existing")) {
        editor.isReady.then(() => {
            editor.blocks.renderFromHTML(document.querySelector("#editor-existing").innerHTML)
            editor.save().then((outputData) => {
                document.querySelector("#content").value = JSON.stringify(outputData);
            })
        });
    }
}

