jQuery(document).ready(function($) {
 var logoField = $('#woocommerce_piprapay_logo_url');
 if (logoField.length) {
     var uploadButton = $('<button type="button" class="button">Upload</button>');
     uploadButton.insertAfter(logoField);
     uploadButton.on('click', function(e) {
         e.preventDefault();
         var mediaUploader = wp.media({
             title: 'Select Logo',
             button: { text: 'Use this image' },
             multiple: false
         }).on('select', function() {
             var attachment = mediaUploader.state().get('selection').first().toJSON();
             logoField.val(attachment.url);
         }).open();
     });
 }
});