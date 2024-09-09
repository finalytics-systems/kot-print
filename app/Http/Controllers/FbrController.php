<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;

class FbrController extends Controller
{
    public function receiveInvoiceWebhook(Request $request)
    {
        // Step 0: Log the incoming request
        Log::info($request->all());
        $last_inv = Invoice::orderBy('branch_inv_no', 'desc')->first();
        $next_inv_no = $last_inv ? $last_inv->branch_inv_no + 1 : 1;
        // Step 1: Validate the incoming request data
        $validated = $request->validate([
            'ntn_no'               => 'required|string',
            'erp_invoice_no'       => 'required|string',
            'total_bill_amount'    => 'required|numeric',
            'total_sales_value'    => 'required|numeric',
            'total_qty'            => 'required|integer',
            'total_vat'            => 'required|numeric',
            'total_discount'       => 'required|numeric',
            'payment_mode'         => 'required|string',
            'fbrItems'             => 'required|array',
            'jurisdiction'         => 'required|string|in:federal,pra',
            'pos_profile'          => 'required|string',
            'pos_shift'            => 'required|string',
            'fbr_pos_id'           => 'required|string',
            'fbr_branch_token'     => 'required|string',
            'gst_rate'             => 'required|numeric'
        ]);

        try {
            $invoice_to_fbr = [
                "InvoiceNumber" => "",
                "POSID" => $validated['fbr_pos_id'],
                "USIN" => $next_inv_no,
                "DateTime" => now(),
                "TotalBillAmount" => $validated['total_bill_amount'] + 1,
                "TotalQuantity" => $validated['total_qty'],
                "TotalSaleValue" => $validated['total_bill_amount'],
                "TotalTaxCharged" => $validated['total_vat'],
                "Discount" => $validated['total_discount'],
                "FurtherTax" => 0.0,
                "PaymentMode" => $validated['payment_mode'] == 'Cash' ? 1 : 2,
                "RefUSIN" => null,
                "InvoiceType" => 1,
                "Items" => $validated['fbrItems']
            ];


            $max_attempts = 5; // Set the maximum number of attempts
            $attempts = 0;
            $fbr_live = env('FBR_LIVE');
            $branch_jurisdiction = $validated['jurisdiction'];
            $branch_token = $validated['fbr_branch_token'];

            if ($fbr_live == 0) {
                $fbr_post_request = Http::withHeaders(['Authorization' => 'Bearer ' . '1298b5eb-b252-3d97-8622-a4a69d5bf818', 'Cache-Control' => 'no-cache'])
                    ->withOptions(["verify" => false])
                    ->post('https://esp.fbr.gov.pk:8244/FBR/v1/api/Live/PostData', $invoice_to_fbr);
                $response_contents = json_decode((string) $fbr_post_request);
            }

            if ($fbr_live == 1) {
                if ($branch_jurisdiction === 'federal') {
                    do {
                        $fbr_post_request = Http::withToken($branch_token)
                            ->withOptions(["verify" => false])
                            ->post('https://gw.fbr.gov.pk/imsp/v1/api/Live/PostData', $invoice_to_fbr);

                        $response_contents = json_decode((string) $fbr_post_request);

                        $attempts++;
                    } while ($response_contents === null && $attempts < $max_attempts);
                } elseif ($branch_jurisdiction === 'pra') {
                    do {
                        $fbr_post_request = Http::withToken($branch_token)
                            ->withOptions(["verify" => false])
                            ->post('https://ims.pral.com.pk/ims/production/api/Live/PostData', $invoice_to_fbr);

                        $response_contents = json_decode((string) $fbr_post_request);

                        $attempts++;
                    } while ($response_contents === null && $attempts < $max_attempts);
                }
            }

            if ($response_contents) {
                $invoice = new Invoice();
                $invoice->ntn_no = $validated['ntn_no'];
                $invoice->erp_invoice_no = $validated['erp_invoice_no'];
                $invoice->branch_inv_no = $next_inv_no;
                $invoice->fbr_invoice_no = $response_contents->InvoiceNumber;
                $invoice->total_bill_amount = $validated['total_bill_amount'];
                $invoice->total_sales_value = $validated['total_sales_value'];
                $invoice->total_qty = $validated['total_qty'];
                $invoice->total_vat = $validated['total_vat'];
                $invoice->total_discount = $validated['total_discount'];
                $invoice->payment_mode = $validated['payment_mode'];
                $invoice->fbr_items = json_encode($validated['fbrItems']);
                $invoice->jurisdiction = $validated['jurisdiction'];
                $invoice->pos_profile = $validated['pos_profile'];
                $invoice->fbr_pos_id = $validated['fbr_pos_id'];
                $invoice->pos_shift = $validated['pos_shift'];
                $invoice->gst_rate = $validated['gst_rate'];
                $invoice->save();
            }

            $this->sendDataToAPI($invoice);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error processing webhook: ' . $e->getMessage());

            // Return an error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the invoice.',
            ], 500);
        }
    }

    public function sendDataToAPI($data){
        
    }
}
