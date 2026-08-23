<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerApplication;
use App\Services\Seller\SellerStatus;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Becoming a seller.
 *
 * The app calls `status` on every profile load and draws the business section
 * from the answer, so a farmer is never offered a screen the API will refuse.
 */
class SellerApplicationController extends Controller
{
    public function __construct(private readonly SellerStatus $status) {}

    /** Where this account stands. Safe for every authenticated user. */
    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'seller' => $this->status->payload($request->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($this->status->canSell($user)) {
            return response()->json([
                'message' => __('seller.already_seller'),
                'seller' => $this->status->payload($user),
            ], 409);
        }

        $existing = $this->status->latestApplication($user);
        if ($existing?->isPending()) {
            // Not an error worth an exception - the app may simply have been
            // reopened. Answer with the current state so it can show the
            // pending card rather than a failure.
            return response()->json([
                'message' => __('seller.already_pending'),
                'seller' => $this->status->payload($user),
            ], 409);
        }

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'business_type' => ['required', 'string', 'in:agrodealer,farmer_producer,cooperative,transporter'],
            'region' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['required', 'string', 'regex:/^255[0-9]{9}$/'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $application = new SellerApplication($validated);
        $application->user_id = $user->id;
        $application->save();

        return response()->json([
            'message' => __('seller.submitted'),
            'seller' => $this->status->payload($user->refresh()),
        ], 201);
    }

    /**
     * Administrative review.
     *
     * Approval grants the selling role, so it is gated on the permission
     * rather than on a role string - and never on `User::can()`, which this
     * codebase overrides to return true for admin *and* superadmin and so
     * cannot tell privileged actions apart.
     */
    public function review(Request $request, string $uuid): JsonResponse
    {
        $reviewer = $request->user();

        if (! in_array($reviewer->role, [Roles::ADMIN, Roles::SUPERADMIN], true)) {
            return response()->json(['message' => __('seller.review_forbidden')], 403);
        }

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:approve,reject'],
            'reason' => ['required_if:decision,reject', 'nullable', 'string', 'max:500'],
            'grant_role' => ['nullable', 'string', 'in:'.Roles::SELLER.','.Roles::AGRODEALER],
        ]);

        $application = SellerApplication::where('uuid', $uuid)->firstOrFail();

        if ($validated['decision'] === 'approve') {
            $application->approve($reviewer, $validated['grant_role'] ?? Roles::SELLER);
        } else {
            $application->reject($reviewer, $validated['reason']);
        }

        return response()->json([
            'message' => __('seller.reviewed'),
            'seller' => $this->status->payload($application->user->refresh()),
        ]);
    }
}
