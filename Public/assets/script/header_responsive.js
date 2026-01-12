/**
 * Navigation responsive et menu burger
 *
 * Initialise le bouton burger et gère l'ouverture/fermeture.
 *
 * Supporte l'insertion tardive du DOM via MutationObserver.
 *
 * Gère la fermeture automatique (clic hors nav, touche Escape).
 *
 * @module header_responsive
 * @package Assets
 */
document.addEventListener('DOMContentLoaded', () => {
    const burgerMenu = document.querySelector('.burger-menu');
    const mainNav = document.querySelector('#mainnav');

    if (!burgerMenu || !mainNav) {
        // Si les éléments ne sont pas encore dans le DOM (injection tardive), observer les mutations
        const observer = new MutationObserver((mutations, obs) => {
            const b = document.querySelector('.burger-menu');
            const m = document.querySelector('#mainnav');
            if (b && m) {
                obs.disconnect();
                // Ré-exécuter l'initialisation après insertion
                initBurger(b, m);
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
        return;
    }
    initBurger(burgerMenu, mainNav);
});

function initBurger(burgerMenu, mainNav) {
    // Eviter de lier plusieurs fois les mêmes handlers
    if (burgerMenu.dataset.burgerInit === '1') return;
    burgerMenu.dataset.burgerInit = '1';

    const setExpanded = (expanded) => {
        burgerMenu.classList.toggle('active', expanded);
        mainNav.classList.toggle('active', expanded);
        burgerMenu.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        mainNav.setAttribute('aria-hidden', expanded ? 'false' : 'true');
        document.body.style.overflow = expanded ? 'hidden' : '';
    };

    // Toggle via click direct sur le bouton (support touch)
    burgerMenu.addEventListener('click', (e) => {
        e.stopPropagation();
        setExpanded(!burgerMenu.classList.contains('active'));
    });

    // Fermer le menu lors du clic sur un lien à l'intérieur de la nav
    mainNav.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link) {
            setExpanded(false);
        }
    });

    // Fermer le menu lors du clic en dehors (délégation globale)
    document.addEventListener('click', (e) => {
        if (!mainNav.contains(e.target) && !burgerMenu.contains(e.target) && mainNav.classList.contains('active')) {
            setExpanded(false);
        }
    });

    // Fermer le menu avec la touche Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' || e.key === 'Esc') {
            if (mainNav.classList.contains('active')) setExpanded(false);
        }
    });
}

// Page show handler: quand la page est restaurée depuis le cache (back/forward),
// s'assurer que le menu est dans un état cohérent et réinitialiser les handlers si besoin.
window.addEventListener('pageshow', (event) => {
    const b = document.querySelector('.burger-menu');
    const m = document.querySelector('#mainnav');
    if (!b || !m) return;

    // Si le document a été restauré depuis le bfcache, forcer la réinitialisation de l'état
    // (retirer active/overflow) puis (ré)initialiser les handlers
    try {
        b.classList.remove('active');
        m.classList.remove('active');
        b.setAttribute('aria-expanded', 'false');
        m.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    } catch (err) {
        // ignore
    }

    // Si init non faite (dataset absent), appeler initBurger
    if (b.dataset.burgerInit !== '1') {
        initBurger(b, m);
    }
});
