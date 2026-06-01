<script>
(function ($) {
    function readSwalOptions($el) {
        var text = $el.attr('data-swal-text');
        return {
            title: $el.attr('data-swal-title') || 'Are you sure?',
            text: text && String(text).length ? text : undefined,
            icon: $el.attr('data-swal-icon') || 'warning',
            confirmButtonText: $el.attr('data-swal-confirm-text') || 'Yes',
            cancelButtonText: $el.attr('data-swal-cancel-text') || 'Cancel',
            confirmButtonColor: $el.attr('data-swal-confirm-color') || '#3085d6',
            cancelButtonColor: $el.attr('data-swal-cancel-color') || '#6c757d',
        };
    }

    $(document).on('submit', 'form[data-swal-confirm]', function (e) {
        var form = this;
        if (form.getAttribute('data-swal-submitting') === '1') {
            return;
        }
        e.preventDefault();
        var $form = $(form);
        var o = readSwalOptions($form);
        Swal.fire({
            title: o.title,
            text: o.text,
            icon: o.icon,
            showCancelButton: true,
            focusCancel: true,
            confirmButtonText: o.confirmButtonText,
            cancelButtonText: o.cancelButtonText,
            confirmButtonColor: o.confirmButtonColor,
            cancelButtonColor: o.cancelButtonColor,
        }).then(function (result) {
            if (result.isConfirmed) {
                form.setAttribute('data-swal-submitting', '1');
                HTMLFormElement.prototype.submit.call(form);
            }
        });
    });

    $(document).on('click', 'a[data-swal-confirm][href]', function (e) {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.which === 2) {
            return;
        }
        var $a = $(this);
        var href = $a.attr('href');
        if (!href || href === '#' || $a.attr('target') === '_blank') {
            return;
        }
        e.preventDefault();
        var o = readSwalOptions($a);
        Swal.fire({
            title: o.title,
            text: o.text,
            icon: o.icon,
            showCancelButton: true,
            focusCancel: true,
            confirmButtonText: o.confirmButtonText,
            cancelButtonText: o.cancelButtonText,
            confirmButtonColor: o.confirmButtonColor,
            cancelButtonColor: o.cancelButtonColor,
        }).then(function (result) {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
})(jQuery);
</script>
