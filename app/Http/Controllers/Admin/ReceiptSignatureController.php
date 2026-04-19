<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReceiptSignatureController extends Controller
{
    public function edit(): View
    {
        $signature = null;

        if (Schema::hasTable('receipt_signature')) {
            $signature = DB::table('receipt_signature')->orderByDesc('id')->first();
        }

        return view('pages.settings.receipt-signature', [
            'signature' => $signature,
            'decodedDescription' => $this->decodeContent($signature->description ?? ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string'],
        ], [
            'description.required' => 'Please enter the receipt signature content before saving.',
        ]);

        $description = $validated['description'];
        $signatureId = null;

        if (Schema::hasTable('receipt_signature')) {
            $signatureId = DB::table('receipt_signature')->orderByDesc('id')->value('id');
        }

        if ($signatureId) {
            DB::table('receipt_signature')
                ->where('id', $signatureId)
                ->update([
                    'description' => $description,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('receipt_signature')->insert([
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('settings.receipt-signature')
            ->with('success', 'Receipt signature details were updated successfully.');
    }

    private function decodeContent(string $content): string
    {
        $decoded = html_entity_decode($content, ENT_QUOTES | ENT_HTML5);

        return html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5);
    }
}
