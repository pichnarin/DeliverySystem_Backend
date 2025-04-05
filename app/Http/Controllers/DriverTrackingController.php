<?php

namespace App\Http\Controllers;

use App\Models\DriverTracking;
use App\Http\Requests\StoreDriverTrackingRequest;
use App\Http\Requests\UpdateDriverTrackingRequest;
use App\Models\Order;

class DriverTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $data = DriverTracking::all();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        }catch (\Exception $e){
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
    public function store(StoreDriverTrackingRequest $request)
    {
        try{
            $data = DriverTracking::create($request->validated());
            return response()->json(['status' => 'success', 'data' => $data], 201);
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DriverTracking $driverTracking)
    {
        try{
            $data = DriverTracking::findOrFail($driverTracking->id);
            return response()->json(['status' => 'success', 'data' => $data], 200);
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DriverTracking $driverTracking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDriverTrackingRequest $request, DriverTracking $driverTracking)
    {
        try{
            $data = $driverTracking->update($request->validated());
            return response()->json(['status' => 'success', 'data' => $data], 200);
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $data = DriverTracking::findOrFail($id);
            $data->delete();
            return response()->json(['status' => 'success', 'message' => 'Record deleted successfully'], 200);
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    //user get driver tracking of driver that is assigned to him
    public function fetchDriverLocation($id)
    {
        try{
            $data = DriverTracking::where('order_id', $id)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function fetchDriverLocationAndCustomerLocation($id)
{
    try {
        $tracking = DriverTracking::where('order_id', $id)->first();

        if (!$tracking) {
            return response()->json(['status' => 'error', 'message' => 'Tracking data not found'], 404);
        }

        $order = Order::with('address')->find($id);

        if (!$order || !$order->address) {
            return response()->json(['status' => 'error', 'message' => 'Customer address not found'], 404);
        }

        $data = [
            'driver_location' => [
                'latitude' => $tracking->latitude,
                'longitude' => $tracking->longitude,
            ],
            'customer_location' => [
                'latitude' => $order->address->latitude,
                'longitude' => $order->address->longitude,
            ],
        ];

        return response()->json(['status' => 'success', 'data' => $data], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

}
