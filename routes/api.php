<?php

use App\Http\Controllers\Api\ContactMessageController as ApiContactMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\MainCategoriesController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SubCategoriesController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\NotificationsController;
use App\Models\ContactMessage;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\QuoteActionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPriceController;
use App\Http\Controllers\BasketController;
use App\Http\Controllers\BasketProductsController;
use App\Http\Controllers\PriceUploadLogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\GuestCartController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\LegendController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ProductImportController;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Artisan;

Route::get('/fix-storage-link', function () {
    Artisan::call('storage:link');
    return '✅ storage link created successfully.';
});

Route::get('/analytics', [AnalyticsController::class, 'index']);
Route::get('/activities', [ActivityController::class, 'index']);

    Route::get('brands/', [BrandController::class, 'index']);
    Route::get('main-categories/', [MainCategoriesController::class, 'index']);
    Route::get('sub-categories/', [SubCategoriesController::class, 'index']);
    Route::get('products/', [ProductController::class, 'index']);
    Route::get('products/show/{product}', [ProductController::class, 'show']);
    Route::get('brands/{brand}', [BrandController::class, 'show']);
    Route::get('main-categories/show/{mainCategory}', [MainCategoriesController::class, 'show']);
    Route::get('sub-categories/show/{subCategory}', [SubCategoriesController::class, 'show']);

Route::get('/price-types', [ProductPriceController::class, 'priceTypes']);
Route::get('/lang', function () {
    return response()->json([
        'message' => __('messages.message'),
        'welcome' => __('messages.welcome'),
        'locale' => app()->getLocale()
    ]);
});

Route::post('/guest/cart/add', [GuestCartController::class, 'addToCart']);
Route::get('/guest/cart/{guest_token}', [GuestCartController::class, 'viewCart']);
Route::middleware('auth:api')->post('/cart/sync', [CartController::class, 'syncGuestCart']);


//  Email Verfication Notification
Route::post('/email/verify/send', [AuthController::class, 'sendEmailVerificationNotification']);
//  Email Verfiy
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail']);
//  Forget Password
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// Reset Password
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
// Public Routes
Route::post('register', [RegisteredUserController::class, 'store']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

Route::middleware(['auth:api', 'is_super_admin'])->group(function () {
        Route::delete('products/delete/{product}', [ProductController::class, 'destroy']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/users/create', [UserController::class, 'create']);
});
Route::middleware(['auth:api', 'is_admin'])->group(function (){
    // view all users
    Route::get('/users', [UserController::class, 'index']);
// Products Routes
Route::group(['prefix'=>'products'],function(){
    Route::post('/create', [ProductController::class, 'store']);
    // Route::post('/update/{product}', [ProductController::class, 'update']);
    // Route::post('/update/{product}', [ProductController::class, 'update']);
// Search and Filter Endpoints
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/filter', [ProductController::class, 'filter']);
    // Update Quantity
    Route::patch('/{product}/update-quantity', [ProductController::class, 'updateQuantity']);
// Daata Sheet
    Route::get('/{product}/technical-datasheet', [ProductController::class, 'downloadTechnicalSheet']);
});
Route::post('/products/update-by-post/{product}', [ProductController::class, 'update']);
 Route::post('/import/products', [ProductImportController::class, 'import']);
    Route::get('/import/status/{id}', [ProductImportController::class, 'status'])->name('import.status');
    Route::get('/products/export', [ProductImportController::class, 'export']);


// Certificate Routes
Route::group(['prefix'=>'certificates'],function(){
    Route::get('/', [CertificateController::class, 'index']);
    Route::post('/create', [CertificateController::class, 'store']);
    Route::delete('delete/{certificate}', [CertificateController::class, 'destroy']);
});
// Legend Routes
Route::group(['prefix'=>'legends'],function(){
    Route::get('/', [LegendController::class, 'index']);
    Route::post('/create', [LegendController::class, 'store']);
    Route::delete('delete/{legend}', [LegendController::class, 'destroy']);
});
// Admin Controller
Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
Route::get('/admin/users', [AdminController::class, 'manageUsers']);

 // Client Routes
Route::group(['prefix' => 'clients'], function () {
    Route::get('/', [ClientsController::class, 'index']);
    Route::post('/create', [ClientsController::class, 'store']);
    Route::get('show/{client}', [ClientsController::class, 'show']);
    Route::post('update/{client}', [ClientsController::class, 'update']);
    Route::delete('delete/{client}', [ClientsController::class, 'destroy']);

Route::post('/{id}/approve', [ClientsController::class, 'approve']);
Route::post('/{id}/reject', [ClientsController::class, 'reject']);

// Company Folder
Route::get('/{client}/folders', [ClientsController::class, 'getClientFolders']);
Route::put('/{client}/rename-folder', [ClientsController::class, 'renameClientFolder']);
Route::delete('/{client}/delete-folder', [ClientsController::class, 'deleteClientFolder']);
Route::post('/{client}/create-folder', [ClientsController::class, 'createClientSubfolder']);
Route::post('/{client}/upload-folder', [ClientsController::class, 'uploadFolder']);
Route::post('/{client}/upload-files', [ClientsController::class, 'uploadFiles']);
Route::get('/{client}/files', [ClientsController::class, 'getClientFiles']);
Route::get('/client-folder/{id}', [ClientsController::class, 'viewClientFolder']);
Route::get('/{clientId}/folders/{folderName?}', [ClientsController::class, 'viewClientSubfolder']);

// QR Code
Route::get('/{id}/qr', [ClientsController::class, 'generateQr']);
});
// Basket Routes
Route::group(['prefix'=>'baskets'],function(){
Route::get('/', [BasketController::class, 'index']);
    Route::post('/create', [BasketController::class, 'store']);
    Route::get('show/{basket}', [BasketController::class, 'show']);
    Route::put('update/{basket}', [BasketController::class, 'update']);
    Route::delete('delete/{basket}', [BasketController::class, 'destroy']);
    Route::patch('/{basket}/status', [BasketController::class, 'changeStatus']);
    Route::get('/filter', [BasketController::class, 'filter']);
});
// Product Price Route
Route::group(['prefix'=>'product-prices'],function(){
Route::get('', [ProductPriceController::class, 'index']);
Route::post('/create', [ProductPriceController::class, 'store']);
Route::post('update/{productPrice}', [ProductPriceController::class, 'update']);
Route::delete('delete/{productPrice}', [ProductPriceController::class, 'destroy']);
Route::get('/export', [ProductPriceController::class, 'export']);
Route::post('/import', [ProductPriceController::class, 'import']);
Route::get('/types', [ProductPriceController::class, 'priceTypes']);
});

// Basket Product Routes
Route::group(['prefix'=>'basket-products'],function(){
    Route::post('/create', [BasketProductsController::class, 'store']);
    Route::put('update/{basketProduct}', [BasketProductsController::class, 'update']);
    Route::delete('delete/{basketProduct}', [BasketProductsController::class, 'destroy']);
});
// Quote Request Routes
Route::group(['prefix' => 'quote-requests'], function () {
    Route::get('/', [QuoteRequestController::class, 'index']);
    Route::post('/create', [QuoteRequestController::class, 'store']);
    Route::get('show/{quoteRequest}', [QuoteRequestController::class, 'show']);
    Route::put('update/{quoteRequest}', [QuoteRequestController::class, 'update']);
    Route::post('/approve/{id}', [QuoteRequestController::class, 'approveQuote']);
    Route::post('/reject/{id}', [QuoteRequestController::class, 'rejectQuote']);
});
Route::post('/quotes/{id}/forward', [QuoteRequestController::class, 'forwardQuote']);
// Quote Action Routes
Route::group(['prefix' => 'quote-actions'], function () {
    Route::get('/', [QuoteActionController::class, 'index']);
    Route::post('/create', [QuoteActionController::class, 'store']);
    Route::post('/forward', [QuoteActionController::class, 'forwardToUser']);
    Route::post('/{id}/request-price-change', [QuoteActionController::class, 'requestPriceChange']);
Route::post('/price-change-requests/{id}/approve', [QuoteActionController::class, 'approvePriceChange']);
Route::post('/price-change-requests/{id}/reject', [QuoteActionController::class, 'rejectPriceChange']);
});
// Brand routes
Route::group(['prefix' => 'brands'], function () {
    Route::post('/create', [BrandController::class, 'store']);
    Route::put('update/{brand}', [BrandController::class, 'update']);
    Route::delete('delete/{brand}', [BrandController::class, 'destroy']);
    Route::post('/{brand}/toggleStatus', [BrandController::class, 'toggleStatus']);
    // Route::post('/{brand}/togglestatus', [BrandController::class, 'togglestatus']);

});

// Main Categories Routes
Route::group(['prefix' => 'main-categories'], function () {
    Route::post('/create', [MainCategoriesController::class, 'store']);
    Route::post('update/{mainCategory}', [MainCategoriesController::class, 'update']);
    Route::delete('delete/{mainCategory}', [MainCategoriesController::class, 'destroy']);
});
// Sub Categories Routes
Route::group(['prefix' => 'sub-categories'], function () {
    Route::post('/create', [SubCategoriesController::class, 'store']);
    Route::post('update/{subCategory}', [SubCategoriesController::class, 'update']);
    Route::delete('delete/{subCategory}', [SubCategoriesController::class, 'destroy']);
    Route::post('/{subCategory}/upload-images', [SubCategoriesController::class, 'updateSubCategoryImages']);
});

// Notification Routes
Route::group(['prefix' => 'notifications'], function () {
    Route::get('/', [NotificationsController::class, 'index']);
    Route::post('/create', [NotificationsController::class, 'store']);
    Route::post('/mark-all-as-read', [NotificationsController::class, 'markAllAsRead']);
    Route::post('/{notification}/approve', [NotificationsController::class, 'approve']);
    Route::post('/{notification}/reject', [NotificationsController::class, 'reject']);

});
// Price Logs Routes
Route::group(['prefix'=>'price-upload-logs'],function(){
    Route::get('/', [PriceUploadLogController::class, 'index']);
    Route::post('/create', [PriceUploadLogController::class, 'store']);
    Route::get('show/{priceUploadLog}', [PriceUploadLogController::class, 'show']);
});
// Templates Routes
Route::group(['prefix' => 'templates'], function () {
Route::get('/', [TemplateController::class, 'index']);
 Route::post('/create', [TemplateController::class, 'store']);
Route::post('/{template}/client', [TemplateController::class, 'addClient']);
Route::post('/{template}/products', [TemplateController::class, 'addProductToTemplate']);
Route::get('/{template}/pdf', [TemplateController::class, 'generatePDF']);
Route::post('/{template}/cover-images', [TemplateController::class, 'uploadCoverImages']);
    // Route::get('show/{id}', [TempletesController::class, 'show']);
    Route::delete('/delete/{template}', [TemplateController::class, 'destroy']);
    Route::put('/update/{template}', [TemplateController::class, 'update']);
    Route::post('/{template}/toggle-status', [TemplateController::class, 'toggleStatus']);
});

// Catalog Routes
Route::group(['prefix'=>'catalogs'],function(){
Route::get('/', [CatalogController::class, 'index']);
Route::post('/create', [CatalogController::class, 'store']);
Route::get('/show/{catalog}', [CatalogController::class, 'show']);
Route::post('/generate',[CatalogController::class,'generateCatalog']);

});
Route::post('/baskets/{basket}/convert-to-catalog', [CatalogController::class, 'convertToCatalog']);
Route::post('/catalogs/{catalog}/revert', [CatalogController::class, 'revertToBasket']);


});

Route::middleware(['auth:api', 'is_user'])->group(function () {
// User Profile & Dashboard
    Route::get('user',[AuthController::class,'user']);
Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile/{user}', [UserController::class, 'updateProfile']);
    Route::get('/dashboard', [UserController::class, 'dashboard']);
    Route::get('clients/my-clients', [UserController::class, 'showmyclient']);
Route::get('/user/{user}/baskets', [BasketController::class, 'getUserBaskets']);
Route::get('/user/catalogs/{catalog}', [CatalogController::class, 'show']);
Route::get('user/quote-requests', [QuoteRequestController::class, 'userQuoteRequests']);
    // Route::get('products/', [ProductController::class, 'index']);
    // Route::get('products/show/{product}', [ProductController::class, 'show']);
    // Route::get('brands/', [BrandController::class, 'index']);
    // Route::get('main-categories/', [MainCategoriesController::class, 'index']);
    // Route::get('sub-categories/', [SubCategoriesController::class, 'index']);

});


