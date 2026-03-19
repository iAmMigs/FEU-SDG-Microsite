// public/js/trix-upload.js
document.addEventListener("trix-attachment-add", function(event) {
    if (event.attachment.file) {
        uploadFileAttachment(event.attachment);
    }
});

function uploadFileAttachment(attachment) {
    var file = attachment.file;
    var form = new FormData();
    form.append("file", file);

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/admin/upload-image", true);

    // Show upload progress bar in the editor
    xhr.upload.onprogress = function(event) {
        var progress = event.loaded / event.total * 100;
        attachment.setUploadProgress(progress);
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            // Tell the editor where the permanently saved image is
            attachment.setAttributes({
                url: response.url,
                href: response.url
            });
        } else {
            console.error("Upload failed");
        }
    };

    xhr.send(form);
}