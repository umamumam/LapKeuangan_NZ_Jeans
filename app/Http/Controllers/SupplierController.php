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
        ]);

        Supplier::create([
            'nama' => $request->nama,
        ]);

        return redirect()->route('partners.index')->with('success', 'Supplier berhasil ditambahkan!');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:suppliers,nama,' . $supplier->id,
        ]);

        $supplier->update([
            'nama' => $request->nama,
        ]);

        return redirect()->route('partners.index')->with('success', 'Supplier berhasil diperbarui!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('partners.index')->with('success', 'Supplier berhasil dihapus!');
    }

    public function show(Supplier $supplier)
    {
        return response()->json($supplier);
    }
}
