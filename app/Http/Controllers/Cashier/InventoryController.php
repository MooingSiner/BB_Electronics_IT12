<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('category_name')->pluck('category_name');

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
            ->when($request->input('status') === 'out_of_stock', fn ($query) => $query->where('quantity_on_hand', '<=', 0))
            ->when($request->input('status') === 'low_stock', fn ($query) => $query
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->where('quantity_on_hand', '>', 0))
            ->when($request->input('status') === 'in_stock', fn ($query) => $query->whereColumn('quantity_on_hand', '>', 'reorder_level'))
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => (object) [
                'id' => $product->product_id,
                'name' => $product->product_name,
                'category' => $product->category->category_name ?? '—',
                'price' => (float) $product->unit_price,
                'stock' => $product->quantity_on_hand,
                'reorder_level' => $product->reorder_level,
            ]);

        return view('cashier.inventory.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        $product->load('category');

        $item = (object) [
            'id' => $product->product_id,
            'name' => $product->product_name,
            'category' => $product->category->category_name ?? '—',
            'price' => (float) $product->unit_price,
            'stock' => $product->quantity_on_hand,
            'reorder_level' => $product->reorder_level,
            'warranty_period_days' => $product->warranty_period_days,
        ];

        return view('cashier.inventory.show', compact('item'));
    }
}
