<style>
    /* Modal Otika harus berada di atas backdrop meskipun halaman memiliki stacking context. */
    .modal { z-index: 1060 !important; }
    .modal-backdrop { z-index: 1050 !important; }
    .modal-dialog { pointer-events: auto; }
    .modal-content { pointer-events: auto; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.jQuery === 'undefined') {
            return;
        }

        $(document).on('show.bs.modal', '.modal', function () {
            if (this.parentNode !== document.body) {
                document.body.appendChild(this);
            }
        });
    });
</script>
