<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeliveryFeeController extends Controller
{
    public function index(): View
    {
        return view('admin.delivery-fees.index', [
            'admin' => request()->user('admin'),
            'fees' => DB::table('delivery_fees')->orderBy('municipality')->get(),
        ]);
    }

    public function store(Request $request, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $request->merge([
            'municipality' => trim((string) $request->input('municipality')),
        ]);
        $validated = $request->validate([
            'municipality' => ['required', 'string', 'max:50', Rule::unique('delivery_fees', 'municipality')],
            'fee' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        $feeId = DB::table('delivery_fees')->insertGetId([
            'municipality' => $validated['municipality'],
            'fee' => $validated['fee'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $activityLogger->record('created', 'delivery_fee', (int) $feeId, "Delivery area {$validated['municipality']} added.");

        return redirect()->route('admin.delivery-fees.index')
            ->with('status', 'Municipality added.');
    }

    public function update(Request $request, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $feeIds = DB::table('delivery_fees')->orderBy('delivery_fee_id')->pluck('delivery_fee_id');
        $rules = [
            'fees' => ['required', 'array:'.$feeIds->implode(',')],
        ];

        foreach ($feeIds as $feeId) {
            $rules["fees.{$feeId}"] = ['required', 'numeric', 'min:0', 'max:999999.99'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $feeIds): void {
            foreach ($feeIds as $feeId) {
                DB::table('delivery_fees')
                    ->where('delivery_fee_id', $feeId)
                    ->update([
                        'fee' => $validated['fees'][$feeId],
                        'updated_at' => now(),
                    ]);
            }
        });
        $activityLogger->record('updated', 'delivery_fees', null, 'Delivery fees updated.');

        return redirect()->route('admin.delivery-fees.index')
            ->with('status', 'Delivery fees updated.');
    }

    public function destroy(int $deliveryFeeId, AdminActivityLogger $activityLogger): RedirectResponse
    {
        $fee = DB::table('delivery_fees')->where('delivery_fee_id', $deliveryFeeId)->first();
        abort_if($fee === null, 404);

        DB::table('delivery_fees')->where('delivery_fee_id', $deliveryFeeId)->delete();
        $activityLogger->record('deleted', 'delivery_fee', $deliveryFeeId, "Delivery area {$fee->municipality} removed.");

        return redirect()->route('admin.delivery-fees.index')
            ->with('status', "Municipality '{$fee->municipality}' removed.");
    }
}
