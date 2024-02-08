<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\DetailTransaction;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Pusher\Pusher;


class TransactionController extends Controller
{


    public function sendMessage()
    {
       
        $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), [
            'cluster' => env('PUSHER_APP_CLUSTER'),
        ]);

        $data = DetailTransaction::
        select('transaction.code as order_number','transaction.status','transaction.payment_method','detail_transaction.*','product.name','product.code')
        ->join('product', function ($join){
        $join->on('product.id', '=', 'detail_transaction.id_product');
        })
        ->join('transaction', function ($join){
            $join->on('transaction.id', '=', 'detail_transaction.id_transaction');
        })->get();


      
      //return $data;
        $pusher->trigger('messages', 'send', $data);
    }

    public function index(Request $request)
    {
       

        $perPage = $request->input('perPage', 8); // Jumlah data per halaman (default: 10)

    if ($request->filled('category') or $request->filled('price') or $request->filled('code') or $request->filled('status') or $request->filled('payment_method') or $request->filled('date') or $request->filled('product') or $request->filled('startDate') or $request->filled('endDate')) {
      
        $cash_persentase = 0;
        $cashless_persentase = 0;
        $pending_persentase = 0;

        $category = $request->input('category');
        $price = $request->input('price');
        $quantity = $request->input('quantity');
        $date = $request->input('date');
        $product = $request->input('product');
        $code = $request->input('code');
        $status = $request->input('status');
        $payment_method = $request->input('payment_method');

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $data = DetailTransaction::select('transaction.code as order_number','transaction.status','transaction.payment_method','detail_transaction.*','product.name','product.code')
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
        ->join('transaction', function ($join) use ($request,$code,$status,$payment_method,$startDate,$endDate){
            $join->on('transaction.id', '=', 'detail_transaction.id_transaction');
            if ($request->filled('code')){
                $join->where('transaction.code',$code);
            } 
            if ($request->filled('status')){
                $join->where('transaction.status',$status);
            } 
            if ($request->filled('payment_method')){
                $join->where('transaction.payment_method',$payment_method);
            } 
            if ($request->filled('startDate')){
                $join->whereBetween('transaction.created_at', [$startDate, $endDate]);
            } 
           
        })
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        $transaction_amaount = DetailTransaction::
        when($price, function ($query) use ($price) {
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
        ->join('transaction', function ($join) use ($request,$code,$status,$payment_method){
            $join->on('transaction.id', '=', 'detail_transaction.id_transaction');
            if ($request->filled('code')){
                $join->where('transaction.code',$code);
            } 
            if ($request->filled('status')){
                $join->where('transaction.status',$status);
            } 
            if ($request->filled('payment_method')){
                $join->where('transaction.payment_method',$payment_method);
            } 
        })->count();



        $total_quantity = DetailTransaction::
        when($price, function ($query) use ($price) {
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
        ->join('transaction', function ($join) use ($request,$code,$status,$payment_method){
            $join->on('transaction.id', '=', 'detail_transaction.id_transaction');
            if ($request->filled('code')){
                $join->where('transaction.code',$code);
            } 
            if ($request->filled('status')){
                $join->where('transaction.status',$status);
            } 
            if ($request->filled('payment_method')){
                $join->where('transaction.payment_method',$payment_method);
            } 
        })->sum('quantity');


      
        $total_amount = DetailTransaction::selectRaw('SUM(detail_transaction.price * quantity) as total_amount')
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
        ->join('transaction', function ($join) use ($request,$code,$status,$payment_method){
            $join->on('transaction.id', '=', 'detail_transaction.id_transaction');
            if ($request->filled('code')){
                $join->where('transaction.code',$code);
            } 
            if ($request->filled('status')){
                $join->where('transaction.status',$status);
            } 
            if ($request->filled('payment_method')){
                $join->where('transaction.payment_method',$payment_method);
            } 
        })
        ->value('total_amount');


    }else {

//
        $data = DetailTransaction::
        select('transaction.code as order_number','transaction.status','transaction.payment_method','detail_transaction.*','product.name','product.code')
        ->join('product', function ($join){
        $join->on('product.id', '=', 'detail_transaction.id_product');
        })
        ->join('transaction', function ($join){
            $join->on('transaction.id', '=', 'detail_transaction.id_transaction');
        })
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        // $transaction_amaount = Transaction::with('DetailTransaction')
        // ->join('transaction.id', '=', 'detail_transaction.id_transaction')
        // ->count();

        $transaction_amaount = DB::table('transaction')
        ->join('detail_transaction','transaction.id', '=', 'detail_transaction.id_transaction')
        ->count();


        $total_quantity = DetailTransaction::sum('quantity');
        $total_amount = DetailTransaction::selectRaw('SUM(price * quantity) as total_amount')->value('total_amount');

        $cashless = Transaction::with('DetailTransaction')
        ->join('detail_transaction','transaction.id', '=', 'detail_transaction.id_transaction')
        ->where('payment_method','Cashless')->count();

        $cash = Transaction::with('DetailTransaction')
        ->join('detail_transaction','transaction.id', '=', 'detail_transaction.id_transaction')
        ->where('payment_method','Cash')->count();

        $pending = Transaction::with('DetailTransaction')
        ->join('detail_transaction','transaction.id', '=', 'detail_transaction.id_transaction')
        ->where('status','Pending')->count();

        $cash_persentase = 0;
        $cashless_persentase = 0;
        $pending_persentase = 0
        ;
        if ($transaction_amaount!=0){
            $cash_persentase = $cash/$transaction_amaount*100;
            $cashless_persentase = $cashless/$transaction_amaount*100;
            $pending_persentase = $pending/$transaction_amaount*100;
        }
    
    }
    

        $result = [
            'result' => $data,
            'transaction_amaount' => $transaction_amaount,
            'total_amount' => $total_amount,
            'total_quantity'=>$total_quantity,
            'cash'=>number_format($cash_persentase, 2),
            'cashless'=>number_format($cashless_persentase, 2),
            'pending'=>number_format($pending_persentase, 2),
        ];


        return response()->json($result);
    }

    public function getPayment(Request $request){
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
                'first_name' => 'cuttomer',
                'email' => 'customer@gmail.com'
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
    }
    
    public function createTransaction(Request $request){

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
            $code = now()->format('ym') . str_pad($counter, 4, '0', STR_PAD_LEFT);
            $payment = $request->input('payment');

            if($payment=='Cash'){
                $status ='Settlement';
            }else {
                $status ='Pending';
            }
            // Tabel transaction
            $transaction = Transaction::create([
                'code' => $code,
                'date' => now(),
                'payment_method' => $payment,
                'payment_type' => '',
                'status' => $status,
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


            $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), [
                'cluster' => env('PUSHER_APP_CLUSTER'),
            ]);
    
          
          //return $data;
            $pusher->trigger('notification', 'new_order', $cartItems);
          //  $pusher->trigger('messages', 'transaction', $id_tr->id);
        

            return response()->json(['message' => 'Data berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
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

    public function getTransaction($id){
        $data = DetailTransaction::with(['transaction','product'])->where('id_transaction',$id)->first();
        //$data = DetailTransaction::findOrFail($id);
        return $data;
    }

    public function getDetailTransaction($id){
        $data = DetailTransaction::findOrFail($id);
        return $data;
    }
}
