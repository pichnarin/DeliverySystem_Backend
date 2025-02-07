<?php

namespace App\Http\Controllers;

use App\Models\person;
use App\Http\Requests\StorepersonRequest;
use App\Http\Requests\UpdatepersonRequest;

class PersonController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index()
    // {
    //     //
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(StorepersonRequest $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(person $person)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(person $person)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(UpdatepersonRequest $request, person $person)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(person $person)
    // {
    //     //
    // }

    public function getAllUsers(){
        $users = Person::all();
        return response()->json($users);
    }

    public function getUserById($id){
        $user = Person::find($id);
        return response()->json($user);
    }
}
