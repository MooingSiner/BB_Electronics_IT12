<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('owner.settings.edit', ['vatRate' => Setting::vatRate()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
        ], [
            'vat_rate.required' => 'Enter the VAT percentage. Use 0 if no VAT is charged.',
            'vat_rate.numeric' => 'The VAT percentage must be a number.',
            'vat_rate.min' => 'The VAT percentage cannot be below 0.',
            'vat_rate.max' => 'The VAT percentage cannot be above 100.',
            'vat_rate.decimal' => 'The VAT percentage can have at most 2 decimal places.',
        ]);

        $old = Setting::vatRate();
        $new = round((float) $validated['vat_rate'], 2);

        Setting::setVatRate($new);

        if ($old !== $new) {
            AuditLog::record('setting', "Changed the VAT rate from {$old}% to {$new}%.");
        }

        return back()->with('success', "The VAT rate is now {$new}%. It applies to new sales only.");
    }
}
