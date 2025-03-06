<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
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
    public function placeOrder(StoreOrderRequest $request)
    {
        DB::beginTransaction();

        try{
            $orders = Order::create([
                'order_number' => 'ORD-'.time(),
                'customer_id' => $request->customer_id,
                'address_id' => $request->address_id,
                'status' => 'pending',
                'quantity' => array_sum(array_column($request->cart_items, 'quantity')),
                'total' => array_sum(array_map(function($item) {
                    return $item['quantity'] * $item['price'];
                }, request('cart_items'))),
                'delivery_fee' => 2.00,
                'tax' => 0.00,
                'discount' => 0.00,
                'payment_method' => $request->payment_method,
            ]);

            //add order details
            foreach ($request->cart_items as $item){
                OrderDetail::create([
                    'order_id' => $orders->id,
                    'food_id' => $item['food_id'],
                    'food_name' => $item['food_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'sub_total' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Order created successfully'], 201);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
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
    public function updateOrderStatus(UpdateOrderStatusRequest $request){

        $order = Order::findOrFail($request->order_id);
        $order->status = $request->status;
        $order->save();

        //get customer's fcm token
        $customerToken = $order->user->noti_token;
        $message = $request->status == "accepted" ? "Your order has been accepted" : "Your order has been rejected";

        //send notification to customer
        $this->sendFCMNotification($customerToken, $message);

        return response()->json(['status' => 'success', 'message' => 'Order status updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
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
        try{
            $data = Order::where('user_id', $id)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get order by driver id
    public function getOrderByDriverId($id)
    {
        try{
            $data = Order::where('driver_id', $id)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get order by status
    public function getOrderByStatus($status)
    {
        try{
            $data = Order::where('status', $status)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get order by address id
    public function getOrderByAddressId($id)
    {
        try{
            $data = Order::where('address_id', $id)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get total amount of all orders
    public function getTotalAmount()
    {
        try{
            $data = Order::sum('total');
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    //get order by payment method
    public function getOrderByPaymentMethod($paymentMethod)
    {
        try{
            $data = Order::where('payment_method', $paymentMethod)->get();
            return response()->json(['status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


}
