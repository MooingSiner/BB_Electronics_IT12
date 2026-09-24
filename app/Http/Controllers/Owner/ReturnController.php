<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\Warranty;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(): View
    {
        return view('owner.returns.index', ['returns' => collect(), 'warranties' => collect()]);
    }

    public function show(ReturnRecord $returnRecord): RedirectResponse
    {
        return back()->with('status', 'Return detail view is not implemented yet.');
    }

    public function process(?ReturnRecord $returnRecord = null): View
    {
        return view('owner.returns.process', ['txn' => new \stdClass]);
    }

    public function store(): RedirectResponse
    {
        return back()->with('status', 'Recording returns is not implemented yet.');
    }

    public function create(Sale $transaction): RedirectResponse
    {
        return back()->with('status', 'Starting a return from a sale is not implemented yet.');
    }

    public function warranty(Warranty $warranty): View
    {
        return view('owner.returns.warranty', ['warranty' => new \stdClass]);
    }

    public function warrantyUpdate(Warranty $warranty): RedirectResponse
    {
        return back()->with('status', 'Updating warranty claims is not implemented yet.');
    }
}
