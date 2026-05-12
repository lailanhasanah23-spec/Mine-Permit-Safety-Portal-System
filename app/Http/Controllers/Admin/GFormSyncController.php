<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GFormSyncController extends Controller
{
    /**
     * Handle incoming data from Google Forms (via Google Apps Script).
     */
    public function sync(Request $request)
    {
        // 1. Security Check (Secret Token)
        $secretToken = config('services.gform.token', 'LCM_SECRET_2026');
        if ($request->header('X-GForm-Token') !== $secretToken) {
            Log::warning('Unauthorized GForm Sync attempt from IP: '.$request->ip());

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Validate incoming data
        $validated = $request->validate([
            'category_code' => 'required|string',
            'applicant_name' => 'required|string|max:190',
            'item_type' => 'required|string|max:100',
            'item_identifier' => 'nullable|string|max:100',
        ]);

        // 3. Find Category
        $category = Category::where('code', $validated['category_code'])->first();
        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // 4. Create Submission
        // Logic: SIMPER goes to HRGA, Permits go to SHE
        $initialStatus = ($category->code === 'SIMPER_PERMIT') ? 'pending_hrga' : 'pending_she';

        $submission = Submission::create([
            'category_id' => $category->id,
            'applicant_name' => $validated['applicant_name'],
            'item_type' => $validated['item_type'],
            'item_identifier' => $validated['item_identifier'],
            'status' => $initialStatus,
            'created_by' => null, // Created via System Sync
        ]);

        return response()->json([
            'message' => 'Success',
            'submission_id' => $submission->id,
            'status' => $submission->status,
        ], 201);
    }
}
