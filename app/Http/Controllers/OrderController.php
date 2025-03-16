<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\OrderDetail;
use App\Models\Food;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Order::all();
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


    // public function storeOrder(StoreOrderRequest $request)
    // {
    //     // Start transaction
    //     DB::beginTransaction();

    //     try {
    //         // Create order
    //         $order = Order::create([
    //             'customer_id' => $request->customer_id,
    //             'address_id' => $request->address_id,
    //             'order_number' => uniqid('ORDER-'),
    //             'status' => 'pending', // Set initial status to 'pending'
    //             'total' => 0, // Will be calculated later
    //             'delivery_fee' => 5.00, // You can adjust this
    //             'tax' => 0, // Add tax logic if needed
    //             'discount' => 0, // Add discount logic if needed
    //         ]);

    //         $total = 0;

    //         // Loop through cart items and store them in order_details table
    //         foreach ($request->cart_items as $item) {
    //             $food = Food::findOrFail($item['food_id']);
    //             $subTotal = $food->price * $item['quantity'];

    //             // Create order detail
    //             OrderDetail::create([
    //                 'order_id' => $order->id,
    //                 'food_id' => $item['food_id'],
    //                 'name' => $item['name'],
    //                 'quantity' => $item['quantity'],
    //                 'price' => $food->price,
    //                 'sub_total' => $subTotal,
    //             ]);

    //             // Update total for the order
    //             $total += $subTotal;
    //         }

    //         // Update the total amount for the order
    //         $order->update([
    //             'total' => $total,
    //         ]);

    //         // Commit the transaction
    //         DB::commit();

    //         return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);

    //     } catch (\Exception $e) {
    //         // Rollback the transaction if there's an error
    //         DB::rollBack();
    //         return response()->json(['message' => 'Error creating order: ' . $e->getMessage()], 500);
    //     }
    // }

    public function placeOrder(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:users,id',
                'address_id' => 'required|exists:addresses,id',
                'food' => 'required|array',
                'food.*.food_id' => 'required|exists:food,id',
                'food.*.quantity' => 'required|integer|min:1',
            ]);

            // Create Order
            $order = Order::create([
                'order_number' => strtoupper(uniqid('ORD')),
                'customer_id' => $validated['customer_id'],
                'address_id' => $validated['address_id'],
                'status' => 'pending',
                'quantity' => array_sum(array_column($validated['food'], 'quantity')),
            ]);

            $total = 0;

            foreach ($validated['food'] as $item) {
                $food = Food::where('id', $item['food_id'])->first();
                if (!$food) {
                    throw new \Exception("Food item not found.");
                }

                $subTotal = $food->price * $item['quantity'];

                OrderDetail::create([
                    'order_id' => $order->id,
                    'food_id' => $item['food_id'],
                    'name' => $food->name,
                    'quantity' => $item['quantity'],
                    'price' => $food->price,
                    'sub_total' => $subTotal,
                ]);

                $total += $subTotal;
            }

            $order->update([
                'total' => floatval($total),
                'delivery_fee' => floatval(5),
                'tax' => floatval($total * 0.1),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('orderDetails') // Load related order details
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error placing order: ' . $e->getMessage()], 500);
        }
    }

    //get order base on input status
    public function getOrderByStatus($status)
    {
        $validStatuses = ['pending', 'accepted', 'delivering', 'completed', 'declined'];

        if (!in_array($status, $validStatuses)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid status'], 400);
        }

        try {
            $data = Order::where('status', $status)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    //admin aprrove or reject order
    public function updateOrderStatus(Request $request, $orderId)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,accepted,declined,delivering,completed'
            ]);

            $order = Order::findOrFail($orderId);

            // Update status
            $order->status = $request->status;
            $order->save();

            DB::commit();

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Order status updated successfully',
                'order' => $order
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            // Return error response
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function assignDriver(Request $request, $id)
    {
        // Validate driver_id
        $request->validate([
            'driver_id' => 'required|integer'
        ]);

        // Find order
        $order = Order::findOrFail($id);

        // Order status check
        if ($order->status != 'accepted') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order status must be accepted before assigning a driver.'
            ], 400);
        }

        // Check if driver exists & has role_id = driver role
        $driver = User::where('id', $request->driver_id)
            ->where('role_id', 3) // Replace 3 with actual driver role_id
            ->first();

        if (!$driver) {
            return response()->json([
                'status' => 'error',
                'message' => 'Driver not found or not valid.'
            ], 404);
        }

        // Assign driver
        $order->driver_id = $driver->id;
        $order->status = 'delivering';
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Driver assigned successfully.'
        ], 200);
    }

    //driver complete order
    public function completeOrder(Request $request, $id)
    {
        // Find order
        $order = Order::findOrFail($id);

        // Order status check
        if ($order->status != 'delivering') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order status must be delivering before completing.'
            ], 400);
        }

        // Complete order
        $order->status = 'completed';
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order completed successfully.'
        ], 200);
    }


    //get delivery details
    // public function getDriverOrders()
    // {
    //     try {
    //         // Fetch all orders with the status 'delivering'
    //         $orders = Order::with(['customer', 'address', 'orderDetails']) // Eager load customer, address, and orderDetails relations
    //             ->where('status', 'delivering') // Only fetch orders with "delivering" status
    //             ->get();

    //         if ($orders->isEmpty()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'No orders found with delivering status.'
    //             ], 404);
    //         }

    //         // Return the order details
    //         return response()->json([
    //             'status' => 'success',
    //             'data' => $orders->map(function ($order) {
    //                 return [
    //                     'order_id' => $order->id,
    //                     'order_number' => $order->order_number,
    //                     'status' => $order->status,
    //                     'customer' => [
    //                         'id' => $order->customer->id,
    //                         'name' => $order->customer->name,
    //                         'email' => $order->customer->email,
    //                         'phone' => $order->customer->phone,
    //                         'avatar' => $order->customer->avatar,
    //                     ],
    //                     'address' => [
    //                         'id' => $order->address->id,
    //                         'street' => $order->address->street,
    //                         'city' => $order->address->city,
    //                         'reference' => $order->address->reference,
    //                         'state' => $order->address->state,
    //                         'zip' => $order->address->zip,
    //                         'latitude' => $order->address->latitude,
    //                         'longitude' => $order->address->longitude,
    //                     ],
    //                     'order_details' => $order->orderDetails->map(function ($detail) {
    //                         return [
    //                             'food_id' => $detail->food_id,
    //                             'quantity' => $detail->quantity,
    //                             'price' => $detail->price,
    //                             'sub_total' => $detail->sub_total,

    //                         ];
    //                     })
    //                 ];
    //             })
    //         ], 200);

    //     } catch (\Exception $e) {
    //         // Log the error message
    //         Log::error('Error fetching orders: ' . $e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred while fetching orders.'
    //         ], 500);
    //     }
    // }


    public function getDriverOrders(Request $request)
    {
        try {
            // Get the authenticated driver's ID from the JWT token
            $driver_id = $request->user()->id;

            // Fetch all orders with the status 'delivering' for this driver
            $orders = Order::with(['customer', 'address', 'orderDetails']) // Eager load related data
                ->where('driver_id', $driver_id) // Only fetch orders assigned to this driver
                ->where('status', 'delivering') // Only fetch orders with "delivering" status
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No orders found for this driver with delivering status.'
                ], 404);
            }

            // Return the order details
            return response()->json([
                'status' => 'success',
                'data' => $orders->map(function ($order) {
                    return [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'customer' => [
                            'id' => $order->customer->id,
                            'name' => $order->customer->name,
                            'email' => $order->customer->email,
                            'phone' => $order->customer->phone,
                            'avatar' => $order->customer->avatar,
                        ],
                        'address' => [
                            'id' => $order->address->id,
                            'street' => $order->address->street,
                            'city' => $order->address->city,
                            'reference' => $order->address->reference,
                            'state' => $order->address->state,
                            'zip' => $order->address->zip,
                            'latitude' => $order->address->latitude,
                            'longitude' => $order->address->longitude,
                        ],
                        'order_details' => $order->orderDetails->map(function ($detail) {
                            return [
                                'food_id' => $detail->food_id,
                                'quantity' => $detail->quantity,
                                'price' => $detail->price,
                                'sub_total' => $detail->sub_total,
                            ];
                        })
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error fetching driver orders: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching orders.'
            ], 500);
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {

    }

    //update order status
    // public function updateOrderStatus(UpdateOrderStatusRequest $request)
    // {

    //     $order = Order::findOrFail($request->order_id);
    //     $order->status = $request->status;
    //     $order->save();

    //     //get customer's fcm token
    //     $customerToken = $order->user->noti_token;
    //     $message = $request->status == "accepted" ? "Your order has been accepted" : "Your order has been rejected";

    //     //send notification to customer
    //     $this->sendFCMNotification($customerToken, $message);

    //     return response()->json(['status' => 'success', 'message' => 'Order status updated successfully'], 200);
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $data = Order::findOrFail($id);
            $data->delete();
            return response()->json(['status' => 'success', 'message' => 'Order deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get order by user id
    public function getOrderByUserId($id)
    {
        try {
            $data = Order::where('user_id', $id)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get order by driver id
    public function getOrderByDriverId($id)
    {
        try {
            $data = Order::where('driver_id', $id)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get order by address id
    public function getOrderByAddressId($id)
    {
        try {
            $data = Order::where('address_id', $id)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get total amount of all orders
    public function getTotalAmount()
    {
        try {
            $data = Order::sum('total');
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get order by payment method
    public function getOrderByPaymentMethod($paymentMethod)
    {
        try {
            $data = Order::where('payment_method', $paymentMethod)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


}
