<?php

namespace App\Http\Controllers;

use App\Models\SubCategories;
use App\Http\Requests\StoreSubCategoriesRequest;
use App\Http\Requests\UpdateSubCategoriesRequest;
use App\Http\Resources\SubCategoryResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class SubCategoriesController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $subcategory = SubCategories::all();
        $data =SubCategoryResource::collection($subcategory);
        return response()->json(['message' => 'Sub Categories Retrieved Successfully', 'data'=>$data],200);
    }

public function store(Request $request)
{
    $request->validate([
        'subcategories' => 'required|array|min:1',
        'subcategories.*.main_category_id' => 'nullable|exists:main_categories,id',
        'subcategories.*.name_en' => 'required|string|max:255',
        'subcategories.*.name_ar' => 'nullable|string|max:255',
        'subcategories.*.cover_image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048',
        'subcategories.*.background_image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:4096',
    ]);

    $created = [];

    foreach ($request->subcategories as $index => $item) {
        $data = $item;

        // صور: cover_image و background_image
        if ($request->hasFile("subcategories.$index.cover_image")) {
            $data['cover_image'] = $request->file("subcategories.$index.cover_image")->store('subcategories/covers', 'public');
        }

        if ($request->hasFile("subcategories.$index.background_image")) {
            $data['background_image'] = $request->file("subcategories.$index.background_image")->store('subcategories/backgrounds', 'public');
        }

        $subcategory = SubCategories::create($data);
        $created[] = new SubCategoryResource($subcategory);
    }

    return response()->json([
        'message' => 'Sub Categories Created Successfully',
        'data' => $created
    ], 201);
}


    public function show($id)
    {
        $subCategory = SubCategories::find($id);
        if(!$subCategory){
            return response()->json([
                'message' => 'Sub Category not found.',
            ], 404);
        }
        $data =new SubCategoryResource($subCategory);
        return response()->json(['message' => 'Sub Category Retrieved Successfully', 'data'=>$data],200);
    }

public function update(UpdateSubCategoriesRequest $request, SubCategories $subCategory)
{
    $subCategory->update($request->except(['cover_image', 'background_image']));

    if ($request->hasFile('cover_image')) {
        if ($subCategory->cover_image && Storage::disk('public')->exists($subCategory->cover_image)) {
            Storage::disk('public')->delete($subCategory->cover_image);
        }

        $subCategory->cover_image = $request->file('cover_image')->store('subcategories/covers', 'public');
    }

    if ($request->hasFile('background_image')) {
        if ($subCategory->background_image && Storage::disk('public')->exists($subCategory->background_image)) {
            Storage::disk('public')->delete($subCategory->background_image);
        }

        $subCategory->background_image = $request->file('background_image')->store('subcategories/backgrounds', 'public');
    }

    $subCategory->save();

    $data = new SubCategoryResource($subCategory);

    return response()->json([
        'message' => 'Sub Category Updated Successfully',
        'data' => $data
    ], 200);
}

    public function destroy(SubCategories $subCategory)
    {
        $subCategory->delete();
        return response()->json(['message' => 'Deleted successfully'],200);
    }


    public function updateSubCategoryImages(Request $request, SubCategories $subCategory)
{
    $request->validate([
        'cover_image' => 'nullable|image',
        'background_image' => 'nullable|image',
    ]);

    if ($request->hasFile('cover_image')) {
        $subCategory->cover_image = $request->file('cover_image')->store('subcategories/covers', 'public');
    }

    if ($request->hasFile('background_image')) {
        $subCategory->background_image = $request->file('background_image')->store('subcategories/backgrounds', 'public');
    }

    $subCategory->save();

    return response()->json(['message' => 'Images updated']);
}

}

