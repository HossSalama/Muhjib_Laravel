<!DOCTYPE html>
@php use SimpleSoftwareIO\QrCode\Facades\QrCode; @endphp
<html>
<head>
    <title>Template PDF</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .page { page-break-after: always; position: relative; padding: 0; height: 100vh; overflow: hidden; }
        .background-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; }
        .content { position: relative; z-index: 1; padding: 20px; }
        .centered-content { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; text-align: center; }
        .logo-top-right { position: absolute; top: 20px; right: 20px; width: 60px; height: 60px; object-fit: contain; }
        .products { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; padding: 10px; }
        .product { border: 1px solid #ccc; border-radius: 5px; padding: 10px; background: #fff; text-align: center; }
        .product img { max-width: 100%; height: auto; max-height: 180px; object-fit: contain; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px; font-size: 11px; }
        th { background: #f5f5f5; text-align: left; }
    </style>
</head>
<body>

{{-- ✅ الغلافات البداية --}}
@foreach($template->startCoverImages as $cover)
    @if($cover->background_position !== 'products' && $cover->background_position !== 'client')
        <div class="page centered-content">
            <img src="{{ pdf_image_path($cover->path) }}" style="max-width:100%; height:auto;">
        </div>
    @endif
@endforeach

{{-- ✅ المنتجات حسب البراند --}}
@php
    $productsBg = $template->coverImages->where('background_position', 'products')->first();
@endphp

@foreach($groupedProducts as $brandId => $products)
    @php
        $brand = $products->first()->product->brand ?? null;
        $chunkedProducts = $products->chunk(4);
    @endphp

    {{-- ✅ غلاف البراند --}}
    @if($brand)
        <div class="page centered-content">
            @if(!empty($brand->background_image_url))
                <img src="{{ pdf_image_path($brand->background_image_url) }}" class="background-image">
            @endif

            @if($brand->logo)
                <img src="{{ pdf_image_path($brand->logo) }}" class="logo-top-right">
            @endif

            <div class="content">
                <h1>{{ $brand->name_en ?? 'Brand Name' }}</h1>
                @if($brand->short_description_en) <p>{{ $brand->short_description_en }}</p> @endif
                @if($brand->full_description_en) <p>{{ $brand->full_description_en }}</p> @endif
            </div>
        </div>
    @endif

    {{-- ✅ صفحات المنتجات --}}
    @foreach($chunkedProducts as $chunk)
        <div class="page">
            @if($productsBg)
                <img src="{{ pdf_image_path($productsBg->path) }}" class="background-image">
            @endif

            <div class="content">
                @if($client && $client->logo)
                    <img src="{{ pdf_image_path($client->logo) }}" class="logo-top-right">
                @endif

                <h2>{{ $brand->name ?? 'Other Products' }}</h2>
                <div class="products">
                    @foreach($chunk as $tp)
                        <div class="product">
                            @if($tp->image)
                                <img src="{{ pdf_image_path($tp->image) }}" alt="{{ $tp->name }}">
                            @else
                                <img src="{{ public_path('images/placeholder.jpg') }}" alt="No Image">
                            @endif

                            <p><strong>{{ $tp->name }}</strong></p>
                            <p>{{ $tp->description }}</p>
                            <p><strong>Price:</strong> {{ $tp->price }} EGP</p>
                            <p><strong>Qty:</strong> {{ $tp->quantity ?? 1 }}</p>
                            <p><strong>Total:</strong> {{ number_format($tp->price * ($tp->quantity ?? 1), 2) }} EGP</p>

                            {{-- QR Code inline --}}
                            <div style="margin-top: 10px;">
                                {!! QrCode::size(100)->generate(url('/products/' . $tp->product->id)) !!}
                                <p style="font-size: 10px;">Scan for more</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
@endforeach

{{-- ✅ صفحة بيانات العميل --}}
@php $clientBg = $template->coverImages->where('background_position', 'client')->first(); @endphp
@if($includeClientInfo)
    <div class="page">
        @if($clientBg)
            <img src="{{ pdf_image_path($clientBg->path) }}" class="background-image">
        @endif
        <div class="content centered-content">
            @if($client && $client->logo)
                <img src="{{ pdf_image_path($client->logo) }}" class="logo-top-right">
            @endif

            <h2>Client Information</h2>
            <p>{{ $client->name ?? 'N/A' }}</p>
            <p>{{ $client->email ?? 'N/A' }}</p>
            <p>{{ $client->phone ?? 'N/A' }}</p>

            <h2>Created By</h2>
            <p>{{ $user->name }}</p>
            <p>{{ $user->email }}</p>
            <p>{{ $template->created_at->format('d M Y') }}</p>

            <hr>

            <h2>Product Summary</h2>
            <table>
                <thead>
                    <tr><th>Brand</th><th>Product Count</th><th>Sub Total (EGP)</th></tr>
                </thead>
                <tbody>
                    @php $totalPrice = 0; @endphp
                    @foreach($groupedProducts as $brandId => $products)
                        @php
                            $brand = $products->first()->product->brand ?? null;
                            $brandName = $brand ? $brand->name : 'Other';
                            $count = $products->count();
                            $subTotal = $products->sum(fn($item) => $item->price * ($item->quantity ?? 1));
                            $totalPrice += $subTotal;
                        @endphp
                        <tr>
                            <td><strong>{{ $brandName }}</strong></td>
                            <td>{{ $count }}</td>
                            <td>{{ number_format($subTotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <ul>
                                    @foreach($products as $p)
                                        <li>{{ $p->name }} - {{ $p->quantity ?? 1 }} × {{ number_format($p->price, 2) }} = {{ number_format($p->price * ($p->quantity ?? 1), 2) }} EGP</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="2" align="right"><strong>Total:</strong></td><td><strong>{{ number_format($totalPrice, 2) }} EGP</strong></td></tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif

{{-- ✅ الغلافات النهاية --}}
@foreach($template->endCoverImages as $cover)
    <div class="page centered-content">
        <img src="{{ pdf_image_path($cover->path) }}" style="max-width:100%; height:auto;">
    </div>
@endforeach

</body>
</html>
