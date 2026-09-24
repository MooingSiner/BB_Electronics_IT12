<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($request->filled('search'), fn ($query) => $query->where(
                'product_name', 'like', '%'.$request->string('search').'%'
            ))
            ->when($request->filled('category'), fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('category_name', 'like', '%'.$request->string('category').'%')
            ))
            ->when($request->input('status') === 'Out of Stock', fn ($query) => $query->where('quantity_on_hand', '<=', 0))
            ->when($request->input('status') === 'Low Stock', fn ($query) => $query
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->where('quantity_on_hand', '>', 0))
            ->when($request->input('status') === 'In Stock', fn ($query) => $query->whereColumn('quantity_on_hand', '>', 'reorder_level'))
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => (object) [
                'id' => $product->product_id,
                'name' => $product->product_name,
                'category' => $product->category->category_name ?? '—',
                'unit_price' => (float) $product->unit_price,
                'stock' => $product->quantity_on_hand,
                'reorder_level' => $product->reorder_level,
            ]);

        return view('owner.inventory.index', compact('products'));
    }

    public function create(): View
    {
        return view('owner.inventory.create');
    }

    public function store(): RedirectResponse
    {
        return back()->with('status', 'Adding products is not implemented yet.');
    }

    public function show(Product $product): RedirectResponse
    {
        return back()->with('status', 'Product detail view is not implemented yet.');
    }

    public function edit(Product $product): RedirectResponse
    {
        return back()->with('status', 'Editing products is not implemented yet.');
    }

    public function stockIn(Product $product): RedirectResponse
    {
        return back()->with('status', 'Stock-in is not implemented yet.');
    }

    public function history(Product $product): RedirectResponse
    {
        return back()->with('status', 'Stock history is not implemented yet.');
    }
}
