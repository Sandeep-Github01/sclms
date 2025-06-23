<footer class="main-footer">
    <small>&copy; {{ date('Y') }} SCLMS - All rights reserved.</small>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const toggleBtn = document.getElementById('sidebarToggle');
    const bodyEl = document.body;
    const overlay = document.querySelector('.sidebar-overlay');

    function openSidebar() {
        bodyEl.classList.add('sidebar-active');
    }
    function closeSidebar() {
        bodyEl.classList.remove('sidebar-active');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(){
            bodyEl.classList.toggle('sidebar-active');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', function(){
            closeSidebar();
        });
    }
});
</script>
