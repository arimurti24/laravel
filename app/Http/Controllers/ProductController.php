<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;

class ProductController extends Controller
{
    public function index(Request $request){

      

        if ($request->input('category')!=0){
            $category =  $request->input('category');
            $data =  Product::where('id_category',$category)->get();

          
        }else {
            $data =  Product::get();
        }


       
        return response()->json($data);
    }

    public function getCategory(){
        $data =  ProductCategory::get();
        return response()->json($data);
    }



    public function destroy($id)
    {
    
        try {

            $Model = Product::findOrFail($id);
            $Model->delete();

            return response()->json([
                'message'=>'Product Deleted Successfully!!'
            ]);
            
        } catch (\Exception $e) {
          
            return response()->json([
                'message'=>'Something goes wrong while deleting!!'
            ]);
        }
    }

}
