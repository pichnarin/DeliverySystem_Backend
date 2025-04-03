<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Address::all();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreAddressRequest $request)
    // {
    //     try{
    //         $data = Address::create($request->validated());
    //         return response()->json(['status' => 'success', 'data' => $data], 201);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function createAddress(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'city' => 'required|string|max:255',
                'state' => 'nullable|string|max:255',
                'zip' => 'nullable|string|max:255',
                'reference' => 'nullable|string|max:255',
            ]);

            $address = Address::create(
                [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'zip' => $validated['zip'],
                    'reference' => $validated['reference'],
                    'customer_id' => $request->user()->id
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Address placed successfully',
                'data' => $address
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating address: ' . $e->getMessage()], 500);
        }

    }


    public function fetchCustomerAddresses(Request $request)
{
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not authenticated'], 401);
        }

        // Manually build the query
        DB::enableQueryLog();
        $addresses = Address::query()->whereRaw('customer_id = ?', [$user->id])->get();

        \Log::info('SQL Query:', DB::getQueryLog());

        return response()->json(['status' => 'success', 'data' => $addresses], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}




    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        try {
            $data = Address::findOrFail($address->id);
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAddressRequest $request, Address $address)
    {
        try {
            $address->update($request->validated());
            return response()->json(['status' => 'success', 'data' => $address], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $data = Address::findOrFail($id);
            $data->delete();
            return response()->json(['status' => 'success', 'message' => 'Address deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    //get address by user id
    public function getAddressByUserId($id)
    {
        try {
            $data = Address::where('user_id', $id)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
