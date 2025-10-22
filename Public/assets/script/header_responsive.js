const burgerMenu = document.querySelector('.burger-menu');
const mainNav = document.querySelector('.mainnav');

burgerMenu.addEventListener('click', () => {
    burgerMenu.classList.toggle('active');
    mainNav.classList.toggle('active');

    const isExpanded = burgerMenu.classList.contains('active');
    burgerMenu.setAttribute('aria-expanded', isExpanded);

    // Empêcher le scroll du body quand le menu est ouvert
    document.body.style.overflow = isExpanded ? 'hidden' : '';
});

// Fermer le menu lors du clic sur un lien
const navLinks = mainNav.querySelectorAll('a');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        burgerMenu.classList.remove('active');
        mainNav.classList.remove('active');
        burgerMenu.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    });
});

// Fermer le menu lors du clic en dehors
document.addEventListener('click', (e) => {
    if (!mainNav.contains(e.target) && !burgerMenu.contains(e.target) && mainNav.classList.contains('active')) {
        burgerMenu.classList.remove('active');
        mainNav.classList.remove('active');
        burgerMenu.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }
});