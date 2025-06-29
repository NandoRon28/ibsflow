<?php
// Pastikan $lang tersedia
if (!isset($lang) || !is_array($lang)) {
    $lang = [];
}
if (!isset($lang['footer'])) {
    $lang['footer'] = '[footer]';
}
?>
<footer class="bg-dark text-white text-center py-3">
    <p>© 2025 IBSFlow-Aliran Digital untuk Pesantren Modern</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/translations.js"></script>

<script src="js/scripts.js"></script>
</body>
</html>