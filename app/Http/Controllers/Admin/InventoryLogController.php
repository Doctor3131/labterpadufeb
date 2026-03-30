<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryLogController extends Controller
{
    public function index()
    {
        $logs = \App\Models\InventoryLog::orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.inventory-logs.index', compact('logs'));
    }

    public function create()
    {
        return view('admin.inventory-logs.create');
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'recipient' => 'required|string|max:255',
            'items' => 'required|string',
            'flow' => 'required|in:IN,OUT',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('proof_file')) {
            $path = $request->file('proof_file')->store('inventory-logs', 'public');
            $validated['proof_file'] = $path;
        }

        \App\Models\InventoryLog::create($validated);

        return redirect()->route('admin.inventory.logs.index')
            ->with('success', 'Catatan arus barang berhasil ditambahkan.');
    }

    public function edit(\App\Models\InventoryLog $log)
    {
        return view('admin.inventory-logs.edit', compact('log'));
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\InventoryLog $log)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'recipient' => 'required|string|max:255',
            'items' => 'required|string',
            'flow' => 'required|in:IN,OUT',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('proof_file')) {
            // Delete old file if exists
            if ($log->proof_file) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($log->proof_file);
            }
            $path = $request->file('proof_file')->store('inventory-logs', 'public');
            $validated['proof_file'] = $path;
        }

        $log->update($validated);

        return redirect()->route('admin.inventory.logs.index')
            ->with('success', 'Catatan arus barang berhasil diperbarui.');
    }

    public function toggleFlow(\App\Models\InventoryLog $log)
    {
        $log->update([
            'flow' => $log->flow === 'IN' ? 'OUT' : 'IN'
        ]);

        return redirect()->back()
            ->with('success', 'Status arus barang berhasil diubah.');
    }

    public function destroy(\App\Models\InventoryLog $log)
    {
        if ($log->proof_file) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($log->proof_file);
        }

        $log->delete();

        return redirect()->route('admin.inventory.logs.index')
            ->with('success', 'Catatan arus barang berhasil dihapus.');
    }
}
