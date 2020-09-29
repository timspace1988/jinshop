<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentsController extends Controller
{
    //show installments for user
    public function index(Request $request){
        $installments = Installment::query()->where('user_id', $request->user()->id) 
                                            ->paginate(10);
                            
        return view('installments.index', ['installments' => $installments]);
    }

    //show user's installment details page
    public function show(Installment $installment){
        $this->authorize('own', $installment);

        //Get all installment items for current installment, and sort them by payment sequence
        $items = $installment->items()->orderBy('sequence')->get();
        return view('installments.show', [
                                            'installment' => $installment, 
                                            'items' => $items,
                                            //next unpaid installment item
                                            'nextItem' => $items->where('paid_at', null)->first(),//note: here ->where() is executed on $items collection, not on database
                                        ]);
    }
}
