(function () {
    var uploadZone = document.getElementById('uploadZone');
    var imageInput = document.getElementById('image');
    var previewContainer = document.getElementById('uploadPreview');
    var previewImage = document.getElementById('previewImage');
    var uploadPlaceholder = document.getElementById('uploadPlaceholder');
    var uploadRemove = document.getElementById('uploadRemove');
    var imageInfo = document.getElementById('imageInfo');
    var statusSelect = document.getElementById('status');

    var sidebarPreview = document.getElementById('sidebarPreview');
    var sidebarPlaceholder = document.getElementById('sidebarPlaceholder');

    var currentFile = null;

    // Upload preview

    function showPreview(file) {
        if (!file) return;
        currentFile = file;
        var reader = new FileReader();
        reader.onload = function (e) {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
            uploadPlaceholder.style.display = 'none';
            uploadZone.classList.add('has_image');
            imageInfo.textContent = file.name + ' (' + formatSize(file.size) + ')';

            if (sidebarPreview) {
                sidebarPreview.src = e.target.result;
                sidebarPreview.classList.add('show');
            }
            if (sidebarPlaceholder) {
                sidebarPlaceholder.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    }

    function resetUpload() {
        currentFile = null;
        previewContainer.style.display = 'none';
        uploadPlaceholder.style.display = 'flex';
        uploadZone.classList.remove('has_image');
        imageInput.value = '';
        if (sidebarPreview) {
            sidebarPreview.classList.remove('show');
            sidebarPreview.src = '';
        }
        if (sidebarPlaceholder) {
            sidebarPlaceholder.style.display = 'flex';
        }
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                showPreview(this.files[0]);
            }
        });
    }

    if (uploadRemove) {
        uploadRemove.addEventListener('click', function (e) {
            e.stopPropagation();
            resetUpload();
        });
    }

    if (uploadZone) {
        uploadZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', function () {
            this.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                var file = e.dataTransfer.files[0];
                if (file.type.match('image.*')) {
                    imageInput.files = e.dataTransfer.files;
                    showPreview(file);
                }
            }
        });
    }

    // Sidebar image upload sync

    if (sidebarPlaceholder) {
        sidebarPlaceholder.addEventListener('click', function () {
            imageInput.click();
        });
    }

    // Status button handling

    document.querySelectorAll('[data-set-status]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (statusSelect) {
                statusSelect.value = this.getAttribute('data-set-status');
            }
        });
    });
})();
