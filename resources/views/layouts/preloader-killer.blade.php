{{-- Solución definitiva para eliminar preloader Y PACE.JS --}}
<style>
    /* Ocultar TODOS los loaders posibles */
    .preloader,
    .preloader *,
    [class*="preloader"],
    [class*="loader"],
    [class*="loading"],
    .fa-chevron-left:before,
    .wrapper.loading .preloader,
    /* PACE.JS - EL CULPABLE DE LAS FLECHAS */
    .pace,
    .pace *,
    .pace-progress,
    .pace-activity,
    .pace-inactive,
    .pace-active,
    .pace-running,
    .pace-done,
    [class*="pace"] {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        height: 0 !important;
        width: 0 !important;
        position: absolute !important;
        left: -9999px !important;
        top: -9999px !important;
        z-index: -9999 !important;
    }
    
    /* Ocultar CUALQUIER SVG grande que aparezca */
    svg {
        max-width: 24px !important;
        max-height: 24px !important;
    }
    
    /* Si hay un SVG muy grande en el body, ocultarlo */
    body > svg,
    .wrapper > svg,
    .content-wrapper > svg,
    .content > svg {
        display: none !important;
    }
    
    /* Ocultar elementos absolutamente posicionados muy grandes */
    [style*="position: absolute"][style*="width"][style*="height"] {
        max-width: 100px !important;
        max-height: 100px !important;
    }
    
    /* Forzar visibilidad del contenido */
    body, .wrapper, .content-wrapper, .content {
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    body {
        overflow-y: auto !important;
    }
</style>

<script>
// Ejecutar INMEDIATAMENTE antes de que se renderice nada
(function() {
    'use strict';
    
    // Función ultra agresiva de eliminación
    function nukePreloader() {
        // Eliminar por clase (incluye PACE.JS)
        var selectors = [
            '.preloader',
            '[class*="preloader"]',
            '[class*="loader"]',
            '[class*="loading"]',
            '.pace',
            '.pace-progress',
            '.pace-activity',
            '[class*="pace"]'
        ];
        
        selectors.forEach(function(selector) {
            try {
                var elements = document.querySelectorAll(selector);
                elements.forEach(function(el) {
                    if (el && el.parentNode) {
                        el.parentNode.removeChild(el);
                    }
                });
            } catch(e) {}
        });
        
        // Forzar visibilidad del body
        if (document.body) {
            document.body.style.overflow = 'visible';
            document.body.classList.remove('sidebar-collapse');
        }
        
        // Forzar visibilidad del wrapper
        var wrapper = document.querySelector('.wrapper');
        if (wrapper) {
            wrapper.classList.remove('loading');
        }
        
        // ELIMINAR CUALQUIER SVG MUY GRANDE (las flechas)
        document.querySelectorAll('svg').forEach(function(svg) {
            var rect = svg.getBoundingClientRect();
            if (rect.width > 100 || rect.height > 100) {
                svg.remove();
            }
        });
        
        // ELIMINAR elementos posicionados absolutamente muy grandes
        document.querySelectorAll('[style*="position: absolute"]').forEach(function(el) {
            var rect = el.getBoundingClientRect();
            if (rect.width > 200 || rect.height > 200) {
                el.remove();
            }
        });
    }
    
    // Desactivar Pace.js si existe
    if (typeof window.Pace !== 'undefined') {
        try {
            window.Pace.stop();
            window.Pace.options = { 
                ajax: false,
                document: false,
                eventLag: false,
                elements: false
            };
        } catch(e) {}
    }
    
    // Prevenir que Pace.js se inicie
    window.paceOptions = {
        ajax: false,
        document: false,
        eventLag: false,
        elements: false
    };
    
    // Ejecutar inmediatamente
    nukePreloader();
    
    // Ejecutar en cada evento posible
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', nukePreloader);
    } else {
        nukePreloader();
    }
    
    window.addEventListener('load', nukePreloader);
    window.addEventListener('pageshow', nukePreloader);
    
    // Ejecutar continuamente durante los primeros 5 segundos
    var counter = 0;
    var maxAttempts = 50; // 5 segundos
    var interval = setInterval(function() {
        nukePreloader();
        counter++;
        if (counter >= maxAttempts) {
            clearInterval(interval);
        }
    }, 100);
    
    // Observar mutaciones del DOM
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    nukePreloader();
                }
            });
        });
        
        // Observar cambios en el body
        if (document.body) {
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });
        }
    }
})();
</script>

