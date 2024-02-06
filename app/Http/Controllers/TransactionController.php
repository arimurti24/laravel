<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\DetailTransaction;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;


class TransactionController extends Controller
{


    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10); // Jumlah data per halaman (default: 10)

    if ($request->filled('category') or $request->filled('price') or $request->filled('quantity') or $request->filled('date') or $request->filled('product')) {
        $category = $request->input('category');
        $price = $request->input('price');
        $quantity = $request->input('quantity');
        $date = $request->input('date');
        $product = $request->input('product');

        $data = DetailTransaction::select('detail_transaction.*','product.name','product.code')
        ->when($price, function ($query) use ($price) {
            $query->where('detail_transaction.price', $price);
        })->when($quantity, function ($query) use ($quantity) {
            $query->where('detail_transaction.quantity', $quantity);
        })
        ->when($date, function ($query) use ($date) {
            $query->whereDate('detail_transaction.created_at','=',$date);
        })
        ->when($product, function ($query) use ($product) {
            $query->where('detail_transaction.id_product',$product);
        })
        ->join('product', function ($join) use ($category,$request){
            if ($request->filled('category')){
                $join->on('product.id', '=', 'detail_transaction.id_product')
                ->where('product.id_category',$category);
            } else {
                $join->on('product.id', '=', 'detail_transaction.id_product');
            }          
        })
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

    }else {


        $data = DetailTransaction::
        select('transaction.code as order_number','detail_transaction.*','product.name','product.code')
        ->join('product', function ($join){
        $join->on('product.id', '=', 'detail_transaction.id_product');
        })
        ->join('transaction', function ($join){
            $join->on('transaction.id', '=', 'detail_transaction.id_transaction');
        })
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }

 

      
        return response()->json($data);
    }
    
    public function createTransaction(Request $request){


        
        if ($request->filled('amount')){
            $amount = $request->input('amount');
            // Set configurasi Midtrans
            Config::$serverKey = config('services.midtrans.serverKey');
            Config::$isProduction = config('services.midtrans.isProduction');
            Config::$isSanitized = config('services.midtrans.isSanitized');
            Config::$is3ds = true; // Aktifkan 3D Secure

        
            // Buat transaksi
            $transaction = [
                'transaction_details' => [
                    'order_id' => uniqid(),
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => 'dimas',
                    'email' => 'dimas@gmail.com'
                ],
                'enabled_payments' => [
                    'shopeepay', 'gopay', 'permata_va', 'bank_transfer','QRIS','LinkAja'
                ],
                'vtweb' => [],
            ];

            try {
                // Dapatkan halaman pembayaran Snap
                $snapToken = Snap::getSnapToken($transaction);
                //return response()->json(['snapToken' => $snapToken]);
                return response()->json(['snapToken' => $snapToken])->header('Access-Control-Allow-Origin', '*');
            } catch (\Exception $e) {
                // Tangani kesalahan
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }else {
                // $cartItems = $request->input('items');
                // foreach ($cartItems as $item) {
                //     DetailTransaction::create([
                //         'id_transaction' => $item['id'],
                //         'id_product' => $item['id'],
                //         'quantity' => $item['quantity'],
                //         'price' => $item['price'],
                //     ]);
                // }


        try {
            DB::beginTransaction();

            $lastOrder = Transaction::latest()->first();
            $counter = $lastOrder ? $lastOrder->id + 1 : 1;
            $orderNumber = now()->format('Ym') . str_pad($counter, 4, '0', STR_PAD_LEFT);

            // Tabel transaction
            $transaction = Transaction::create([
                'code' => $orderNumber,
                'date' => now(),
            ]);
   
            $cartItems = $request->input('items');
            $id_tr = Transaction::latest()->first();
         
            foreach ($cartItems as $item) {
                DetailTransaction::create([
                    'id_transaction' => $id_tr->id,
                    'id_product' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
            DB::commit();

            return response()->json(['message' => 'Data berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }

         }

   

        
    

        
    }


    public function destroy($id)
    {
    
        try {

            $Model = DetailTransaction::findOrFail($id);
            $Model->delete();

            return response()->json([
                'message'=>'Deleted Successfully!!'
            ]);
            
        } catch (\Exception $e) {
          
            return response()->json([
                'message'=>'Something goes wrong while deleting!!'
            ]);
        }
    }


}
