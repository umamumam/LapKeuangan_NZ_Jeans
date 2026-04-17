<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('id')->get();
        return view('partners.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:suppliers,nama',
            'hutang_awal' => 'nullable|numeric|min:0',
        ]);

        Supplier::create([
            'nama' => $request->nama,
            'hutang_awal' => $request->hutang_awal ?? 0,
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil ditambahkan!');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:suppliers,nama,' . $supplier->id,
            'hutang_awal' => 'nullable|numeric|min:0',
        ]);

        $supplier->update([
            'nama' => $request->nama,
            'hutang_awal' => $request->hutang_awal ?? 0,
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil diperbarui!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->back()->with('success', 'Supplier berhasil dihapus!');
    }

    public function show(Supplier $supplier)
    {
        return response()->json($supplier);
    }
}
