


const profileTrigger = document.getElementById('profileTrigger');
const dropdownMenu = document.getElementById('dropdownMenu');

if (profileTrigger && dropdownMenu) {
    profileTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
    });

    document.addEventListener('click', () => {
        dropdownMenu.classList.remove('show');
    });
}


const mobileMenuTrigger = document.getElementById('mobileMenuTrigger');
const mobileNav = document.getElementById('mobileNav');
const closeMenu = document.getElementById('closeMenu');

if (mobileMenuTrigger && mobileNav) {
    mobileMenuTrigger.onclick = () => {
        mobileNav.classList.add('active');
        document.body.style.overflow = 'hidden';
    };
}

if (closeMenu && mobileNav) {
    closeMenu.onclick = () => {
        mobileNav.classList.remove('active');
        document.body.style.overflow = 'auto';
    };
}




function toggleShare() {
    const shareMenu = document.getElementById('mobileShareMenu');
    if (shareMenu) {
        shareMenu.classList.toggle('active');
    }
}




