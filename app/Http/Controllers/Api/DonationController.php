<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDonationRequest;
use App\Http\Resources\DonationResource;
use App\Services\Interfaces\DonationServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    use ApiResponse;

    public function __construct(protected DonationServiceInterface $donationService)
    {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $donations = $this->donationService->getAllDonations($perPage);
        return $this->successWithPagination(DonationResource::collection($donations), 'Donations retrieved successfully');
    }

    public function store(StoreDonationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $donation = $this->donationService->createDonation($data);
        return $this->success(new DonationResource($donation), 'Donation initiated successfully', 201);
    }

    public function show(string $number): JsonResponse
    {
        try {
            $donation = $this->donationService->getDonationByNumber($number);
            return $this->success(new DonationResource($donation), 'Donation details retrieved');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }
}
