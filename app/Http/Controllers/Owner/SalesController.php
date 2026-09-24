<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(): View
    {
        return view('owner.sales.index');
    }

    public function show(Sale $sale): View
    {
        return view('owner.sales.show', ['txn' => new \stdClass]);
    }

    public function create(): View
    {
        return view('owner.sales.create');
    }

    public function store(): RedirectResponse
    {
        return back()->with('status', 'Recording sales is not implemented yet.');
    }

    public function receipt(Sale $sale): RedirectResponse
    {
        return back()->with('status', 'Receipts are not implemented yet.');
    }
}
