<footer class="main-footer">
    <small>&copy; {{ date('Y') }} SCLMS Admin Panel - All rights reserved.</small>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.dashboard-toggle');

        toggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const submenu = toggle.nextElementSibling;
                const icon = toggle.querySelector('.toggle-icon');

                if (submenu && submenu.classList.contains('sidebar-submenu')) {
                    submenu.classList.toggle('show');
                }

                if (icon) {
                    icon.classList.toggle('rotate');
                }
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('sidebarToggle');
        const overlay = document.querySelector('.sidebar-overlay');
        const body = document.body;

        function toggleSidebar() {
            body.classList.toggle('sidebar-active');
        }

        function closeSidebar() {
            body.classList.remove('sidebar-active');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            if (
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target) &&
                body.classList.contains('sidebar-active')
            ) {
                closeSidebar();
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select options",
            allowClear: true,
            closeOnSelect: false
        });
    });
</script>

</body>

</html>
