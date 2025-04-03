/**
 * Sticky App Banner Admin JavaScript
 */
jQuery(document).ready(function($) {
    // Initialize color picker
    $('.color-picker').wpColorPicker();
    
    // Media uploader for app icon
    var mediaUploader;
    
    $('#upload_app_icon_button').on('click', function(e) {
        e.preventDefault();
        
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Create the media uploader
        mediaUploader = wp.media({
            title: 'Select App Icon',
            button: {
                text: 'Use This Icon'
            },
            multiple: false // Set to true if you want to allow multiple selections
        });
        
        // When an image is selected, run a callback
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#app_icon_id').val(attachment.id);
            $('#app-icon-preview').attr('src', attachment.url).show();
            $('#remove_app_icon_button').show();
        });
        
        // Open the uploader dialog
        mediaUploader.open();
    });
    
    // Remove image button
    $('#remove_app_icon_button').on('click', function(e) {
        e.preventDefault();
        $('#app_icon_id').val('');
        $('#app-icon-preview').attr('src', '').hide();
        $(this).hide();
    });
});
