<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('ntn_no');
            $table->string('erp_invoice_no');
            $table->integer('branch_inv_no');
            $table->string('fbr_invoice_no');
            $table->timestamp('inv_date')->useCurrent();
            $table->decimal('total_bill_amount', 10, 2);
            $table->decimal('total_sales_value', 10, 2);
            $table->decimal('total_qty', 10, 2);
            $table->decimal('total_vat', 10, 2);
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->string('payment_mode');
            $table->json('fbr_items')->nullable();
            $table->enum('jurisdiction', ['federal', 'provincial'])->default('federal');
            $table->string('pos_profile');
            $table->string('pos_shift');
            $table->string('fbr_pos_id');
            $table->integer('gst_rate');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
