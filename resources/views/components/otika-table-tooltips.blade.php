<script>
    (function ($) {
        'use strict';

        if (!$ || !$.fn.tooltip) {
            return;
        }

        var actionLabels = {
            'fa-eye': 'Lihat detail',
            'fa-edit': 'Edit',
            'fa-trash': 'Hapus',
            'fa-key': 'Reset password',
            'fa-upload': 'Unggah bukti',
            'fa-download': 'Unduh',
            'fa-check': 'Konfirmasi',
            'fa-times': 'Tolak'
        };

        function getActionLabel(element) {
            var $element = $(element);
            var label = $.trim($element.attr('aria-label') || $element.text());

            if (label) {
                return label;
            }

            var classes = ($element.attr('class') || '').split(/\s+/);
            var classLabel = classes.find(function (className) {
                return actionLabels[className];
            });

            if (classLabel) {
                return actionLabels[classLabel];
            }

            if (classes.some(function (className) { return className.indexOf('delete') !== -1; })) {
                return 'Hapus';
            }

            if (classes.some(function (className) { return className.indexOf('reset') !== -1; })) {
                return 'Reset password';
            }

            return 'Aksi';
        }

        function initializeActionTooltips() {
            $('table').each(function () {
                var $table = $(this);
                var actionIndex = -1;

                $table.find('thead tr:first th').each(function (index) {
                    if ($.trim($(this).text()).toLowerCase() === 'aksi') {
                        actionIndex = index;
                    }
                });

                if (actionIndex < 0) {
                    return;
                }

                $table.find('tbody tr').each(function () {
                    $(this).children('td').eq(actionIndex).find('a.btn, button.btn').each(function () {
                        var $action = $(this);
                        var label = getActionLabel(this);

                        if (!$action.attr('title')) {
                            $action.attr('title', label);
                        }

                        if (!$action.attr('aria-label') && !$.trim($action.text())) {
                            $action.attr('aria-label', label);
                        }

                        $action.attr({
                            'data-toggle': 'tooltip',
                            'data-placement': 'top',
                            'data-container': 'body'
                        });

                        if (!$action.data('bs.tooltip')) {
                            $action.tooltip({
                                trigger: 'hover focus',
                                container: 'body'
                            });
                        }
                    });
                });
            });
        }

        $(function () {
            initializeActionTooltips();
            $(document).on('draw.dt', initializeActionTooltips);
        });
    })(window.jQuery);
</script>
