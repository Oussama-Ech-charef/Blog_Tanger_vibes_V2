document.addEventListener('DOMContentLoaded', function() {
    var items = document.querySelectorAll('.faq_item');
    items.forEach(function(item) {
        var btn = item.querySelector('.faq_question');
        btn.addEventListener('click', function() {
            var isOpen = item.classList.contains('open');
            items.forEach(function(el) { el.classList.remove('open'); });
            if (!isOpen) {
                item.classList.add('open');
            }
        });
    });

    autoDismissPopup('.comment-success-popup');
});
