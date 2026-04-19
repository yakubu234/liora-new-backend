<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AgreementController extends Controller
{
    public function edit(): View
    {
        $agreement = null;

        if (Schema::hasTable('agreement')) {
            $agreement = DB::table('agreement')->orderByDesc('id')->first();
        }

        return view('pages.settings.agreement', [
            'agreement' => $agreement,
            'decodedDescription' => $this->decodeAgreement($agreement->description ?? ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string'],
        ], [
            'description.required' => 'Please enter the agreement description before saving.',
        ]);

        $description = $validated['description'];
        $agreementId = null;

        if (Schema::hasTable('agreement')) {
            $agreementId = DB::table('agreement')->orderByDesc('id')->value('id');
        }

        if ($agreementId) {
            DB::table('agreement')
                ->where('id', $agreementId)
                ->update([
                    'description' => $description,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('agreement')->insert([
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('settings.agreement')
            ->with('success', 'Agreement details were updated successfully.');
    }

    private function decodeAgreement(string $content): string
    {
        $decoded = html_entity_decode($content, ENT_QUOTES | ENT_HTML5);
        $decodedAgain = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5);

        return $decodedAgain;
    }
}
