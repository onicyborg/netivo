<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function () {
        'use strict';

        function alertType(element) {
            if (element.classList.contains('alert-success')) return 'success';
            if (element.classList.contains('alert-danger')) return 'error';
            if (element.classList.contains('alert-warning')) return 'warning';
            return 'info';
        }

        function alertMarkup(element) {
            var clone = element.cloneNode(true);

            clone.querySelectorAll('i, .alert-icon').forEach(function (icon) {
                icon.remove();
            });

            return clone.innerHTML.trim();
        }

        function showAlerts() {
            if (!window.Swal) {
                return;
            }

            var alerts = Array.from(document.querySelectorAll('.alert:not(.d-none):not([data-sweetalert-ignore])'));

            function showNext(index) {
                if (!alerts[index]) {
                    return;
                }

                var alert = alerts[index];
                alert.setAttribute('hidden', 'hidden');

                window.Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: alertType(alert),
                    html: alertMarkup(alert),
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    showCloseButton: true,
                    customClass: { popup: 'netivo-sweetalert' },
                    didClose: function () {
                        showNext(index + 1);
                    }
                });
            }

            showNext(0);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showAlerts);
        } else {
            showAlerts();
        }
    })();
</script>
