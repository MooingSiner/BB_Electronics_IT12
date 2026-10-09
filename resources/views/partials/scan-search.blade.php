<script>
    // A barcode scanner types like a very fast keyboard. The search box that has data-scan-search
    // is focused on its own, picks up scans typed anywhere on the page, and each new scan replaces the last one.
    // data-scan-submit sends the form after a scan; data-scan-enter presses Enter for scanners that do not.
    (function () {
        function searchBox() {
            return document.querySelector('input[data-scan-search]');
        }

        if (!searchBox()) {
            return;
        }

        var lastKey = 0;
        var fastKeys = 0;
        var lastWasScan = false;
        var timer = null;

        function focusAndSelect() {
            var input = searchBox();

            if (input) {
                input.focus();
                input.select();
            }
        }

        if (window.matchMedia('(pointer: fine)').matches) {
            focusAndSelect();
        }

        document.addEventListener('keydown', function (event) {
            var input = searchBox();

            if (!input || event.ctrlKey || event.metaKey || event.altKey || event.key.length !== 1) {
                return;
            }

            var target = event.target;
            var typingElsewhere = target !== input && (['INPUT', 'TEXTAREA', 'SELECT'].indexOf(target.tagName) !== -1 || target.isContentEditable);

            if (typingElsewhere) {
                return;
            }

            var now = Date.now();
            var gap = now - lastKey;

            if (target !== input) {
                focusAndSelect();
            } else if (lastWasScan && gap > 400 && input.value !== '') {
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }

            fastKeys = gap < 50 ? fastKeys + 1 : 0;
            lastKey = now;

            clearTimeout(timer);
            timer = setTimeout(function () {
                lastWasScan = fastKeys >= 4;

                var box = searchBox();

                if (!lastWasScan || !box) {
                    return;
                }

                if (box.hasAttribute('data-scan-submit') && box.form) {
                    box.form.requestSubmit();
                } else if (box.hasAttribute('data-scan-enter')) {
                    box.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
                }
            }, 250);
        });
    })();
</script>
