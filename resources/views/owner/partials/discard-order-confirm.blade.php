{{-- The Cancel link on a new order form asks before it throws away what was entered. An empty form just closes. --}}
<script>
    (function () {
        const link = document.getElementById('discardOrder');
        const form = document.getElementById('orderForm');

        if (!link || !form) {
            return;
        }

        function hasData() {
            const picked = Array.from(form.querySelectorAll('.item-product-select')).some(select => select.value !== '');
            const typed = Array.from(form.querySelectorAll('input[type="text"], textarea')).some(field => field.value.trim() !== '');

            return picked || typed;
        }

        function sync() {
            if (hasData()) {
                link.dataset.confirm = 'Cancel this new order? What you entered will not be saved.';
            } else {
                delete link.dataset.confirm;
            }
        }

        ['mousedown', 'touchstart', 'keydown', 'focus'].forEach(name => link.addEventListener(name, sync));
    })();
</script>
