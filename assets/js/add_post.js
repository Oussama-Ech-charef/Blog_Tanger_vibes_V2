document.addEventListener('DOMContentLoaded', function() {
    var imageInput = document.getElementById('image');
    if (!imageInput) return;

    imageInput.addEventListener('change', function(e) {
        var preview = document.getElementById('image_preview');
        var file = e.target.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                preview.innerHTML = '<p class="preview_error">Please select an image file.</p>';
                return;
            }
            var reader = new FileReader();
            reader.onload = function(ev) {
                preview.innerHTML = '<img src="' + ev.target.result + '" alt="Preview">';
            };
            reader.readAsDataURL(file);
        }
    });
});
