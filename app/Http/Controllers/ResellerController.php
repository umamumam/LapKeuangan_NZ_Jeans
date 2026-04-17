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
            'hutang_awal' => 'nullable|numeric|min:0',
        ]);

        Reseller::create([
            'nama' => $request->nama,
            'hutang_awal' => $request->hutang_awal ?? 0,
        ]);

        return redirect()->back()->with('success', 'Reseller berhasil ditambahkan!');
    }

    public function update(Request $request, Reseller $reseller)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:resellers,nama,' . $reseller->id,
            'hutang_awal' => 'nullable|numeric|min:0',
        ]);

        $reseller->update([
            'nama' => $request->nama,
            'hutang_awal' => $request->hutang_awal ?? 0,
        ]);

        return redirect()->back()->with('success', 'Reseller berhasil diperbarui!');
    }

    public function destroy(Reseller $reseller)
    {
        $reseller->delete();

        return redirect()->back()->with('success', 'Reseller berhasil dihapus!');
    }

    public function show(Reseller $reseller)
    {
        return response()->json($reseller);
    }
}
