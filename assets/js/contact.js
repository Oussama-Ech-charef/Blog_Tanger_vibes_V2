// FAQ accordion and notification dismiss
document.addEventListener('DOMContentLoaded', function() {
    const items = document.querySelectorAll('.faq_item');
    items.forEach(function(item) {
        const btn = item.querySelector('.faq_question');
        btn.addEventListener('click', function() {
            const isOpen = item.classList.contains('open');
            items.forEach(function(el) { el.classList.remove('open'); });
            if (!isOpen) {
                item.classList.add('open');
            }
        });
    });

    autoDismissPopup('.notification');
});
