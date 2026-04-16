<?php

namespace App\Http\Controllers;

use App\Models\Reseller;
use Illuminate\Http\Request;

class ResellerController extends Controller
{
    public function index()
    {
        $resellers = Reseller::orderBy('id')->get();
        return view('partners.index', compact('resellers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:resellers,nama',
        ]);

        Reseller::create([
            'nama' => $request->nama,
        ]);

        return redirect()->route('partners.index')->with('success', 'Reseller berhasil ditambahkan!');
    }

    public function update(Request $request, Reseller $reseller)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:resellers,nama,' . $reseller->id,
        ]);

        $reseller->update([
            'nama' => $request->nama,
        ]);

        return redirect()->route('partners.index')->with('success', 'Reseller berhasil diperbarui!');
    }

    public function destroy(Reseller $reseller)
    {
        $reseller->delete();

        return redirect()->route('partners.index')->with('success', 'Reseller berhasil dihapus!');
    }

    public function show(Reseller $reseller)
    {
        return response()->json($reseller);
    }
}
