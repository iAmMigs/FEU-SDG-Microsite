document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.tinymce-editor',
            height: 600,
            plugins: 'image link lists media table code wordcount emoticons autoresize',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image emoticons | code',
            
            // Image Upload Settings
            images_upload_url: '/admin/upload-image',
            automatic_uploads: true,
            
            // Visual behavior
            menubar: false,
            statusbar: true,
            branding: false,
            promotion: false,
            
            setup: function (editor) {
                /**
                 * CRITICAL FIX: Forces TinyMCE to sync the visual "Word-like" content
                 * to the hidden HTML field every time the user types or clicks a button.
                 * This prevents the "null" value error on save.
                 */
                editor.on('change keyup', function () {
                    editor.save(); 
                });
            },

            // Dark/Light mode detection
            skin: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oxide-dark' : 'oxide'),
            content_css: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'default'),

            image_class_list: [
                {title: 'Responsive (Full Width)', value: 'w-full h-auto rounded-2xl shadow-lg my-10 block mx-auto'}
            ]
        });
    }
});