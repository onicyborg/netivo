<script>
    (function ($) {
        'use strict';

        if (!$) {
            return;
        }

        function adminPath(path) {
            return '{{ url('/admin') }}' + path;
        }

        function setValue(selector, value) {
            var $field = $(selector);
            var normalized = value == null ? '' : value;

            if ($field.is('input, select, textarea')) {
                $field.val(normalized);
            } else {
                $field.text(normalized);
            }
        }

        function showModal(selector) {
            $(selector).modal('show');
        }

        function confirmDelete(button, formSelector, action) {
            var $button = $(button);
            var name = $button.data('name') || 'data ini';
            var $form = $(formSelector);

            $form.attr('action', action);

            if (!window.Swal) {
                return;
            }

            window.Swal.fire({
                icon: 'warning',
                title: 'Hapus data?',
                html: 'Data <strong>' + $('<div>').text(name).html() + '</strong> akan dihapus dan tidak dapat dikembalikan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    $form.trigger('submit');
                }
            });
        }

        $(function () {
            $('.js-delete-service, .js-delete-payment-method, .js-delete-customer').removeAttr('data-toggle data-target');

            $(document)
                .off('click.netivoAdminActions', '.js-edit-service')
                .on('click.netivoAdminActions', '.js-edit-service', function (event) {
                    event.preventDefault();
                    var $button = $(this);
                    $('#service-edit-form').attr('action', adminPath('/services/' + $button.data('id')));
                    setValue('#service-edit-name', $button.data('name'));
                    setValue('#service-edit-speed', $button.data('speed'));
                    setValue('#service-edit-price', $button.data('price'));
                    setValue('#service-edit-description', $button.data('description'));
                    $('#service-edit-active').prop('checked', String($button.data('active')) === '1').trigger('change');
                    showModal('#service-edit-modal');
                })
                .off('click.netivoAdminActions', '.js-edit-payment-method')
                .on('click.netivoAdminActions', '.js-edit-payment-method', function (event) {
                    event.preventDefault();
                    var $button = $(this);
                    $('#payment-method-edit-form').attr('action', adminPath('/payment-methods/' + $button.data('id')));
                    setValue('#payment-method-edit-type', $button.data('type'));
                    setValue('#payment-method-edit-name', $button.data('name'));
                    setValue('#payment-method-edit-number', $button.data('number'));
                    setValue('#payment-method-edit-account-name', $button.data('account-name'));
                    $('#payment-method-edit-active').prop('checked', String($button.data('active')) === '1').trigger('change');
                    showModal('#payment-method-edit-modal');
                })
                .off('click.netivoAdminActions', '.js-edit-customer')
                .on('click.netivoAdminActions', '.js-edit-customer', function (event) {
                    event.preventDefault();
                    var $button = $(this);
                    $('#customer-edit-form').attr('action', adminPath('/customers/' + $button.data('id')));
                    setValue('#customer-edit-name', $button.data('name'));
                    setValue('#customer-edit-email', $button.data('email'));
                    setValue('#customer-edit-phone', $button.data('phone'));
                    setValue('#customer-edit-address', $button.data('address'));
                    setValue('#customer-edit-service', $button.data('service'));
                    setValue('#customer-edit-status', $button.data('status'));
                    showModal('#customer-edit-modal');
                })
                .off('click.netivoAdminActions', '.js-reset-customer')
                .on('click.netivoAdminActions', '.js-reset-customer', function (event) {
                    event.preventDefault();
                    var $button = $(this);
                    $('#customer-reset-form').attr('action', adminPath('/customers/' + $button.data('id') + '/reset-password'));
                    setValue('#customer-reset-name', $button.data('name'));
                    showModal('#customer-reset-modal');
                })
                .off('click.netivoAdminActions', '.js-delete-service')
                .on('click.netivoAdminActions', '.js-delete-service', function (event) {
                    event.preventDefault();
                    confirmDelete(this, '#service-delete-form', adminPath('/services/' + $(this).data('id')));
                })
                .off('click.netivoAdminActions', '.js-delete-payment-method')
                .on('click.netivoAdminActions', '.js-delete-payment-method', function (event) {
                    event.preventDefault();
                    confirmDelete(this, '#payment-method-delete-form', adminPath('/payment-methods/' + $(this).data('id')));
                })
                .off('click.netivoAdminActions', '.js-delete-customer')
                .on('click.netivoAdminActions', '.js-delete-customer', function (event) {
                    event.preventDefault();
                    confirmDelete(this, '#customer-delete-form', adminPath('/customers/' + $(this).data('id')));
                });
        });
    })(window.jQuery);
</script>
