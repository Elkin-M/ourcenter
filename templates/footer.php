<!-- footer.php -->
<footer class="text-center mt-4 py-3">
    <small>&copy; <?= date('Y') ?> Our Center. Todos los derechos reservados.</small>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById("sidebarToggle").onclick = function () {
        document.getElementById("sidebar").classList.toggle("active");
        document.getElementById("mainContent").classList.toggle("expanded");
    };
</script>
