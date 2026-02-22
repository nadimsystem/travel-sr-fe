

(function() {
    'use strict';


    const criticalStyles = `
        /* Loading Optimizer Critical CSS */
        [v-cloak], .v-cloak-hidden { 
            display: none !important; 
        }
        
        body:not(.sr-app-ready) #app {
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }
        
        body.sr-app-ready #app {
            opacity: 1;
        }
        
        /* Skeleton Loading */
        .sr-skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: sr-skeleton-pulse 1.5s ease-in-out infinite;
            border-radius: 0.5rem;
        }
        
        @keyframes sr-skeleton-pulse {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        .dark .sr-skeleton {
            background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
            background-size: 200% 100%;
        }
        
        /* Loading Spinner */
        .sr-loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            display: none;
        }
        
        body:not(.sr-app-ready) .sr-loading-spinner {
            display: block;
        }
        
        .sr-spinner-ring {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(37, 99, 235, 0.1);
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: sr-spin 0.8s linear infinite;
        }
        
        @keyframes sr-spin {
            to { transform: rotate(360deg); }
        }
        
        /* Hide error popups during loading */
        body:not(.sr-app-ready) .swal2-container,
        body:not(.sr-app-ready) [role="dialog"],
        body:not(.sr-app-ready) .modal {
            display: none !important;
        }
    `;
    

    const styleTag = document.createElement('style');
    styleTag.id = 'sr-loading-optimizer-styles';
    styleTag.textContent = criticalStyles;
    document.head.insertBefore(styleTag, document.head.firstChild);
    
 
    
    const RESOURCES_TO_PRELOAD = {
        fonts: [
            'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap'
        ],
        scripts: [
            'https://cdn.tailwindcss.com',
            'https://unpkg.com/vue@3/dist/vue.global.js',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11'
        ],
        styles: [
            'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css'
        ]
    };
    
    function preloadResource(url, type) {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.href = url;
        
        if (type === 'font') {
            link.as = 'style';
            link.onload = function() { this.rel = 'stylesheet'; };
        } else if (type === 'script') {
            link.as = 'script';
        } else if (type === 'style') {
            link.as = 'style';
        }
        
        document.head.appendChild(link);
    }
    

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPreload);
    } else {
        initPreload();
    }
    
    function initPreload() {
        // Preload fonts
        RESOURCES_TO_PRELOAD.fonts.forEach(url => preloadResource(url, 'font'));
        
        // Store in cache for next visit
        if ('caches' in window) {
            caches.open('sr-v12-resources').then(cache => {
                cache.addAll([
                    ...RESOURCES_TO_PRELOAD.scripts,
                    ...RESOURCES_TO_PRELOAD.styles,
                    ...RESOURCES_TO_PRELOAD.fonts
                ].filter(url => !url.includes('tailwindcss'))); // Skip CDN scripts
            }).catch(() => {}); // Silent fail
        }
    }
    
    

    
    let appReadyChecks = 0;
    const MAX_CHECKS = 50; 
    
    function checkAppReady() {
        appReadyChecks++;
        

        const appElement = document.getElementById('app');
        const vueInstance = appElement && appElement.__vue_app__;
        
        if (vueInstance || appReadyChecks >= MAX_CHECKS) {

            setTimeout(() => {
                document.body.classList.add('sr-app-ready');
                
               
                setTimeout(() => {
                    const spinner = document.querySelector('.sr-loading-spinner');
                    if (spinner) spinner.remove();
                }, 300);
            }, 100);
            
        } else {
            // Keep checking
            setTimeout(checkAppReady, 100);
        }
    }
    

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            // Add loading spinner
            addLoadingSpinner();
            setTimeout(checkAppReady, 50);
        });
    } else {
        addLoadingSpinner();
        setTimeout(checkAppReady, 50);
    }
    
    function addLoadingSpinner() {
        const spinner = document.createElement('div');
        spinner.className = 'sr-loading-spinner';
        spinner.innerHTML = '<div class="sr-spinner-ring"></div>';
        document.body.appendChild(spinner);
    }

    window.SRPageTransition = {
        navigate: function(url) {
            // Fade out current page
            const app = document.getElementById('app');
            if (app) {
                app.style.opacity = '0';
                setTimeout(() => {
                    window.location.href = url;
                }, 200);
            } else {
                window.location.href = url;
            }
        }
    };
    
    
    // ============================================
    // 5. ERROR PREVENTION
    // ============================================
    
    // Suppress console errors during initial load (optional)
    let originalConsoleError;
    const errorBuffer = [];
    
    function suppressInitialErrors() {
        originalConsoleError = console.error;
        console.error = function(...args) {
            if (!document.body.classList.contains('sr-app-ready')) {
                // Buffer errors instead of showing them
                errorBuffer.push(args);
            } else {
                originalConsoleError.apply(console, args);
            }
        };
    }
    
    function restoreConsoleErrors() {
        if (originalConsoleError) {
            console.error = originalConsoleError;
            // Optionally replay buffered errors
            // errorBuffer.forEach(args => console.error(...args));
        }
    }
    
    // Uncomment to enable error suppression during load
    // suppressInitialErrors();
    // document.addEventListener('DOMContentLoaded', () => {
    //     setTimeout(restoreConsoleErrors, 2000);
    // });
    
    
    // ============================================
    // 6. PERFORMANCE MONITORING (DEV ONLY)
    // ============================================
    
    if (window.location.hostname === 'localhost' || window.location.hostname.includes('127.0.0.1')) {
        window.addEventListener('load', () => {
            if (window.performance && window.performance.timing) {
                const timing = window.performance.timing;
                const loadTime = timing.loadEventEnd - timing.navigationStart;
                const domReady = timing.domContentLoadedEventEnd - timing.navigationStart;
                
                console.log('%c[SR Loading Optimizer] Performance Stats', 'color: #2563eb; font-weight: bold;');
                console.log(`  DOM Ready: ${domReady}ms`);
                console.log(`  Full Load: ${loadTime}ms`);
                console.log(`  App Ready: ${appReadyChecks * 100}ms (${appReadyChecks} checks)`);
            }
        });
    }
    
    
    // ============================================
    // 7. CACHE MANAGEMENT
    // ============================================
    
    window.SRCacheManager = {
        clear: function() {
            if ('caches' in window) {
                caches.delete('sr-v12-resources').then(() => {
                    console.log('[SR] Cache cleared');
                    location.reload();
                });
            } else {
                location.reload();
            }
        },
        
        version: '12.12'
    };
    
    // Auto-clear cache if version mismatch
    const CACHE_VERSION_KEY = 'sr_cache_version';
    const currentVersion = '12.12';
    const storedVersion = localStorage.getItem(CACHE_VERSION_KEY);
    
    if (storedVersion && storedVersion !== currentVersion) {
        // Version changed, clear cache
        if ('caches' in window) {
            caches.delete('sr-v12-resources');
        }
        localStorage.setItem(CACHE_VERSION_KEY, currentVersion);
    } else if (!storedVersion) {
        localStorage.setItem(CACHE_VERSION_KEY, currentVersion);
    }
    
})();
