<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Role::all();
        return response()->json($data);
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
    public function store(StoreRoleRequest $request)
    {

        // Create new record using mass-assignment
        $data = Role::create($request->validated());

        return response()->json($data, 201);
    }




    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Find the role by ID
            $role = Role::findOrFail($id); // Use findOrFail to automatically handle not found

            return response()->json([
                'status' => 'success',
                'data' => $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404); // Send a 404 status if the role is not found or any other error
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
{
    try {
        // No need to find the role since it's already injected
        $role->update($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $role
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 404);
    }
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $data = Role::findOrFail($id);
            $data->delete();
            return response()->json(['status' => 'success', 'message' => 'Role deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

}
