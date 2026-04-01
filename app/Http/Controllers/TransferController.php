<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesFamilyMember;
use App\Models\Family;
use App\Services\FamilyFinancialService;
use App\Services\WalletBalanceGuard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransferController extends Controller
{
    use AuthorizesFamilyMember;

    public function index(Family $family, Request $request)
    {
        $this->authorizeFamilyMember($family);

        $query = $family->transfers()->with([
            'fromWallet:id,family_id,name,currency_code',
            'toWallet:id,family_id,name,currency_code',
            'createdBy:id,name',
        ]);
        if ($request->filled('wallet_id')) {
            $wallet = $family->wallets()->find($request->wallet_id);
            if ($wallet) {
                $query->where(function ($q) use ($wallet) {
                    $q->where('from_wallet_id', $wallet->id)->orWhere('to_wallet_id', $wallet->id);
                });
            }
        }
        $transfers = $query->orderByDesc('transfer_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'currency_code']);

        $chartCurrency = $family->currency_code ?? config('currencies.default', 'TZS');
        $now = Carbon::now();
        $chartMonthKeys = [];
        $chartMonthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i)->startOfMonth();
            $chartMonthKeys[] = $d->format('Y-m-d');
            $chartMonthLabels[] = $d->format('M');
        }
        $chartStart = $chartMonthKeys[0];
        $chartEnd = $now->copy()->endOfMonth()->toDateString();

        $amountBucket = array_fill_keys($chartMonthKeys, 0.0);
        $countBucket = array_fill_keys($chartMonthKeys, 0);

        $transferRows = DB::table('transfers')
            ->where('family_id', $family->id)
            ->when($request->filled('wallet_id'), function ($q) use ($request) {
                $wid = $request->input('wallet_id');
                $q->where(function ($q2) use ($wid) {
                    $q2->where('from_wallet_id', $wid)->orWhere('to_wallet_id', $wid);
                });
            })
            ->where('transfer_date', '>=', $chartStart)
            ->where('transfer_date', '<=', $chartEnd)
            ->get(['transfer_date', 'amount']);

        foreach ($transferRows as $row) {
            $k = Carbon::parse($row->transfer_date)->startOfMonth()->format('Y-m-d');
            if (isset($amountBucket[$k])) {
                $amountBucket[$k] += (float) $row->amount;
                $countBucket[$k]++;
            }
        }

        $chartTransferAmountByMonth = array_map(fn ($v) => round($v, 2), array_values($amountBucket));
        $chartTransferCountByMonth = array_values($countBucket);

        return view('families.transfers.index', compact(
            'family',
            'transfers',
            'wallets',
            'chartMonthLabels',
            'chartTransferAmountByMonth',
            'chartTransferCountByMonth',
            'chartCurrency',
        ));
    }

    public function create(Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get();
        if ($wallets->count() < 2) {
            return redirect()
                ->route('families.wallets.index')
                ->with('error', 'You need at least two wallets to make a transfer. Create another wallet first.');
        }

        return view('families.transfers.create', compact('family', 'wallets'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $fromWallet = $family->wallets()->findOrFail($request->input('from_wallet_id'));
        $toWallet = $family->wallets()->findOrFail($request->input('to_wallet_id'));

        $validated = $request->validate([
            'from_wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'to_wallet_id' => [
                'required',
                'different:from_wallet_id',
                Rule::exists('wallets', 'id')->where('family_id', $family->id),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3', Rule::in([strtoupper($fromWallet->currency_code)])],
            'transfer_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'reference' => ['nullable', 'string', 'max:100'],
        ], [
            'from_wallet_id.required' => 'Please select the source wallet.',
            'to_wallet_id.required' => 'Please select the destination wallet.',
            'to_wallet_id.different' => 'Source and destination wallets must be different.',
            'amount.min' => 'Amount must be greater than zero.',
            'currency_code.in' => 'Currency must match both wallets.',
        ]);

        if (strtoupper($fromWallet->currency_code) !== strtoupper($toWallet->currency_code)) {
            return back()->withInput()->withErrors(['to_wallet_id' => 'Both wallets must use the same currency. Multi-currency transfers are not supported yet.']);
        }

        $financials = app(FamilyFinancialService::class);
        if ($financials->transferToSavingsBlockedByBudget($family, $toWallet)) {
            return back()
                ->withInput()
                ->withErrors([
                    'amount' => 'Cannot transfer to a savings wallet while any monthly budget is over its limit. Adjust spending or budgets first.',
                ]);
        }

        return DB::transaction(function () use ($family, $validated, $fromWallet, $toWallet) {
            $locked = WalletBalanceGuard::lockWalletsForUpdate([$fromWallet->id, $toWallet->id]);
            $from = $locked->get($fromWallet->id);
            if (! $from || ! $from->canAffordDebit((float) $validated['amount'])) {
                $available = $from ? $from->balance : 0;
                $message = 'Insufficient funds in the source wallet. Available balance is '.number_format($available, 2).' '.strtoupper($fromWallet->currency_code).'.';

                return back()
                    ->withInput()
                    ->withErrors([
                        'amount' => $message,
                    ])
                    ->with('error', $message);
            }

            $family->transfers()->create([
                'from_wallet_id' => $validated['from_wallet_id'],
                'to_wallet_id' => $validated['to_wallet_id'],
                'amount' => $validated['amount'],
                'currency_code' => strtoupper($validated['currency_code']),
                'transfer_date' => $validated['transfer_date'],
                'description' => $validated['description'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('families.transfers.index')
                ->with('success', 'Transfer recorded. Both wallet balances updated.');
        });
    }
}
