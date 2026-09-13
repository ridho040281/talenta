<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('payment_adjustments')) {
            Schema::create('payment_adjustments', function (Blueprint $table) {
                $table->id();
                $table->string('reference_type'); // 'registration' or 'invoice'
                $table->unsignedBigInteger('reference_id');
                $table->string('adjustment_type')->default('refund_cancellation'); // refund_overpayment, refund_cancellation, discount, correction
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('bank_account')->nullable(); // info rekening tujuan transfer balik
                $table->string('proof_file')->nullable(); // file bukti transfer pengembalian uang
                $table->text('reason')->nullable(); // catatan / alasan pengembalian dana
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['reference_type', 'reference_id']);
                $table->index('adjustment_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_adjustments');
    }
};
