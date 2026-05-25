<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreWithdrawalRequest;
use App\Http\Requests\Api\ProcessWithdrawalRequest;
use App\Http\Resources\WithdrawalResource;
use App\Services\Interfaces\WithdrawalServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    use ApiResponse;

    protected WithdrawalServiceInterface $withdrawalService;

    public function __construct(WithdrawalServiceInterface $withdrawalService)
    {
        $this->withdrawalService = $withdrawalService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $withdrawals = $this->withdrawalService->getAllWithdrawals($perPage);

        return $this->successWithPagination(WithdrawalResource::collection($withdrawals), 'Withdrawals retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWithdrawalRequest $request
     * @return JsonResponse
     */
    public function store(StoreWithdrawalRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id();

            $withdrawal = $this->withdrawalService->requestWithdrawal($data);

            return $this->success(new WithdrawalResource($withdrawal), 'Withdrawal requested successfully', 201);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $withdrawal = $this->withdrawalService->getWithdrawalById($id);
            $withdrawal->load(['campaign', 'user', 'processor']);
            return $this->success(new WithdrawalResource($withdrawal), 'Withdrawal retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Process the specified withdrawal.
     *
     * @param ProcessWithdrawalRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function process(ProcessWithdrawalRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['processed_by'] = Auth::guard('admin-api')->id();
            
            if ($request->hasFile('transfer_proof')) {
                $data['transfer_proof'] = $request->file('transfer_proof');
            }

            $withdrawal = $this->withdrawalService->processWithdrawal($id, $data);

            return $this->success(new WithdrawalResource($withdrawal), "Withdrawal request updated to {$request->status}");
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        } catch (ModelNotFoundException $e) {
            return $this->error("Withdrawal with ID {$id} not found.", 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Search for resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->query('keyword', '');
        $perPage = $request->query('per_page', 10);
        
        $withdrawals = $this->withdrawalService->searchWithdrawals($keyword, $perPage);

        return $this->successWithPagination(WithdrawalResource::collection($withdrawals), 'Withdrawals search results retrieved successfully');
    }
}
