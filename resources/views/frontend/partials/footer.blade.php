<footer class="main-footer">
    <small>&copy; {{ date('Y') }} SCLMS - All rights reserved.</small>
</footer>

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

        // Also close sidebar if clicked outside (optional fallback)
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

    // // Essential JavaScript for Smart College Leave Management System
    // // Add this to your footer.blade.php after the existing sidebar toggle script

    // document.addEventListener('DOMContentLoaded', function() {
    //     // Initialize all components
    //     initPasswordToggle();
    //     initFormValidation();
    //     initAnimations();
    //     initAccessibility();
    // });

    // // ===================== PASSWORD TOGGLE FUNCTIONALITY =====================
    // function initPasswordToggle() {
    //     // Handle multiple password fields (login, register, etc.)
    //     const passwordFields = [{
    //             toggle: '#togglePassword',
    //             input: '#password'
    //         },
    //         {
    //             toggle: '#togglePassword1',
    //             input: '#password'
    //         },
    //         {
    //             toggle: '#togglePassword2',
    //             input: '#password2'
    //         }
    //     ];

    //     passwordFields.forEach(field => {
    //         const toggleBtn = document.querySelector(field.toggle);
    //         const passwordInput = document.querySelector(field.input);

    //         if (toggleBtn && passwordInput) {
    //             toggleBtn.addEventListener('click', function() {
    //                 const type = passwordInput.type === 'password' ? 'text' : 'password';
    //                 passwordInput.type = type;

    //                 // Update icon
    //                 const icon = this.querySelector('i');
    //                 if (icon) {
    //                     icon.classList.toggle('fa-eye');
    //                     icon.classList.toggle('fa-eye-slash');
    //                 }

    //                 // Add smooth animation
    //                 this.style.transform = 'scale(0.9)';
    //                 setTimeout(() => {
    //                     this.style.transform = 'scale(1)';
    //                 }, 150);
    //             });
    //         }
    //     });
    // }
</script>
