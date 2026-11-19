<?php

namespace App\Http\Controllers\Api\Public;

use Midtrans\Snap;
use App\Models\Cart;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class CheckoutController extends Controller implements HasMiddleware
{
    // property
    public $response;

    /**
     * middleware
     * @return array
     */
    public static function middleware(): array
    {
        return [
            'auth:api'
        ];
    }

    /**
     * __construct
     * @return void
     */
    public function __construct()
    {
        // set midtrans configuration
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized  = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds        = config('midtrans.is_3ds');
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            //create invoice code (dipastikan unik)
            do {
                $invoice = 'INV-' . mt_rand(1000, 9999);
            } while (Transaction::where('invoice', $invoice)->exists());

            //create transaction
            $transaction = Transaction::create([
                'customer_id'      => auth()->guard('api')->user()->id,
                'invoice'          => $invoice,
                'province_name'    => $request->province_name,
                'city_name'        => $request->city_name,
                'district_name'    => $request->district_name,
                'subdistrict_name' => $request->subdistrict_name,
                'zip_code'         => $request->zip_code,
                'full_address'     => $request->full_address,
                'weight'           => $request->weight,
                'total'            => $request->total,
                'status'           => 'PENDING',
            ]);

            //create shipping
            $transaction->shipping()->create([
                'transaction_id'   => $transaction->id,
                'shipping_courier' => $request->shipping_courier,
                'shipping_service' => $request->shipping_service,
                'shipping_cost'    => $request->shipping_cost,
            ]);

            //create items details
            $item_details = [];
            foreach (Cart::where('customer_id', auth()->guard('api')->user()->id)->get() as $cart) {

                //insert product ke table order
                $transaction->transactionDetails()->create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $cart->product_id,
                    'qty'            => $cart->qty,
                    'price'          => $cart->product->price * $cart->qty,
                ]);

                $item_details[] = [
                    'id'       => $cart->product->id,
                    'price'    => $cart->product->price * $cart->qty,
                    'quantity' => 1,
                    'name'     => $cart->product->title
                ];
            }

            //item detail shipping
            $item_details[] = [
                'id'       => 'SHIPPING',
                'price'    => $request->shipping_cost,
                'quantity' => 1,
                'name'     => 'Shipping Cost'
            ];

            //remove cart by customer
            Cart::with('product')
                ->where('customer_id', auth()->guard('api')->user()->id)
                ->delete();

            // Buat transaksi ke midtrans kemudian save snap tokennya.
            $payload = [
                'transaction_details' => [
                    'order_id'     => $transaction->invoice,
                    'gross_amount' => $transaction->total
                ],
                'customer_details' => [
                    'first_name'       => auth()->guard('api')->user()->name,
                    'email'            => auth()->guard('api')->user()->email,
                    'shipping_address' => $transaction->address
                ],
                'item_details' => $item_details
            ];

            //create snap token
            $snapToken = Snap::getSnapToken($payload);

            //update snap_token
            $transaction->snap_token = $snapToken;
            $transaction->save();

            //make response "snap_token"
            $this->response['snap_token'] = $snapToken;

        });

        //return response JSON
        return response()->json([
            'success' => true,
            'message' => 'Checkout Berhasil!',
            'data'    => $this->response
        ]);
    }

    /**
     * status
     * Cek status pembayaran ke Midtrans lalu perbarui order.
     * Dipakai saat webhook tidak bisa menjangkau server lokal.
     *
     * @param  string $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function status($invoice)
    {
        // order harus milik customer yang sedang login
        $transaction = Transaction::where('invoice', $invoice)
            ->where('customer_id', auth()->guard('api')->user()->id)
            ->firstOrFail();

        try {
            // tulis lengkap, karena "Transaction" di file ini adalah model App\Models\Transaction
            $midtrans = \Midtrans\Transaction::status($invoice);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi belum ditemukan di Midtrans',
            ], 404);
        }

        $state = $midtrans->transaction_status ?? null;
        $fraud = $midtrans->fraud_status ?? null;

        // sesuaikan 'SUCCESS' dengan nilai status "dibayar" di project-mu
        if ($state === 'settlement' || ($state === 'capture' && $fraud === 'accept')) {
            $transaction->status = 'SUCCESS';
        } elseif ($state === 'expire') {
            $transaction->status = 'EXPIRED';
        } elseif (in_array($state, ['deny', 'cancel', 'failure'])) {
            $transaction->status = 'FAILED';
        }

        $transaction->save();

        return response()->json([
            'success' => true,
            'data'    => [
                'invoice' => $invoice,
                'status'  => $transaction->status,
            ],
        ]);
    }
}