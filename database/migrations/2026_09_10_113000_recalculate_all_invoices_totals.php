<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Invoice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $invoices = Invoice::with(['registrations.competition', 'registrations.members'])->get();
        foreach ($invoices as $invoice) {
            $invoice->recalculateTotals();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
