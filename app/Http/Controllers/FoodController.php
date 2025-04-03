<?php
namespace App\Http\Controllers;

use Log;
use App\Models\Food;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use App\Http\Resources\FoodResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    public function createFood(Request $request)
    {
        // Check if the image file exists
        try{
            // Check if an image was uploaded
            $imagePath = null;
            if ($request->hasFile('img')) {
                $image = $request->file('img');
                $imagePath = $image->store('images', 'public'); // Store in "storage/app/public/images"
            }

            // Store food item in the database
            $food = Food::create([
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'description' => $request->input('description'),
                'category_id' => $request->input('category_id'),
                'image' => $imagePath ? asset("storage/" . $imagePath) : null, // Full image URL
                ]);
            return response()->json(['message' => 'Food item created successfully!', 
            'data' => new FoodResource($food)], 201);
            }

        catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getAllFoods()
    {
        // Retrieve all food items
        $foods = Food::all();

        // Check if there are no food items
        if ($foods->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No food items found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Foods retrieved successfully!',
            'data' => FoodResource::collection($foods)
        ], 200);
    }

    public function updateFood(Request $request, $id){
        try {
            $food = Food::find($id);
            if (!$food) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Food not found!'
                ], 404);
            }

            // Explicitly fetch form-data values
            $food->name = $request->input('name');
            $food->price = $request->input('price');    
            $food->description = $request->input('description');
            $food->category_id = $request->input('category_id');

            // Check if a new image is uploaded
            if ($request->hasFile('img')) {
                // Validate the image
                $request->validate([
                    'img' => 'image|mimes:jpeg,png,jpg,webp|max:2048' // Max 2MB
                ]);

                // Delete the old image if it exists
                if ($food->image) {
                    Storage::delete(str_replace(asset('storage/'), 'public/', $food->image));
                }

                // Store new image in "storage/app/public/images"
                $image = $request->file('img');
                $imagePath = $image->store('images', 'public'); 

                // Store full image URL
                $food->image = asset("storage/" . $imagePath);
            }

            // Save changes
            $food->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Food updated successfully!',
                'data' => new FoodResource($food)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating food!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteFood(Request $request, $id)
    {
        $food = Food::find($id);

        if (!$food) {
            return response()->json([
                'message' => 'food not found!'
            ]);
        }

        try {
            $food->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Food deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete food! Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function searchFood($id)
    {
        $food = Food::find($id);
        if (!$food) {
            return response()->json([
                'message' => 'food not found!'
            ]);
        }

        try {
            return response()->json([
                'message' => 'food successfully found!',
                'data' => new FoodResource($food)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'error occured!',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function index()
    {
        $food = Food::all();

        //Here I command the food->isEmpty because it could cause error for admin frontend 
        //We should not return like that. Instead let front end handle by themself

        // if ($food->isEmpty()) {
        //     return response()->json([
        //         'message' => 'Food not found!'
        //     ], 404);
        // }

        return response()->json([
            'message' => 'Food found!',
            'data' => FoodResource::collection($food)
        ], 200);
    }

    //get food by the category name
    public function fetchFoodsByCategory($category)
    {
        // Case-insensitive search for category name
        $food = Food::whereHas('category', function ($query) use ($category) {
            $query->whereRaw('LOWER(name) = ?', [strtolower($category)]);
        })->get();

        // Check if any food found
        if ($food->isEmpty()) {
            return response()->json([
                'message' => 'Food not found!'
            ], 404);
        }

        // Return food data
        return response()->json([
            'message' => 'Food found!',
            'data' => FoodResource::collection($food)
        ], 200);
    }

}
