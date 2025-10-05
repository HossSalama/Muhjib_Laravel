<?php
namespace App\Http\Controllers;

use App\Models\MainCategories;
use App\Http\Requests\StoreMainCategoriesRequest;
use App\Http\Requests\UpdateMainCategoriesRequest;
use App\Http\Resources\MainCategoryResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MainCategoriesController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $mainCategory = MainCategories::all();
        $data =MainCategoryResource::collection($mainCategory);
        return response()->json(['message' => 'Main Categories Retrieved Successfully', 'data'=>$data],200);
    }
    public function filter(){
    }

public function store(StoreMainCategoriesRequest $request)
{
    $validated = $request->validate([
        'categories' => 'required|array|min:1',
        'categories.*.brand_id' => 'required|exists:brands,id',
        'categories.*.name_en' => 'required|string|max:255',
        'categories.*.name_ar' => 'required|string|max:255',
        'categories.*.image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        'categories.*.color_code' => 'nullable|string|max:7',
    ]);

    $createdCategories = [];

    foreach ($request->categories as $index => $catData) {
        // التعامل مع الصور لو موجودة
        if ($request->hasFile("categories.$index.image_url")) {
            $catData['image_url'] = $request->file("categories.$index.image_url")->store('main_categories', 'public');
        }

        $category = MainCategories::create($catData);
        $createdCategories[] = new MainCategoryResource($category);
    }

    return response()->json([
        'message' => 'Main Categories Created Successfully',
        'data' => $createdCategories
    ], 201);
}


    public function show($id)
    {
        $mainCategory = MainCategories::find($id);
        if(!$mainCategory){
            return response()->json([
                'message' => 'Main Category not found.',
            ], 404);
        }
        $data =new MainCategoryResource($mainCategory);
        return response()->json(['message' => 'Main Category Retrieved Successfully', 'data'=>$data],200);
    }

    public function update(UpdateMainCategoriesRequest $request, MainCategories $mainCategory)
{
    $validated = $request->validate([
        'brand_id' => 'nullable|exists:brands,id',
        'name_en' => 'nullable|string|max:255',
        'name_ar' => 'nullable|string|max:255',
        'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        'color_code' => 'nullable|string|max:7',
    ]);

    if ($request->hasFile('image_url')) {
        $validated['image_url'] = $request->file('image_url')->store('main_categories', 'public');
    }

    $mainCategory->update($validated);

    return response()->json([
        'message' => 'Main Category Updated Successfully',
        'data' => new MainCategoryResource($mainCategory)
    ], 200);
}

    public function destroy(MainCategories $mainCategory)
    {
        if ($mainCategory->image_url) {
            Storage::disk('public')->delete($mainCategory->image_url);
        }

        $mainCategory->delete();
        return response()->json(['message' => 'Deleted successfully'],200);
    }
}
