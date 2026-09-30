<?php

namespace App\Http\Controllers\Owner;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $statusLabels = [
            PurchaseOrderStatus::Pending->value => 'Ordered',
            PurchaseOrderStatus::PartiallyReceived->value => 'Partially Received',
            PurchaseOrderStatus::Received->value => 'Received',
            PurchaseOrderStatus::Cancelled->value => 'Cancelled',
        ];

        $orders = PurchaseOrder::whereNotNull('store_id')
            ->with(['store', 'items'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(fn ($q) => $q
                    ->where('order_id', 'like', "%{$search}%")
                    ->orWhereHas('store', fn ($s) => $s->where('store_name', 'like', "%{$search}%")));
            })
            ->when($request->filled('status'), function ($query) use ($request, $statusLabels) {
                $value = array_search($request->string('status'), $statusLabels, true);

                if ($value !== false) {
                    $query->where('status', $value);
                }
            })
            ->latest('order_date')
            ->get()
            ->map(fn (PurchaseOrder $order) => (object) [
                'id' => $order->order_id,
                'store' => $order->store->store_name ?? '—',
                'order_date' => $order->order_date,
                'expected_date' => $order->date_received,
                'items_count' => $order->items->count(),
                'status' => $statusLabels[$order->status->value] ?? 'Ordered',
            ]);

        return view('owner.purchase-orders.index', compact('orders'));
    }

    public function create(): View
    {
        $stores = Store::orderBy('store_name')->pluck('store_name');

        $products = Product::where('is_active', true)
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => (object) ['id' => $product->product_id, 'name' => $product->product_name]);

        return view('owner.purchase-orders.create', compact('stores', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store' => ['required', 'string', 'max:150'],
            'order_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:product,product_id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $store = Store::where('store_name', $validated['store'])->first()
            ?? Store::create(['store_name' => $validated['store']]);

        $order = PurchaseOrder::create([
            'store_id' => $store->store_id,
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

        return redirect()->route('owner.purchase-orders.show', $order->order_id)->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $order): View
    {
        abort_unless($order->store_id, 404);

        $order->load(['store', 'items.product']);

        $statusLabels = [
            PurchaseOrderStatus::Pending->value => 'Ordered',
            PurchaseOrderStatus::PartiallyReceived->value => 'Partially Received',
            PurchaseOrderStatus::Received->value => 'Received',
            PurchaseOrderStatus::Cancelled->value => 'Cancelled',
        ];

        $item = (object) [
            'id' => $order->order_id,
            'store' => $order->store->store_name ?? '—',
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

        return view('owner.purchase-orders.show', ['order' => $item]);
    }

    public function receive(Request $request, PurchaseOrder $order): RedirectResponse
    {
        abort_unless($order->store_id, 404);

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

        AuditLog::record('stock_adjustment', "Received delivery for purchase order #{$order->order_id}.");

        return redirect()->route('owner.purchase-orders.show', $order->order_id)->with('success', 'Delivery received and stock updated.');
    }
}
