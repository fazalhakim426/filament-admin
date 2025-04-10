<div class="space-y-4">
    @foreach($product->productVariants->take(3) as $variant)
    <div class="flex space-x-4">
        <div class="flex-shrink-0 w-32">
            <!-- Show images (limit to 2) -->
            @foreach($variant->images->take(2) as $image)
            <img src="{{ Storage::url($image->url) }}" alt="Variant Image" class="w-full h-32 object-cover">
            @endforeach
        </div>
        <div>
            <p class="text-sm font-medium">SKU: {{ $variant->sku }}</p>
            <p class="text-sm">Description: {{ $variant->description }}</p>

            <!-- Show Variant Options (limit to 3) -->
            <ul class="mt-2">
                @foreach($variant->variantOptions->take(3) as $option)
                <li class="text-sm">{{ $option->attribute_name }}: {{ $option->attribute_value }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endforeach
</div>