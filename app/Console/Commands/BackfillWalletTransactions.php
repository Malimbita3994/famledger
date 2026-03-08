<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\Income;
use App\Models\SavingsContribution;
use App\Models\Transfer;
use App\Services\WalletTransactionService;
use Illuminate\Console\Command;

class BackfillWalletTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-wallet-transactions {--family_id= : Process only specific family}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill wallet transactions for existing income, expense, transfer, and savings contribution records';

    protected WalletTransactionService $transactionService;

    public function __construct(WalletTransactionService $transactionService)
    {
        parent::__construct();
        $this->transactionService = $transactionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $familyId = $this->option('family_id');

        $this->info('Starting wallet transaction backfill...');

        // Process incomes
        $this->backfillIncomes($familyId);

        // Process expenses
        $this->backfillExpenses($familyId);

        // Process transfers
        $this->backfillTransfers($familyId);

        // Process savings contributions
        $this->backfillSavingsContributions($familyId);

        $this->info('Wallet transaction backfill completed!');
    }

    private function backfillIncomes($familyId = null)
    {
        $query = Income::with('wallet');
        if ($familyId) {
            $query->where('family_id', $familyId);
        }

        $incomes = $query->get();
        $this->info("Processing {$incomes->count()} incomes...");

        $bar = $this->output->createProgressBar($incomes->count());
        $bar->start();

        foreach ($incomes as $income) {
            try {
                $this->transactionService->recordIncome($income);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Failed to process income {$income->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
    }

    private function backfillExpenses($familyId = null)
    {
        $query = Expense::with('wallet');
        if ($familyId) {
            $query->where('family_id', $familyId);
        }

        $expenses = $query->get();
        $this->info("Processing {$expenses->count()} expenses...");

        $bar = $this->output->createProgressBar($expenses->count());
        $bar->start();

        foreach ($expenses as $expense) {
            try {
                $this->transactionService->recordExpense($expense);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Failed to process expense {$expense->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
    }

    private function backfillTransfers($familyId = null)
    {
        $query = Transfer::with(['fromWallet', 'toWallet']);
        if ($familyId) {
            $query->where('family_id', $familyId);
        }

        $transfers = $query->get();
        $this->info("Processing {$transfers->count()} transfers...");

        $bar = $this->output->createProgressBar($transfers->count());
        $bar->start();

        foreach ($transfers as $transfer) {
            try {
                $this->transactionService->recordTransfer($transfer);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Failed to process transfer {$transfer->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
    }

    private function backfillSavingsContributions($familyId = null)
    {
        $query = SavingsContribution::with(['fromWallet', 'goal']);
        if ($familyId) {
            $query->whereHas('goal', function ($q) use ($familyId) {
                $q->where('family_id', $familyId);
            });
        }

        $contributions = $query->get();
        $this->info("Processing {$contributions->count()} savings contributions...");

        $bar = $this->output->createProgressBar($contributions->count());
        $bar->start();

        foreach ($contributions as $contribution) {
            try {
                $this->transactionService->recordSavingsContribution($contribution);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Failed to process savings contribution {$contribution->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
    }
}
