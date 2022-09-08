(function() {
    tinymce.PluginManager.add('rrze_campo_shortcode', function(editor) {

    var menuItems = [];
    menuItems.push({
        text: 'Lectures',
        icon: 'paste', 
        menu: [
            {
                type: 'menuitem',
                text: 'Alle',
                onclick: function() {
                    editor.insertContent('[contact task="lectures-all"]');
                }
            },
            {
                type: 'menuitem',
                text: 'Einzelne',
                onclick: function() {
                    editor.insertContent('[contact task="lectures-single" lv_id=""]');
                }
            },
        ]
    });

    editor.addMenuItem('insertShortcodesRRZECampo', {
        icon: 'orientation', 
        text: 'RRZE-Contact',
        menu: menuItems,
        context: 'insert',
    });
});
})();