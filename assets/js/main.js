
const profileBtn = document.getElementById('profileTrigger');
const dropdown = document.getElementById('dropdownMenu');

if (profileBtn && dropdown) {
    profileBtn.onclick = function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    };
    document.onclick = function() {
        dropdown.classList.remove('show');
    };
}

const openMenu = document.getElementById('mobileMenuTrigger');
const mobileNav = document.getElementById('mobileNav');
const closeBtn = document.getElementById('closeMobileNav');

if (mobileNav) {
    
    const close = function() {
        mobileNav.classList.remove('active');
        document.body.style.overflow = 'auto';
    };

    if (openMenu) {
        openMenu.onclick = function() {
            mobileNav.classList.add('active');
            document.body.style.overflow = 'hidden';
        };
    }

    if (closeBtn) closeBtn.onclick = close;
    
    mobileNav.onclick = function(e) {
        if (e.target === mobileNav) close();
    };
}
