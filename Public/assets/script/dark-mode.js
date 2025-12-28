/**
 * Script de gestion du mode sombre
 *
 * - Garde le choix (light/dark) dans localStorage.
 * - Applique l'attribut `data-theme` sur l'élément root.
 * - Émet un événement custom `themechange` pour les autres scripts.
 *
 * @module dark-mode
 */

(function() {
    'use strict';
    
    const STORAGE_KEY = 'dashmed-theme';
    const THEME_ATTR = 'data-theme';
    
    // Récupère le thème sauvegardé ou 'light' par défaut
    function getSavedTheme() {
        return localStorage.getItem(STORAGE_KEY) || 'light';
    }
    
    // Sauvegarde le thème dans localStorage
    function saveTheme(theme) {
        localStorage.setItem(STORAGE_KEY, theme);
    }
    
    // Applique le thème au document
    function applyTheme(theme) {
        document.documentElement.setAttribute(THEME_ATTR, theme);
        
            // Met à jour l'aria-label et le title du bouton
        const toggle = document.getElementById('darkModeToggle');
        if (toggle) {
            const label = theme === 'dark' ? 'Activer le mode clair' : 'Activer le mode sombre';
            toggle.setAttribute('aria-label', label);
            toggle.setAttribute('title', theme === 'dark' ? 'Mode clair' : 'Mode sombre');
        }
    }
    
    // Basculer entre light et dark
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute(THEME_ATTR) || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        applyTheme(newTheme);
        saveTheme(newTheme);
        
        // Déclenche un événement custom pour que d'autres scripts puissent réagir
        window.dispatchEvent(new CustomEvent('themechange', { 
            detail: { theme: newTheme } 
        }));
    }
    
    // Initialisation du thème au chargement de la page
    function initTheme() {
        const savedTheme = getSavedTheme();
        applyTheme(savedTheme);
    }
    
    // Configuration du bouton de bascule
    function setupToggle() {
        const toggle = document.getElementById('darkModeToggle');
        if (toggle) {
            toggle.addEventListener('click', toggleTheme);
        }
    }
    
    // Exécution immédiate pour éviter un flash visuel lors du chargement
    initTheme();
    
    // Setup toggle when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupToggle);
    } else {
        setupToggle();
    }
    
})();
