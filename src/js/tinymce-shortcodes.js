(function() {

    tinymce.PluginManager.add('contactrteshortcodes', function( editor )
    {
        
        editor.addMenuItem('shortcode_contact', {
            text: 'Add contact',
            context: 'tools',
            onclick: function() {
                editor.insertContent('[contact id="" format="" show="" hide=""]');
            }
        });
        
        editor.addMenuItem('shortcode_contacts', {
            text: 'Insert people gallery',
            context: 'tools',
            onclick: function() {
                editor.insertContent('[contact category="" format="" show="" hide=""]');
            }
        });
            });
})();