<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCatalogRequest;
use App\Http\Resources\CatalogResource;
use App\Models\Catalog;
use App\Models\Basket;
use App\Models\Template;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;


class CatalogController extends Controller
{
    use AuthorizesRequests;
     // Get all catalogs
    public function index()
    {
        $catalogs = Catalog::with(['basket'])->latest()->get();
        $data = CatalogResource::collection($catalogs);
        return response()->json([
            'message' => 'Catalogs Retrieved Successfully',
            'data' => $data
        ], 200);
    }

    // Store a new catalog
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'basket_id' => 'required|exists:baskets,id',
            'template_id' => 'required|exists:templates,id',
        ]);

        $basket = Basket::with('products')->findOrFail($validated['basket_id']);
        $template = Template::findOrFail($validated['template_id']);

        // تأكد إن view PDF موجود
        if (!view()->exists('pdf.templates.custom_template')) {
            return response()->json(['error' => 'PDF view not found'], 404);
        }

        // Generate the PDF
        $pdf = Pdf::loadView('pdf.templates.custom_template', [
            'basket' => $basket,
            'template' => $template,
        ]);

        // Save PDF file
        $pdfPath = 'catalogs/' . Str::uuid() . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // Save catalog data
        $catalog = Catalog::create([
            'name' => $validated['name'],
            'basket_id' => $validated['basket_id'],
            'template_id' => $validated['template_id'],
            'created_by' => Auth::id(),
            'pdf_path' => $pdfPath,
        ]);
        $data = new CatalogResource($catalog);
        return response()->json([
            'message' => 'Catalog Created Successfully',
            'data' => $data
        ], 201);
    }

    // Show single catalog
    public function show(Catalog $catalog)
    {
        $catalog->load('basket.basketProducts.product', 'template', 'creator');
        $data = new CatalogResource($catalog);
        return response()->json([
            'message' => 'Catalog Retrieved Successfully',
            'data' => $data
        ], 200);
    }


public function generateCatalog(Request $request)
{
    $request->validate([
        'template_id' => 'required|exists:templates,id',
        'basket_id' => 'required|exists:baskets,id',
        'include_client_info' => 'nullable|boolean',
    ]);

    $template = Template::with('creator')->findOrFail($request->template_id);
    $user = Auth::user();
    $basket = Basket::with('basketProducts.product.brand', 'client')->findOrFail($request->basket_id);
    $client = $basket->client;

    if (!$client) {
        return response()->json(['message' => 'No client associated with this basket.'], 400);
    }

    // ✅ Generate QR Codes locally
    $qrDir = storage_path('app/public/qrcodes');
    if (!file_exists($qrDir)) {
        mkdir($qrDir, 0755, true);
    }

    foreach ($basket->basketProducts as $item) {
        $productUrl = url('/products/' . $item->product->id);
        $qrFileName = 'qrcodes/qr_' . $item->product->id . '.svg';
        $qrFullPath = storage_path('app/public/' . $qrFileName);

        if (!file_exists($qrFullPath)) {
            QrCode::format('svg')->size(200)->generate($productUrl, $qrFullPath);
        }
    }

    // ✅ Build Products
    $templateProducts = $basket->basketProducts->map(function ($item) {
        $product = $item->product;
        $qrFileName = 'qrcodes/qr_' . $product->id . '.svg';
        $qrPublicPath = storage_path('app/public/' . $qrFileName);

        return (object)[
            'name' => $product->name_en,
            'description' => $product->specification,
            'price' => $item->price,
            'image' => $product->main_image,
            'quantity' => $item->quantity,
            'total' => $item->quantity * $item->price,
            'product' => $product,
            'qrCodePath' => $qrPublicPath,
        ];
    });

    $groupedProducts = $templateProducts->groupBy(function ($item) {
        return optional($item->product->brand)->id;
    });

    // ✅ Create Catalog record
    $catalog = Catalog::create([
        'name' => 'Generated Catalog from Basket #' . $basket->id,
        'template_id' => $template->id,
        'created_by' => $user->id,
        'basket_id' => $basket->id,
    ]);

    $includeClientInfo = $request->boolean('include_client_info', true);

    // ✅ Generate PDF
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('templates.pdf', [
        'template' => $template,
        'user' => $user,
        'client' => $client,
        'groupedProducts' => $groupedProducts,
        'includeClientInfo' => $includeClientInfo,
    ])->setPaper('A4', 'portrait');

    $filename = 'catalog_' . time() . '.pdf';

    // ✅ Save Local Only
    $filePath = 'catalogs/' . $filename;
    Storage::disk('public')->put($filePath, $pdf->output());

    $catalog->update(['pdf_path' => $filePath]);

    return response()->json([
        'message' => 'Catalog PDF Generated Successfully (Local)',
        'file_url' => Storage::url($filePath),
    ]);
}



public function convertToCatalog(Request $request, Basket $basket)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    if ($basket->status === 'converted') {
        return response()->json([
            'message' => 'This basket has already been converted to a catalog.',
        ], 400);
    }

    // لو عايز تستخدم تمبليت افتراضي
    $template = Template::first(); // أو where('is_default', true) لو عندك حقل زي كده

    if (!$template) {
        return response()->json(['message' => 'No template found.'], 400);
    }

    $user = Auth::user();

    $basketProducts = $basket->basketProducts()->with('product.subCategory')->get();

    $templateProducts = $basketProducts->map(function ($item) {
        $product = $item->product;
        return (object)[
            'name' => $product->name_en,
            'description' => $product->specification,
            'price' => $item->price,
            'image' => $product->main_image,
            'quantity' => $item->quantity,
            'total' => $item->quantity * $item->price,
            'product' => $product,
        ];
    });

    $groupedProducts = $templateProducts->groupBy(function ($item) {
        return optional($item->product->subCategory)->id;
    });

    $catalog = Catalog::create([
        'name' => $request->name,
        'basket_id' => $basket->id,
        'template_id' => $template->id,
        'created_by' => $user->id,
    ]);

    $pdf = Pdf::loadView('templates.pdf', [
        'template' => $template,
        'user' => $user,
        'client' => $basket->client,
        'groupedProducts' => $groupedProducts,
        'includeClientInfo' => true
    ])->setPaper('A4', 'portrait');

    $fileName = 'catalog_' . $catalog->id . '_' . time() . '.pdf';
    $filePath = 'catalogs/' . $fileName;
    Storage::disk('public')->put($filePath, $pdf->output());

    $catalog->update([
        'pdf_path' => $filePath,
    ]);

    $basket->status = 'done';
    $basket->save();

    $catalog->load('basket.basketProducts.product', 'template', 'creator');

    return response()->json([
        'message' => 'Basket converted to catalog successfully.',
        'data' => new CatalogResource($catalog),
        'pdf_url' => Storage::url($filePath),
    ], 201);
}



public function revertToBasket(Request $request, Catalog $catalog)
{
    if ($catalog->created_by !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $basket = $catalog->basket;
    if (!$basket) {
        return response()->json(['message' => 'No basket found for this catalog.'], 404);
    }

    $basket->status = 'in_progress';
    $basket->save();

    if (Storage::disk('public')->exists($catalog->pdf_path)) {
        Storage::disk('public')->delete($catalog->pdf_path);
    }

    $catalog->delete();

    return response()->json([
        'message' => 'Basket reverted. You can now edit the basket again.',
        'data' => $basket->load('basketProducts.product'),
    ]);
}

}
