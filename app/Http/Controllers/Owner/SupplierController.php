<?php

namespace App\Http\Controllers\Owner;

use App\Enums\PurchaseOrderStatus;
use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReturnRecord;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $suppliers = Supplier::orderBy('supplier_name')->pluck('supplier_name');

        $products = Product::where('is_active', true)
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => (object) ['id' => $product->product_id, 'name' => $product->product_name]);

        return view('owner.suppliers.create', compact('suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier' => ['required', 'string', 'max:150'],
            'order_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:product,product_id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $supplier = Supplier::where('supplier_name', $validated['supplier'])->first()
            ?? Supplier::create(['supplier_name' => $validated['supplier']]);

        $order = PurchaseOrder::create([
            'supplier_id' => $supplier->supplier_id,
            'user_id' => Auth::id(),
            'order_date' => $validated['order_date'],
            'status' => PurchaseOrderStatus::Pending,
        ]);

        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->order_id,
                'product_id' => $item['product_id'],
                'quantity_ordered' => $item['qty'],
                'quantity_received' => 0,
                'unit_cost' => $item['unit_cost'] ?? 0,
            ]);
        }

        return redirect()->route('owner.suppliers.show', $order->order_id)->with('success', 'Supplier order created.');
    }

    public function show(PurchaseOrder $order): View
    {
        $order->load(['supplier', 'items.product']);

        $statusLabels = [
            PurchaseOrderStatus::Pending->value => 'Ordered',
            PurchaseOrderStatus::PartiallyReceived->value => 'Partially Received',
            PurchaseOrderStatus::Received->value => 'Received',
            PurchaseOrderStatus::Cancelled->value => 'Cancelled',
        ];

        $item = (object) [
            'id' => $order->order_id,
            'supplier' => $order->supplier->supplier_name ?? '—',
            'supplier_id' => $order->supplier_id,
            'order_date' => $order->order_date,
            'expected_date' => $order->date_received,
            'status' => $statusLabels[$order->status->value] ?? 'Ordered',
            'total_cost' => $order->items->sum(fn (OrderItem $i) => (float) $i->unit_cost * $i->quantity_ordered),
            'items' => $order->items->map(fn (OrderItem $i) => (object) [
                'id' => $i->order_item_id,
                'product' => (object) ['id' => $i->product->product_id ?? null, 'name' => $i->product->product_name ?? '—'],
                'qty_ordered' => $i->quantity_ordered,
                'qty_received' => $i->quantity_received,
                'unit_cost' => (float) $i->unit_cost,
            ]),
        ];

        $openDamaged = ReturnRecord::where('supplier_id', $order->supplier_id)
            ->where('status', ReturnStatus::Open)
            ->where('condition', ReturnCondition::Damaged)
            ->with('product')
            ->get()
            ->map(fn (ReturnRecord $r) => (object) [
                'id' => $r->return_id,
                'product_name' => $r->product->product_name ?? '—',
                'quantity' => $r->quantity,
            ]);

        return view('owner.suppliers.show', ['order' => $item, 'openDamaged' => $openDamaged]);
    }

    public function damagedIndex(): View
    {
        $damaged = ReturnRecord::whereNotNull('supplier_id')
            ->with(['product', 'supplier'])
            ->latest('return_date')
            ->get()
            ->map(fn (ReturnRecord $r) => (object) [
                'id' => 'DMG-'.str_pad((string) $r->return_id, 4, '0', STR_PAD_LEFT),
                'return_id' => $r->return_id,
                'supplier' => $r->supplier->supplier_name ?? '—',
                'product_name' => $r->product->product_name ?? '—',
                'qty_damaged' => $r->quantity,
                'date' => $r->return_date,
                'description' => $r->reason,
                'status' => match (true) {
                    $r->status === ReturnStatus::Resolved && $r->resolution === ReturnResolution::Replacement => 'Replacement Received',
                    $r->status === ReturnStatus::Resolved && $r->resolution === ReturnResolution::SupplierExchange => 'Returned to Supplier',
                    $r->status === ReturnStatus::Resolved => 'Resolved',
                    default => 'Reported',
                },
            ]);

        return view('owner.suppliers.damaged', compact('damaged'));
    }

    public function damagedShow(int $id): View
    {
        $returnRecord = ReturnRecord::whereNotNull('supplier_id')->with(['product', 'supplier'])->findOrFail($id);

        $item = (object) [
            'id' => 'DMG-'.str_pad((string) $returnRecord->return_id, 4, '0', STR_PAD_LEFT),
            'supplier' => $returnRecord->supplier->supplier_name ?? '—',
            'product_name' => $returnRecord->product->product_name ?? '—',
            'qty_damaged' => $returnRecord->quantity,
            'date' => $returnRecord->return_date,
            'description' => $returnRecord->reason,
            'resolution' => ucwords(str_replace('_', ' ', $returnRecord->resolution->value)),
            'status' => $returnRecord->status === ReturnStatus::Open ? 'Reported' : 'Resolved',
        ];

        return view('owner.suppliers.damaged-show', ['item' => $item]);
    }

    public function reportDamage(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:product,product_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        ReturnRecord::create([
            'sale_id' => null,
            'product_id' => $validated['product_id'],
            'supplier_id' => $order->supplier_id,
            'return_date' => now(),
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'condition' => ReturnCondition::Damaged,
            'resolution' => ReturnResolution::Pending,
            'status' => ReturnStatus::Open,
        ]);

        AuditLog::record('stock_adjustment', "Reported {$validated['quantity']} damaged unit(s) from supplier delivery on order #{$order->order_id}.");

        return back()->with('success', 'Damaged product reported.');
    }

    public function returnToSupplier(PurchaseOrder $order): RedirectResponse
    {
        $updated = ReturnRecord::where('supplier_id', $order->supplier_id)
            ->where('status', ReturnStatus::Open)
            ->where('condition', ReturnCondition::Damaged)
            ->get();

        foreach ($updated as $returnRecord) {
            $returnRecord->update([
                'status' => ReturnStatus::Resolved,
                'resolution' => ReturnResolution::SupplierExchange,
            ]);
        }

        AuditLog::record('refund', "Marked {$updated->count()} damaged item(s) as returned to supplier for order #{$order->order_id}.");

        return back()->with('success', 'Damaged items marked as returned to supplier.');
    }

    public function replacement(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'return_id' => ['required', 'exists:return_record,return_id'],
        ]);

        $returnRecord = ReturnRecord::where('supplier_id', $order->supplier_id)
            ->where('status', ReturnStatus::Open)
            ->findOrFail($validated['return_id']);

        $returnRecord->update([
            'status' => ReturnStatus::Resolved,
            'resolution' => ReturnResolution::Replacement,
        ]);

        StockAdjustment::create([
            'product_id' => $returnRecord->product_id,
            'user_id' => Auth::id(),
            'adjustment_date' => now(),
            'quantity_change' => $returnRecord->quantity,
            'reason' => "Replacement received from supplier for damaged report #{$returnRecord->return_id}",
        ]);

        AuditLog::record('stock_adjustment', "Received {$returnRecord->quantity} replacement unit(s) from supplier for order #{$order->order_id}.");

        return back()->with('success', 'Replacement recorded and stock updated.');
    }

    public function receive(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'date_received' => ['required', 'date'],
            'items' => ['required', 'array'],
            'items.*.qty_received' => ['required', 'integer', 'min:0'],
        ]);

        $order->load('items');

        foreach ($validated['items'] as $orderItemId => $data) {
            $orderItem = $order->items->firstWhere('order_item_id', (int) $orderItemId);

            if (! $orderItem) {
                continue;
            }

            $orderItem->update([
                'quantity_received' => min($data['qty_received'], $orderItem->quantity_ordered),
            ]);
        }

        $order->load('items');
        $allReceived = $order->items->every(fn (OrderItem $i) => $i->quantity_received >= $i->quantity_ordered);
        $anyReceived = $order->items->sum('quantity_received') > 0;

        $order->update([
            'status' => $allReceived ? PurchaseOrderStatus::Received : ($anyReceived ? PurchaseOrderStatus::PartiallyReceived : PurchaseOrderStatus::Pending),
            'date_received' => $validated['date_received'],
        ]);

        AuditLog::record('stock_adjustment', "Received delivery for supplier order #{$order->order_id}.");

        return redirect()->route('owner.suppliers.show', $order->order_id)->with('success', 'Delivery received and stock updated.');
    }
}
