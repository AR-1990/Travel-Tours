</main>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ==================== DARK MODE TOGGLE ====================
        const darkModeToggle = document.getElementById('darkModeToggle');
        const htmlElement = document.documentElement;

        // Initialize dark mode from localStorage
        function initDarkMode() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true' ||
                             window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (isDarkMode) {
                htmlElement.classList.add('dark-mode');
                if (darkModeToggle) {
                    darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                }
            }
        }

        // Toggle dark mode
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function() {
                htmlElement.classList.toggle('dark-mode');
                const isDark = htmlElement.classList.contains('dark-mode');
                localStorage.setItem('darkMode', isDark);
                
                // Update icon
                darkModeToggle.innerHTML = isDark ? 
                    '<i class="fas fa-sun"></i>' : 
                    '<i class="fas fa-moon"></i>';
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initDarkMode);

        // ==================== SIDEBAR TOGGLE ====================
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('dashboardSidebar');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('open');
            });

            // Close sidebar when clicking on a menu item
            const sidebarLinks = document.querySelectorAll('.sidebar-menu li a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                });
            });

            // Close sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            });

            // Handle sidebar overlay
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);

            sidebar.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    sidebar.classList.remove('open');
                }
            });
        }

        // Smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add active class to current route
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = location.pathname;
            const menuItems = document.querySelectorAll('.sidebar-menu a');
            
            menuItems.forEach(item => {
                if (item.getAttribute('href') === currentLocation) {
                    item.classList.add('active');
                }
            });
        });

        // DataTables default settings
        if (typeof $.fn.dataTable !== 'undefined') {
            $.extend($.fn.dataTable.defaults, {
                responsive: true,
                language: {
                    searchPlaceholder: "Search...",
                }
            });
        }

        // Toast notification function
        function showToast(message, type = 'success') {
            const toastClass = `toast-${type}`;
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'} position-fixed bottom-0 end-0 m-3`;
            toast.setAttribute('role', 'alert');
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="d-flex">
                    <div>${message}</div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.body.appendChild(toast);
            
            const bsAlert = new bootstrap.Alert(toast);
            setTimeout(() => {
                bsAlert.close();
            }, 3000);
        }

        // CSRF token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Add loading state to buttons
        document.addEventListener('click', function(e) {
            if (e.target.matches('button[type="submit"], .btn-submit')) {
                e.target.disabled = true;
                e.target.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
            }
        });

        // Form validation
        if (typeof $.validator !== 'undefined') {
            $.validator.setDefaults({
                highlight: function(element) {
                    $(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function(element) {
                    $(element).closest('.form-group').removeClass('has-error');
                },
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                }
            });
        }
    </script>

    @yield('scripts')
</body>
</html>