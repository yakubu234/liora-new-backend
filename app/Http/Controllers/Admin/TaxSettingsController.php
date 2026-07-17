<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxSettingsController extends Controller
{
    public function edit(): View
    {
        return view('pages.settings.tax-deductions', [
            'taxRate' => (float) (DB::table('tax_settings')->orderByDesc('id')->value('rate') ?? 0),
            'deductions' => DB::table('pre_tax_deductions')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'deductions' => ['nullable', 'array'],
            'deductions.*.id' => ['nullable', 'integer', 'exists:pre_tax_deductions,id'],
            'deductions.*.name' => ['required', 'string', 'max:255'],
            'deductions.*.amount' => ['required', 'numeric', 'min:0'],
            'deductions.*.is_active' => ['nullable', 'boolean'],
            'deductions.*.is_default' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated): void {
            $settingId = DB::table('tax_settings')->orderByDesc('id')->value('id');
            $setting = ['rate' => $validated['tax_rate'], 'updated_at' => now()];

            if ($settingId) {
                DB::table('tax_settings')->where('id', $settingId)->update($setting);
            } else {
                DB::table('tax_settings')->insert($setting + ['created_at' => now()]);
            }

            $keptIds = [];
            foreach (array_values($validated['deductions'] ?? []) as $index => $deduction) {
                $values = [
                    'name' => trim($deduction['name']),
                    'amount' => $deduction['amount'],
                    'is_active' => (bool) ($deduction['is_active'] ?? false),
                    'is_default' => (bool) ($deduction['is_default'] ?? false),
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                ];

                if (! empty($deduction['id'])) {
                    DB::table('pre_tax_deductions')->where('id', $deduction['id'])->update($values);
                    $keptIds[] = (int) $deduction['id'];
                } else {
                    $keptIds[] = DB::table('pre_tax_deductions')->insertGetId($values + ['created_at' => now()]);
                }
            }

            DB::table('pre_tax_deductions')
                ->when($keptIds, fn ($query) => $query->whereNotIn('id', $keptIds))
                ->delete();
        });

        return redirect()->route('settings.tax-deductions')
            ->with('success', 'Tax and pre-tax deduction settings were updated successfully.');
    }
}
