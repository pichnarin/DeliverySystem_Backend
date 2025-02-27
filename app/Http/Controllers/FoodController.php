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
    public function createFood(Request $request){
        //validation
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'category_id' => 'required|integer',
            'img' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048', 
        ]);

        //handle file upload
        $foodImgObj = $request->file('img');
        $path = './assets/food_images';
        $foodImg = time().'_'.$foodImgObj->getClientOriginalName();
        $foodImgObj->move($path,$foodImg);

        $name = $request->get('name');
        $category = $request->input('category_id');
        $price = $request->input('price');
        $description = $request->input('description');

        $food = Food::create([
            'name' => $name,
            'category_id' => $category,
            'price' => $price,
            'description' => $description,
            'image' => $foodImg,

        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Food created successfully!',
            'data' => new FoodResource($food)
        ], 201); 

    }

    public function getAllFoods(){
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

    public function updateFood(Request $request, $id) { 
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
                if ($request->hasFile('image')) {
                    // Validate the image
                    $request->validate([
                        'image' => 'image|mimes:jpeg,png,jpg,webp|max:2048' // Max 2MB
                    ]);

                    // Delete the old image if it exists
                    if ($food->image) {
                        Storage::delete('public/assets/food_images/'.$food->image);
                    }

                    // Store the new image
                    $foodImgObj = $request->file('image');
                    $path = './assets/food_images';
                    $foodImg = time().'_'.$foodImgObj->getClientOriginalName();
                    $foodImgObj->move($path,$foodImg);
         
                    // // Update the image field
                    $food->image = $foodImg;
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

    public function deleteFood(Request $request,$id){
        $food = Food::find($id);

        if(!$food){
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
    
    public function searchFood($id){
        $food = Food::find($id);
        if(!$food){
            return response()->json([
                'message' => 'food not found!'
            ]);
        }

        try{
            return response()->json([
                'message' => 'food successfully found!',
                'data' => new FoodResource($food)
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'error occured!',
                'error' => $e->getMessage()
            ]);
        }
    }

}
