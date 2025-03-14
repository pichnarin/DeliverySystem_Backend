<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\OrderDetail;
use App\Models\Food;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
        $validStatuses = ['pending', 'accepted', 'preparing', 'ready', 'picked_up', 'delivering', 'completed', 'declined', 'canceled'];

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
                'status' => 'required|in:pending,accepted,preparing,ready,picked_up,delivering,completed,declined,canceled'
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
