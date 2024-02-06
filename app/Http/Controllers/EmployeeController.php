<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;


class EmployeeController extends Controller
{
    function index(){
        return Employee::select('id','nrp','name','age','phone_num','address')->get();
    }

    public function destroy($id)
    {
    
        try {

            $Model = Employee::findOrFail($id);
            $Model->delete();

            return response()->json([
                'message'=>'Employee Deleted Successfully!!'
            ]);
            
        } catch (\Exception $e) {
          
            return response()->json([
                'message'=>'Something goes wrong while deleting!!'
            ]);
        }
    }

}
