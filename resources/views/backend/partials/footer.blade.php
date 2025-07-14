<footer class="main-footer">
    <small>&copy; {{ date('Y') }} SCLMS Admin Panel - All rights reserved.</small>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('.admin-works-toggle');
        const submenu = document.querySelector('.admin-works-submenu');
        const icon = document.querySelector('.toggle-icon');

        if (toggle && submenu && icon) {
            toggle.addEventListener('click', function () {
                submenu.classList.toggle('show');
                icon.classList.toggle('rotate');
            });
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        document.addEventListener('click', function (e) {
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

</body>

</html>