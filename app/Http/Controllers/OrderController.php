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
use GuzzleHttp\Client;

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


    public function create()
    {
        //
    }


    public function placeOrder(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'address_id' => 'required|exists:addresses,id',
                'food' => 'required|array',
                'food.*.food_id' => 'required|exists:food,id',
                'food.*.quantity' => 'required|integer|min:1',
            ]);

            // Create Order
            $order = Order::create([
                'order_number' => strtoupper(uniqid('ORD')),
                'customer_id' => $request->user()->id,
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

            //emit order notification to socket server
            $this->emitRecieveOrder($order);

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('orderDetails') // Load related order details
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error placing order: ' . $e->getMessage()], 500);
        }
    }

    //get pending orders
    public function fetchPendingOrders()
    {
        try {
            $data = Order::where('status', 'pending')->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get accepted orders
    public function fetchAcceptedOrders()
    {
        try {
            $data = Order::where('status', 'accepted')->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get delivering orders
    public function fetchDeliveringOrders()
    {
        try {
            $data = Order::where('status', 'delivering')->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get completed orders
    public function fetchCompletedOrders()
    {
        try {
            $data = Order::where('status', 'completed')->get();
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
                'status' => 'required|in:pending,accepted,declined'
            ]);

            $order = Order::findOrFail($orderId);

            // Update status
            $order->status = $request->status;
            $order->save();

            DB::commit();

            //emit order status to socket server
            $this->emitOrderStatus($order);

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
        DB::beginTransaction();
    
        try {
            // Validate driver_id
            $request->validate([
                'driver_id' => 'required|integer'
            ]);
    
            // Find the order
            $order = Order::findOrFail($id);
    
            // Order status check
            if ($order->status != 'accepted') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order status must be accepted before assigning a driver.'
                ], 400);
            }
    
            // Check if driver exists and has the correct role_id
            $driver = User::where('id', $request->driver_id)
                ->where('role_id', 3) // Assuming 3 is the driver role_id
                ->first();
    
            if (!$driver) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Driver not found or not valid.'
                ], 404);
            }
    
            // Assign the driver
            $order->driver_id = $driver->id;
            $order->status = 'assigning'; // Update order status to 'assigning'
            $order->save();
    
            // Commit the transaction
            DB::commit();

            //emit the order status to socket server
            $this->emitOrderStatus($order);
    
            return response()->json([
                'status' => 'success',
                'message' => 'Driver assigned successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
    
            // Log the error
            \Log::error('Error assigning driver: ' . $e->getMessage());
    
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to assign driver: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function fetchDriveingOrderDetails(Request $request)
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


    public function fetchCustomerOrders(Request $request)
    {
        try {
            $data = Order::where('customer_id', $request->user()->id)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function fetchCurrentCustomerOrder(Request $request)
    {
        try {
            // Eager load orderDetails for ALL orders of user
            $data = Order::with('orderDetails')
                ->where('customer_id', $request->user()->id)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function fetchOrderDetails(Request $request)
    {
        try {
            $data = Order::with('orderDetails', 'customer', 'address', 'driver') // Eager load relations including driver if needed
                ->get();

            return response()->json(['status' => 'success', 'data' => $data], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

    }


    // Driver fetch orders assigned by admin
    public function fetchAssignedOrders(Request $request)
    {
        try {
            $driverId = $request->user()->id; // Assuming the driver is authenticated!

            $data = Order::where('status', 'assigning') // You might be using 'assigned' or 'on_the_way' status
                ->where('driver_id', $driverId)
                ->with('orderDetails', 'customer', 'address') // Eager load order details if needed
                ->get();

            return response()->json(['status' => 'success', 'data' => $data], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    //delivery orders
    public function DeliveringOrder(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $validated = $request->validate([
                'status' => 'required|in:delivering'
            ]);


            $order = Order::findOrFail($id);

            // Update status
            $order->status = $request->status;
            $order->save();

            DB::commit();

            //emit order status to socket server
            $this->emitOrderStatus($order);

            return response()->json(['status' => 'success', 'order' => $order, 'message' => 'Order status updated to delivering successfully'], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //update order status to completed
    public function CompletedOrder(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $validated = $request->validate([
                'status' => 'required|in:completed'
            ]);

            $order = Order::findOrFail($id);

            // Update status
            $order->status = $request->status;
            $order->save();

            DB::commit();

            //emit the order status to socket server
            $this->emitOrderStatus($order);

            return response()->json(['status' => 'success', 'order' => $order, 'message' => 'Order status updated to completed successfully'], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }



    /**
     * Send a notification to the Socket.IO server.
     */

    //alert admin when customer done plcaing order
    private function emitRecieveOrder($order)
    {
        $client = new Client();
        try {
            $client->post('http://localhost:3000/order-notification', [
                'json' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => $order->total,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending notification to Socket.IO server: ' . $e->getMessage());
        }
    }


    //alert customer when admin accepted or declined order
    private function emitOrderStatus($order)
    {
        $client = new Client();
        try {
            $client->put('http://localhost:3000/order-status', [
                'json' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending notification to Socket.IO server: ' . $e->getMessage());
        }
    }
}
