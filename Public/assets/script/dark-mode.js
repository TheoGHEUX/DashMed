/**
 * Dark Mode Toggle Script
 * Manages dark mode state with localStorage persistence
 */

(function() {
    'use strict';
    
    const STORAGE_KEY = 'dashmed-theme';
    const THEME_ATTR = 'data-theme';
    
    // Get saved theme or default to light
    function getSavedTheme() {
        return localStorage.getItem(STORAGE_KEY) || 'light';
    }
    
    // Save theme to localStorage
    function saveTheme(theme) {
        localStorage.setItem(STORAGE_KEY, theme);
    }
    
    // Apply theme to document
    function applyTheme(theme) {
        document.documentElement.setAttribute(THEME_ATTR, theme);
        
        // Update button aria-label
        const toggle = document.getElementById('darkModeToggle');
        if (toggle) {
            const label = theme === 'dark' ? 'Activer le mode clair' : 'Activer le mode sombre';
            toggle.setAttribute('aria-label', label);
            toggle.setAttribute('title', theme === 'dark' ? 'Mode clair' : 'Mode sombre');
        }
    }
    
    // Toggle between light and dark
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute(THEME_ATTR) || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        applyTheme(newTheme);
        saveTheme(newTheme);
        
        // Trigger custom event for other scripts to react
        window.dispatchEvent(new CustomEvent('themechange', { 
            detail: { theme: newTheme } 
        }));
    }
    
    // Initialize theme on page load
    function initTheme() {
        const savedTheme = getSavedTheme();
        applyTheme(savedTheme);
    }
    
    // Setup toggle button
    function setupToggle() {
        const toggle = document.getElementById('darkModeToggle');
        if (toggle) {
            toggle.addEventListener('click', toggleTheme);
        }
    }
    
    // Run immediately to prevent flash
    initTheme();
    
    // Setup toggle when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupToggle);
    } else {
        setupToggle();
    }
    
})();
