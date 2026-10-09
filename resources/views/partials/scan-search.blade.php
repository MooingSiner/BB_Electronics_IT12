<script>
    // A barcode scanner types like a very fast keyboard. The search box that has data-scan-search
    // is focused on its own, picks up scans typed anywhere on the page, and each new scan replaces the last one.
    // A scan that lands in any other field (a supplier name, a note, a quantity) is moved to the search box
    // and the field gets its old text back. People never type that fast, so ordinary typing is left alone.
    // data-scan-submit sends the form after a scan; data-scan-enter presses Enter for scanners that do not.
    (function () {
        function searchBox() {
            return document.querySelector('input[data-scan-search]');
        }

        if (!searchBox()) {
            return;
        }

        var FAST = 50;
        var lastKey = 0;
        var fastKeys = 0;
        var lastWasScan = false;
        var timer = null;
        var burst = null;
        var textTypes = ['text', 'search', 'number', 'email', 'tel', 'url', 'password'];

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

        function isOtherField(target, input) {
            if (target === input) {
                return false;
            }

            if (target.tagName === 'TEXTAREA' || target.tagName === 'SELECT' || target.isContentEditable) {
                return true;
            }

            return target.tagName === 'INPUT' && textTypes.indexOf((target.getAttribute('type') || 'text').toLowerCase()) !== -1;
        }

        function remember(target) {
            return { field: target, value: target.value, index: target.selectedIndex, chars: '' };
        }

        function restore(snapshot) {
            var field = snapshot.field;

            if (field.tagName === 'SELECT') {
                field.selectedIndex = snapshot.index;
            } else if (!field.isContentEditable) {
                field.value = snapshot.value;
            }

            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function afterTyping() {
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
        }

        document.addEventListener('keydown', function (event) {
            var input = searchBox();

            if (!input || event.ctrlKey || event.metaKey || event.altKey || event.key.length !== 1) {
                return;
            }

            var target = event.target;
            var now = Date.now();
            var gap = now - lastKey;
            var other = isOtherField(target, input);

            if (other) {
                if (!burst || burst.field !== target || gap >= FAST) {
                    burst = remember(target);
                    fastKeys = 0;
                } else {
                    fastKeys++;
                }

                lastKey = now;

                if (fastKeys >= 3) {
                    // the fourth quick key in a row: this is a scanner, so move what it typed to the search box
                    var typed = burst.chars;
                    restore(burst);
                    burst = null;
                    input.focus();
                    input.value = typed;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    fastKeys = 4;
                    afterTyping();

                    return;
                }

                burst.chars += event.key;

                return;
            }

            burst = null;

            if (target !== input) {
                focusAndSelect();
            } else if (lastWasScan && gap > 400 && input.value !== '') {
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }

            fastKeys = gap < FAST ? fastKeys + 1 : 0;
            lastKey = now;
            afterTyping();
        });
    })();
</script>
