<?php
/**
 * Preloader moderno y minimalista
 */
?>
<style>
/* Estilos del preloader */
#preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #ffffff;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.4s ease, visibility 0.4s;
}

#preloader.hidden {
    opacity: 0;
    visibility: hidden;
}

/* Spinner circular */
.spinner {
    width: 60px;
    height: 60px;
    border: 6px solid #e0e0e0;
    border-top-color: #0a1b5c; /* color corporativo */
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Mensaje opcional */
.loading-text {
    margin-top: 20px;
    font-family: 'Segoe UI', sans-serif;
    font-size: 16px;
    color: #444;
    text-align: center;
}
</style>

<div id="preloader">
    <div>
        <div class="spinner"></div>
        <div class="loading-text">Cargando...</div>
    </div>
</div>

<script>
// Ocultar el preloader automáticamente cuando la página cargue completamente
window.addEventListener('load', () => {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        preloader.classList.add('hidden');
        setTimeout(() => preloader.style.display = 'none', 400); // remueve del flujo visual
    }
});

// Mostrar preloader cuando se envía un formulario
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.remove('hidden');
                preloader.style.display = 'flex';
            }
        });
    });
});
</script>
