<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressesRequest;
use App\Models\UserAddress;
use Illuminate\Http\Request;


class UserAddressesController extends Controller
{
    //Show user address list
    public function index(Request $request){
        return view('user_addresses.index', ['addresses' => $request->user()->addresses]);
    }

    //Go to "add address" page
    public function create(){
        return view('user_addresses.create_and_edit', ['address' => new UserAddress()]);
    }

    //Create and store new address and redirect to user address list page
    public function store(UserAddressesRequest $request){
        //user()->addresses() will return a has-many relationship, not addresses set 
        $request->user()->addresses()->create($request->only([
            'state',
            'suburb',
            'postcode',
            'address',
            'contact_name',
            'contact_phone',
        ]));

        return redirect()->route('user_addresses.index');
    }

    //Go to "edit address" page
    public function edit(UserAddress $user_address){
        //Customers can only edit their own address 
        $this->authorize('own', $user_address);
        
        return view('user_addresses.create_and_edit', ['address' =>$user_address]);
    }

    //Update an address
    public function update(UserAddress $user_address, UserAddressesRequest $request){
        $this->authorize('own', $user_address);

        $user_address->update($request->only([
            'state',
            'suburb',
            'postcode',
            'address',
            'contact_name',
            'contact_phone',
        ]));

        return redirect()->route('user_addresses.index');
    }

    //Destroy an address
    public function destroy(UserAddress $user_address){
        $this->authorize('own', $user_address);

        $user_address->delete();
        //return redirect()->route('user_addresses.index');
        //As we send ajax request here, we need to return a empty array, ajax will use a callback function to reload the page
        return [];
    }
}

