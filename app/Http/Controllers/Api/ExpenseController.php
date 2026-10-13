<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExpenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenses,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('expenses.view'), 403);

        $expenses = Expense::query()
            ->with(['category', 'currency', 'paymentMethod'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('expense_date')
            ->paginate(20);

        return ExpenseResource::collection($expenses);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('expenses.create'), 403);

        $companyId = $request->user()->company_id;
        $data = $request->validate([
            'stock_id' => [
                'nullable',
                'integer',
                Rule::exists('stocks', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'expense_category_id' => [
                'required',
                'integer',
                Rule::exists('expense_categories', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'cash_session_id' => ['nullable', 'integer', Rule::exists('cash_sessions', 'id')],
            'reference' => ['nullable', 'string', 'max:60'],
            'description' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_id' => ['required', 'integer', Rule::exists('currencies', 'id')],
            'payment_method_id' => ['required', 'integer', Rule::exists('payment_methods', 'id')],
            'expense_date' => ['required', 'date'],
            'status' => ['sometimes', Rule::enum(ExpenseStatus::class)],
            'receipt_path' => ['nullable', 'string', 'max:255'],
        ]);

        $expense = $this->expenses->create($request->user(), $data);

        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Expense $expense): ExpenseResource
    {
        abort_unless($request->user()->can('expenses.view'), 403);

        return new ExpenseResource($expense->load(['category', 'currency', 'paymentMethod', 'cashSession', 'stock']));
    }

    public function validateExpense(Request $request, Expense $expense): ExpenseResource
    {
        abort_unless($request->user()->can('expenses.validate'), 403);

        return new ExpenseResource($this->expenses->validate($expense, $request->user()));
    }

    public function cancel(Request $request, Expense $expense): ExpenseResource
    {
        abort_unless($request->user()->can('expenses.cancel'), 403);

        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string'],
        ]);

        return new ExpenseResource(
            $this->expenses->cancel($expense, $request->user(), $data['cancellation_reason'] ?? null)
        );
    }
}
