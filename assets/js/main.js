const profileTrigger = document.getElementById('profileTrigger');
const dropdownMenu = document.getElementById('dropdownMenu');

if (profileTrigger) {
    profileTrigger.addEventListener('click', () => {
        dropdownMenu.classList.toggle('show');
    });
}



const controlMenu = (triggerId, menuId, closeId) => {
        const btn = document.getElementById(triggerId);
        const menu = document.getElementById(menuId);
        const close = document.getElementById(closeId);

        if (btn && menu) {
            btn.onclick = () => {
                menu.classList.add('active');
                document.body.style.overflow = 'hidden';
            };
        }

        if (close && menu) {
            close.onclick = () => {
                menu.classList.remove('active');
                document.body.style.overflow = 'auto';
            };
        }
    };

    controlMenu('mobileMenuTrigger', 'mobileNav', 'closeMenu');
    controlMenu('mobileSearchTrigger', 'mobileSearchBar', 'closeSearch');
    controlMenu('mobileProfileTrigger', 'mobileProfileMenu', 'closeProfile');