<?php

namespace App\Http\Controllers;

use App\Models\address;
use App\Http\Requests\StoreaddressRequest;
use App\Http\Requests\UpdateaddressRequest;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreaddressRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(address $address)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(address $address)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateaddressRequest $request, address $address)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(address $address)
    {
        //
    }
}
