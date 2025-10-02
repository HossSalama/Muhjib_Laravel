<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Catalog - {{ $catalog->name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #222;
        }
        .product {
            border: 1px solid #ccc;
            margin-bottom: 15px;
            padding: 10px;
            page-break-inside: avoid;
        }
        .product h2 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #000;
        }
        .product-image {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .product-details {
            font-size: 12px;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 11px;
        }
        th {
            background: #f5f5f5;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Catalog: {{ $catalog->name }}</h1>
    <p>Created at: {{ $catalog->created_at->format('Y-m-d') }}</p>
    <p>Creator: {{ $catalog->creator->name ?? 'N/A' }}</p>
</div>

@foreach($catalog->basket->basketProducts as $basketProduct)
    @php
        $product = $basketProduct->product;
    @endphp

    <div class="product">
        <h2>{{ $product->name_en ?? 'Unnamed Product' }}</h2>

        {{-- Main Image --}}
        @if($product->main_image)
            <img src="{{ image_url($product->main_image) }}" class="product-image" alt="Main Image">
        @endif

        {{-- Extra Images --}}
        @if(!empty($product->images))
            @foreach($product->images as $img)
                <img src="{{ image_url($img) }}" class="product-image" alt="Product Image">
            @endforeach
        @endif

        <div class="product-details">
            <p><strong>Name (AR):</strong> {{ $product->name_ar }}</p>
            <p><strong>Description:</strong> {{ $product->description_ar ?? 'N/A' }}</p>
            <p><strong>HS Code:</strong> {{ $product->hs_code }}</p>
            <p><strong>SKU:</strong> {{ $product->sku }}</p>
            <p><strong>Pack Size:</strong> {{ $product->pack_size }}</p>
            <p><strong>Dimensions:</strong> {{ $product->dimensions }}</p>
            <p><strong>Capacity:</strong> {{ $product->capacity }}</p>
            <p><strong>Specification:</strong> {{ $product->specification }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Price Type</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($product->prices))
                    @foreach($product->prices as $type => $price)
                        <tr>
                            <td>{{ $type }}</td>
                            <td>{{ $price }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2">No Prices Available</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endforeach

</body>
</html>
