        </main> 
        <footer class="admin-footer bg-dark text-white text-center py-3 mt-auto">
            <div class="container-fluid">
                <p class="mb-0">© <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> - Admin Panel. All Rights Reserved.</p>
            </div>
        </footer>
    </div> 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src='https://api.mapbox.com/mapbox-gl-js/v3.2.0/mapbox-gl.js'></script>
    <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js'></script>
    <script src="<?php echo APP_URL; ?>assets/js/admin_script.js"></script> 
    <?php
    if (isset($page_specific_js) && is_array($page_specific_js)) {
        foreach ($page_specific_js as $js_file) {
            echo '<script src="' . APP_URL . 'assets/js/' . htmlspecialchars($js_file) . '"></script>';
        }
    } elseif (isset($page_specific_js) && is_string($page_specific_js)) {
         echo '<script src="' . APP_URL . 'assets/js/' . htmlspecialchars($page_specific_js) . '"></script>';
    }
    ?>
</body>
</html>