<?php

namespace App\Http\Controllers\Owner;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $statusLabels = [
            PurchaseOrderStatus::Pending->value => 'Ordered',
            PurchaseOrderStatus::PartiallyReceived->value => 'Partially Received',
            PurchaseOrderStatus::Received->value => 'Received',
            PurchaseOrderStatus::Cancelled->value => 'Cancelled',
        ];

        $orders = PurchaseOrder::with(['supplier', 'items'])
            ->latest('order_date')
            ->get()
            ->map(fn (PurchaseOrder $order) => (object) [
                'id' => $order->order_id,
                'supplier' => $order->supplier->supplier_name ?? '—',
                'order_date' => $order->order_date,
                'expected_date' => $order->date_received,
                'items_count' => $order->items->count(),
                'status' => $statusLabels[$order->status->value] ?? 'Ordered',
            ]);

        return view('owner.suppliers.index', compact('orders'));
    }

    public function orders(): RedirectResponse
    {
        return redirect()->route('owner.suppliers.index');
    }

    public function create(): View
    {
        return view('owner.suppliers.create');
    }

    public function store(): RedirectResponse
    {
        return back()->with('status', 'Adding suppliers is not implemented yet.');
    }

    public function show(Supplier $supplier): View
    {
        return view('owner.suppliers.show', ['order' => new \stdClass]);
    }

    public function damagedIndex(): View
    {
        return view('owner.suppliers.damaged', ['damaged' => collect()]);
    }

    public function damagedShow(int $id): RedirectResponse
    {
        return back()->with('status', 'Damaged product detail is not implemented yet.');
    }

    public function reportDamage(Supplier $supplier): RedirectResponse
    {
        return back()->with('status', 'Reporting damaged products is not implemented yet.');
    }

    public function returnToSupplier(Supplier $supplier): RedirectResponse
    {
        return back()->with('status', 'Returning products to a supplier is not implemented yet.');
    }

    public function replacement(Supplier $supplier): RedirectResponse
    {
        return back()->with('status', 'Requesting replacements is not implemented yet.');
    }

    public function receive(Supplier $supplier): RedirectResponse
    {
        return back()->with('status', 'Receiving supplier orders is not implemented yet.');
    }
}
