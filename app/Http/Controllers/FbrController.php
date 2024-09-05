<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;

class FbrController extends Controller
{
    public function receiveInvoiceWebhook(Request $request)
    {
        return response()->json(['message' => 'Webhook received successfully'], 200);
        // Step 0: Log the incoming request
        Log::info('Received Invoice Webhook: ' . $request->all());
        // Step 1: Validate the incoming request data
        $validated = $request->validate([
            'branch_sequence_no'   => 'required|string',
            'total_bill_amount'    => 'required|numeric',
            'total_sales_value'    => 'required|numeric',
            'total_qty'            => 'required|integer',
            'total_vat'            => 'required|numeric',
            'total_discount'       => 'required|numeric',
            'fbr_payment_mode'     => 'required|string',
            'fbrItems'             => 'required|array',
            'jurisdiction'         => 'required|string|in:federal,pra',
            'pos_id'               => 'required|string',
            'branch_token'         => 'required|string',
            'gst_rate'             => 'required|numeric',
            'orderID'              => 'required|string',
        ]);

        try {
            // Step 2: Extract validated data
            $branch_sequence_no = $validated['branch_sequence_no'];
            $total_bill_amount  = $validated['total_bill_amount'];
            $total_sales_value  = $validated['total_sales_value'];
            $total_qty          = $validated['total_qty'];
            $total_vat          = $validated['total_vat'];
            $total_discount     = $validated['total_discount'];
            $fbr_payment_mode   = $validated['fbr_payment_mode'];
            $fbrItems           = $validated['fbrItems'];
            $jurisdiction       = $validated['jurisdiction'];
            $pos_id             = $validated['pos_id'];
            $branch_token       = $validated['branch_token'];
            $gst_rate           = $validated['gst_rate'];
            $orderID            = $validated['orderID'];

            // Step 3: Call the sendToFbr method
            $response = $this->sendToFbr(
                $branch_sequence_no,
                $total_bill_amount,
                $total_sales_value,
                $total_qty,
                $total_vat,
                $total_discount,
                $fbr_payment_mode,
                $fbrItems,
                $jurisdiction,
                $pos_id,
                $branch_token,
                $gst_rate,
                $orderID
            );

            // Step 4: Return a successful response
            return response()->json([
                'success' => true,
                'message' => 'Invoice processed successfully.',
                'data'    => $response,
            ], 200);
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

    /**
     * Send invoice data to FBR.
     *
     * @param  string  $branch_sequence_no
     * @param  float   $total_bill_amount
     * @param  float   $total_sales_value
     * @param  int     $total_qty
     * @param  float   $total_vat
     * @param  float   $total_discount
     * @param  string  $fbr_payment_mode
     * @param  array   $fbrItems
     * @param  string  $jurisdiction
     * @param  string  $pos_id
     * @param  string  $branch_token
     * @param  float   $gst_rate
     * @param  string  $orderID
     * @return mixed
     */
    public function sendToFbr(
        $branch_sequence_no,
        $total_bill_amount,
        $total_sales_value,
        $total_qty,
        $total_vat,
        $total_discount,
        $fbr_payment_mode,
        $fbrItems,
        $jurisdiction,
        $pos_id,
        $branch_token,
        $gst_rate,
        $orderID
    ) {
        // Prepare the invoice data to send to FBR
        $invoice_to_fbr = [
            "InvoiceNumber"   => "",
            "POSID"           => $pos_id,
            "USIN"            => $branch_sequence_no,
            "DateTime"        => now(),
            // "BuyerNTN" => "",
            // "BuyerCNIC" => "",
            // "BuyerName" => "",
            // "BuyerPhoneNumber" => "",
            "TotalBillAmount" => $total_bill_amount + 1, // Verify if +1 is intentional
            "TotalQuantity"   => $total_qty,
            "TotalSaleValue"  => $total_sales_value,
            "TotalTaxCharged" => $total_vat,
            "Discount"        => $total_discount,
            "FurtherTax"      => 0.0,
            "PaymentMode"     => $fbr_payment_mode,
            "RefUSIN"         => null,
            "InvoiceType"     => 1,
            "Items"           => $fbrItems,
        ];

        $max_attempts = 5;
        $attempts     = 0;
        $fbr_live     = env('FBR_LIVE', 0);
        $branch_jurisdiction = $jurisdiction;
        $response_contents   = null;
        $url                 = null;

        // Determine the FBR endpoint based on jurisdiction
        if ($fbr_live == 1) {
            if ($branch_jurisdiction === 'federal') {
                $url = 'https://gw.fbr.gov.pk/imsp/v1/api/Live/PostData';
            } elseif ($branch_jurisdiction === 'pra') {
                $url = 'https://ims.pral.com.pk/ims/production/api/Live/PostData';
            }
        }

        // Attempt to send the data to FBR
        if ($url) {
            do {
                try {
                    $fbr_post_request = Http::withToken($branch_token)
                        ->withOptions(['verify' => false]) // Consider enabling SSL verification in production
                        ->post($url, $invoice_to_fbr);

                    if ($fbr_post_request->successful()) {
                        $response_contents = $fbr_post_request->json();
                        break; // Exit loop on success
                    } else {
                        Log::warning('FBR API responded with an error', [
                            'status' => $fbr_post_request->status(),
                            'body'   => $fbr_post_request->body(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending data to FBR: ' . $e->getMessage());
                }

                $attempts++;
            } while ($response_contents === null && $attempts < $max_attempts);
        }

        // Handle the response
        if ($response_contents === null) {
            // All attempts failed
            $order = Orders::where('orderID', $orderID)->first();
            if ($order) {
                $order->fbrinvoicenumber = "error - fbr server down";
                $order->save();
            }
        } else {
            // Successful response from FBR
            $order = Orders::where('orderID', $orderID)->first();
            if ($order && isset($response_contents['InvoiceNumber'])) {
                $order->fbrinvoicenumber = $response_contents['InvoiceNumber'];
                $order->save();
            }
        }

        return $response_contents;
    }
}
