{{-- Fix para el preloader de AdminLTE en reportes --}}
<style>
    .preloader {
        display: none !important;
    }
    .wrapper.loading .content-wrapper {
        opacity: 1 !important;
    }
    /* Asegurar que todo el contenido sea visible */
    .content-wrapper, .content {
        opacity: 1 !important;
        visibility: visible !important;
    }
</style>

<script>
    // Forzar ocultar preloader si existe
    document.addEventListener('DOMContentLoaded', function() {
        const preloader = document.querySelector('.preloader');
        if (preloader) {
            preloader.style.display = 'none';
        }
        document.body.classList.remove('sidebar-mini', 'layout-fixed');
    });
</script>

