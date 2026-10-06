{{-- Replaces the browser's confirm() box. Works with data-confirm="..." on a form or button, and with legacy onsubmit/onclick="return confirm('...')". --}}
<div id="confirmDialog" style="display:none" class="fixed inset-0 z-[100] items-center justify-center bg-black/40 p-4" role="dialog" aria-modal="true" aria-labelledby="confirmDialogTitle">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-start gap-3">
            <span id="confirmDialogIcon" class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-100 text-[#363E48]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </span>
            <div>
                <h3 id="confirmDialogTitle" class="text-base font-semibold text-[#363E48]">Please confirm</h3>
                <p id="confirmDialogMessage" class="mt-1 text-sm text-slate-600"></p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" id="confirmDialogCancel" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
            <button type="button" id="confirmDialogOk" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background-color:#363E48">Confirm</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const dialog = document.getElementById('confirmDialog');
        const message = document.getElementById('confirmDialogMessage');
        const okButton = document.getElementById('confirmDialogOk');
        const cancelButton = document.getElementById('confirmDialogCancel');
        let pending = null;

        function close() {
            dialog.style.display = 'none';
            pending = null;
        }

        function ask(text, onConfirm) {
            const risky = /archive|cancel|deactivate|remove|delete|void|clear|log out/i.test(text);
            message.textContent = text;
            okButton.style.backgroundColor = risky ? '#dc2626' : '#363E48';
            okButton.textContent = risky ? 'Yes, continue' : 'Confirm';
            pending = onConfirm;
            dialog.style.display = 'flex';
            okButton.focus();
        }

        function legacyMessage(element, attribute) {
            const code = element.getAttribute(attribute) || '';
            const match = code.match(/confirm\('((?:[^'\\]|\\.)*)'\)/);
            return match ? match[1].replace(/\\'/g, "'") : null;
        }

        okButton.addEventListener('click', () => { const run = pending; close(); if (run) run(); });
        cancelButton.addEventListener('click', close);
        dialog.addEventListener('click', event => { if (event.target === dialog) close(); });
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && pending) close(); });

        document.addEventListener('submit', event => {
            const form = event.target;
            if (!form.matches || !form.matches('form')) return;

            if (form.dataset.confirmPassed) {
                delete form.dataset.confirmPassed;
                return;
            }

            if (form.dataset.confirm) {
                event.preventDefault();
                event.stopImmediatePropagation();
                const submitter = event.submitter;
                ask(form.dataset.confirm, () => {
                    form.dataset.confirmPassed = '1';
                    form.requestSubmit(submitter || undefined);
                });
                return;
            }

            const text = legacyMessage(form, 'onsubmit');
            if (!text) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            ask(text, () => HTMLFormElement.prototype.submit.call(form));
        }, true);

        document.addEventListener('click', event => {
            const control = event.target.closest('button[data-confirm], a[data-confirm], button[onclick], a[onclick]');
            if (!control) return;

            if (control.dataset.confirmPassed) {
                delete control.dataset.confirmPassed;
                return;
            }

            if (control.dataset.confirm) {
                event.preventDefault();
                event.stopImmediatePropagation();
                ask(control.dataset.confirm, () => {
                    control.dataset.confirmPassed = '1';
                    control.click();
                });
                return;
            }

            const text = legacyMessage(control, 'onclick');
            if (!text) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            ask(text, () => {
                if (control.tagName === 'A') {
                    window.location.href = control.href;
                } else if (control.form) {
                    HTMLFormElement.prototype.submit.call(control.form);
                }
            });
        }, true);
    })();
</script>
