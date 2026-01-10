<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Business Card Price Calculator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* New Design Styles */
        .option-card {
            transition: all 0.2s ease;
            cursor: pointer;
            border: 2px solid #e5e7eb;
        }
        .option-card.selected {
            border-color: #000;
            background-color: #f9fafb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .option-card input[type="radio"] {
            display: none;
        }
        .option-checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .option-card.selected .option-checkmark {
            border-color: #000;
            background-color: #000;
        }
        .option-card.selected .option-checkmark:after {
            content: "✓";
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Quantity selection styles */
        .quantity-row {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .quantity-row.selected {
            background-color: #f0f9ff !important;
            border-left: 3px solid #3b82f6;
        }
        .quantity-badge {
            background-color: #3b82f6;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 4px;
        }
        .recommended-tag {
            background-color: #000;
            color: white;
            font-size: 10px;
            padding: 1px 6px;
            border-radius: 10px;
            position: absolute;
            top: -8px;
            right: 5px;
        }
        .compact-table {
            font-size: 14px;
        }
        .compact-table th, .compact-table td {
            padding: 8px 12px;
        }
        
        /* Summary table styles */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
        }
        .summary-table tr {
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-table tr:last-child {
            border-bottom: none;
        }
        .summary-table td {
            padding: 10px 0;
            vertical-align: top;
        }
        .summary-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            padding-right: 20px;
            width: 40%;
        }
        .summary-value {
            color: #111827;
            font-size: 14px;
            font-weight: 500;
            text-align: right;
            width: 60%;
        }
        .price-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
        .current-price {
            color: #1d4ed8;
            font-size: 16px;
            font-weight: 700;
        }
        .original-price {
            color: #9ca3af;
            font-size: 14px;
            text-decoration: line-through;
        }
        
        /* Section spacing */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title i {
            color: #6b7280;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-6 max-w-5xl">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold mb-2">Business Cards</h1>
            <p class="text-gray-600">Professional business cards with customization options</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Customization Options -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
                    <h2 class="text-xl font-bold mb-6 flex items-center">
                        <i class="fas fa-palette mr-3 text-gray-500"></i> Customize Your Cards
                    </h2>
                    
                    <div class="space-y-8">
                        <!-- Size Options -->
                        <div>
                            <div class="section-title">
                                <i class="fas fa-expand-alt"></i>
                                <span>Select Size</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3" id="size-options">
                                @php
                                    $sizeOptions = $category->subcategories->where('type', 'size')->first()->options ?? collect();
                                    $defaultSizeId = $defaults['size'] ?? null;
                                @endphp
                                @foreach($sizeOptions as $option)
                                    <label class="option-card p-4 rounded-lg cursor-pointer relative
                                        {{ $defaultSizeId == $option->id ? 'selected' : '' }}"
                                        data-type="size">
                                        <input type="radio" name="size" value="{{ $option->id }}"
                                            {{ $defaultSizeId == $option->id ? 'checked' : '' }}>
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $option->name }}</div>
                                                @if(!empty($option->description))
                                                    <div class="text-sm text-gray-500 mt-1">{{ $option->description }}</div>
                                                @endif
                                                @if($option->price_adjustment > 0)
                                                   
                                                @endif
                                            </div>
                                            <div class="option-checkmark ml-3 flex-shrink-0"></div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Finish Options -->
                        <div>
                            <div class="section-title">
                                <i class="fas fa-sparkles"></i>
                                <span>Select Finish</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="finish-options">
                                @php
                                    $finishOptions = $category->subcategories->where('type', 'finish')->first()->options ?? collect();
                                    $defaultFinishId = $defaults['finish'] ?? null;
                                @endphp
                                @foreach($finishOptions as $option)
                                    <label class="option-card p-4 rounded-lg cursor-pointer relative
                                        {{ $defaultFinishId == $option->id ? 'selected' : '' }}"
                                        data-type="finish">
                                        <input type="radio" name="finish" value="{{ $option->id }}"
                                            {{ $defaultFinishId == $option->id ? 'checked' : '' }}>
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $option->name }}</div>
                                                @if(!empty($option->description))
                                                    <div class="text-sm text-gray-500 mt-1">{{ $option->description }}</div>
                                                @endif
                                                @if($option->price_adjustment > 0)
                                                   
                                                @endif
                                            </div>
                                            <div class="option-checkmark ml-3 flex-shrink-0"></div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Corner Options -->
                        <div>
                            <div class="section-title">
                                <i class="fas fa-cut"></i>
                                <span>Select Corners</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="corner-options">
                                @php
                                    $cornerOptions = $category->subcategories->where('type', 'corners')->first()->options ?? collect();
                                    $defaultCornerId = $defaults['corners'] ?? null;
                                @endphp
                                @foreach($cornerOptions as $option)
                                    <label class="option-card p-4 rounded-lg cursor-pointer relative
                                        {{ $defaultCornerId == $option->id ? 'selected' : '' }}"
                                        data-type="corners">
                                        <input type="radio" name="corners" value="{{ $option->id }}"
                                            {{ $defaultCornerId == $option->id ? 'checked' : '' }}>
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $option->name }}</div>
                                                @if(!empty($option->description))
                                                    <div class="text-sm text-gray-500 mt-1">{{ $option->description }}</div>
                                                @endif
                                                @if($option->price_adjustment > 0)
                                                   
                                                @endif
                                            </div>
                                            <div class="option-checkmark ml-3 flex-shrink-0"></div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quantity Selection -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h2 class="text-xl font-bold mb-6 flex items-center">
                        <i class="fas fa-calculator mr-3 text-gray-500"></i> Choose Your Quantity
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-200">
                                    <th class="text-left py-4 px-4 font-semibold text-gray-700">Quantity</th>
                                    <th class="text-left py-4 px-4 font-semibold text-gray-700">Price per card</th>
                                    <th class="text-left py-4 px-4 font-semibold text-gray-700">Pack price</th>
                                </tr>
                            </thead>
                            <tbody id="pricing-table-body">
                                @php
                                    $firstItem = reset($initialPricing['pricing_table']);
                                    $defaultQuantity = $firstItem['quantity'] ?? 200;
                                @endphp
                                @foreach($initialPricing['pricing_table'] as $item)
                                    <tr class="quantity-row border-b hover:bg-gray-50 cursor-pointer
                                        {{ $item['is_recommended'] ? 'bg-blue-50' : '' }}
                                        {{ $item['quantity'] == $defaultQuantity ? 'selected' : '' }}"
                                        data-quantity="{{ $item['quantity'] }}">
                                        <td class="py-4 px-4">
                                            <div class="flex items-center">
                                                <span class="font-medium text-gray-900">{{ number_format($item['quantity']) }}</span>
                                                @if($item['quantity'] == $defaultQuantity)
                                                    <span class="quantity-badge ml-2">Selected</span>
                                                @endif
                                                @if($item['is_recommended'])
                                                    <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">Recommended</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-medium text-gray-900">${{ number_format($item['price_per_card'], 2) }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-bold text-gray-900 text-lg">${{ number_format($item['pack_price'], 2) }}</div>
                                            @if($item['original_price'])
                                                <div class="original-price mt-1">${{ number_format($item['original_price'], 2) }}</div>
                                                @if($item['savings'] > 0)
                                                    <div class="text-green-600 text-sm font-medium mt-1">
                                                        Save ${{ number_format($item['savings'], 2) }} ({{ $item['savings_percentage'] }}%)
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Quick Info -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-start">
                            <i class="fas fa-gift text-blue-500 mt-1 mr-3"></i>
                            <div>
                                <p class="text-sm text-blue-800 font-medium">Variety at no extra cost</p>
                                <p class="text-xs text-blue-600 mt-1">Print a different design on every card, for FREE.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary (UPDATED FORMAT) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg border p-6 sticky top-6">
                    <h2 class="text-xl font-bold mb-6 pb-3 border-b text-center">
                        Summary
                    </h2>
                    
                    <!-- Summary Table - UPDATED FORMAT -->
                    <table class="summary-table">
                        <tbody>
                            <!-- Size -->
                            <tr>
                                <td class="summary-label">Size</td>
                                <td id="summary-size" class="summary-value">
                                    {{ $initialPricing['selected_options']['size']->name ?? 'Standard' }}
                                </td>
                            </tr>
                            
                            <!-- Paper -->
                            <tr>
                                <td class="summary-label">Paper</td>
                                <td class="summary-value">Original</td>
                            </tr>
                            
                            <!-- Coating -->
                            <tr>
                                <td class="summary-label">Coating</td>
                                <td class="summary-value">Coated on both sides</td>
                            </tr>
                            
                            <!-- Finish -->
                            <tr>
                                <td class="summary-label">Finish</td>
                                <td id="summary-finish" class="summary-value">
                                    {{ $initialPricing['selected_options']['finish']->name ?? 'Matte' }}
                                </td>
                            </tr>
                            
                            <!-- Corners -->
                            <tr>
                                <td class="summary-label">Corners</td>
                                <td id="summary-corners" class="summary-value">
                                    {{ $initialPricing['selected_options']['corners']->name ?? 'Square' }}
                                </td>
                            </tr>
                            
                            <!-- Quantity -->
                            <tr>
                                <td class="summary-label">Quantity</td>
                                <td id="summary-quantity" class="summary-value font-bold">{{ $defaultQuantity }}</td>
                            </tr>
                            
                            <!-- Price -->
                            <tr>
                                <td class="summary-label">Price</td>
                                <td class="summary-value">
                                    <div class="price-container">
                                        @php
                                            $firstItem = reset($initialPricing['pricing_table']);
                                        @endphp
                                        <span id="summary-price" class="current-price">${{ number_format($firstItem['pack_price'], 2) }}</span>
                                        @if($firstItem['original_price'] ?? false)
                                            <span id="summary-original" class="original-price">${{ number_format($firstItem['original_price'], 2) }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Savings (if any) -->
                    @if(($firstItem['savings'] ?? 0) > 0)
                        <div id="summary-savings" class="text-green-600 text-sm text-right mb-4">
                            Save ${{ number_format($firstItem['savings'], 2) }} ({{ $firstItem['savings_percentage'] ?? 0 }}%)
                        </div>
                    @endif

                    <!-- Options Adjustment -->
                    <div class="mb-5 p-3 bg-gray-50 rounded-lg">
                        <div class="text-xs text-gray-500 mb-1">Options Adjustment</div>
                        <div id="adjustment-per-card" class="text-sm font-medium">+$0.00 per card</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                       
                        
                        <button class="w-full bg-white border border-gray-300 text-gray-800 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center justify-center">
                            <i class="fas fa-download mr-2"></i> Save Quote
                        </button>
                    </div>
                    
                    <!-- Guarantees -->
                   
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const categoryId = {{ $category->id }};
            let selectedOptions = {
                size: {{ $defaults['size'] ?? 'null' }},
                finish: {{ $defaults['finish'] ?? 'null' }},
                corners: {{ $defaults['corners'] ?? 'null' }}
            };
            
            let currentPricingTable = @json($initialPricing['pricing_table']);
            let selectedQuantity = {{ $defaultQuantity }};

            console.log('Initial options:', selectedOptions);

            // Initialize the UI
            updateInitialSummary();

            // Option card click handler
            $(document).on('click', '.option-card', function() {
                const $card = $(this);
                const type = $card.data('type');
                const optionId = $card.find('input[type="radio"]').val();
                const optionName = $card.find('.font-medium.text-gray-900').text().trim();
                
                console.log(`Selected ${type}:`, optionId, optionName);
                
                // Update selected options
                selectedOptions[type] = optionId;
                
                // Update UI for this option type
                $(`.option-card[data-type="${type}"]`).removeClass('selected');
                $card.addClass('selected');
                
                // Make sure radio button is checked
                $card.find('input[type="radio"]').prop('checked', true);
                
                // Update summary display immediately
                $(`#summary-${type}`).text(optionName);
                
                // Calculate new prices
                calculatePrices();
            });

            // Quantity row click handler
            $(document).on('click', '.quantity-row', function() {
                selectedQuantity = $(this).data('quantity');
                
                // Update UI
                $('.quantity-row').removeClass('selected').find('.quantity-badge').remove();
                $(this).addClass('selected').find('td:first-child .font-medium').append('<span class="quantity-badge">Selected</span>');
                
                const item = currentPricingTable.find(p => p.quantity == selectedQuantity);
                if (item) {
                    updateSummaryForQuantity(item, selectedQuantity);
                }
            });

            // Update initial summary
            function updateInitialSummary() {
                const defaultItem = currentPricingTable.find(item => item.quantity === selectedQuantity);
                if (defaultItem) {
                    updateSummaryForQuantity(defaultItem, selectedQuantity);
                }
            }

            // Update summary for specific quantity
            function updateSummaryForQuantity(item, quantity) {
                $('#summary-quantity').text(quantity.toLocaleString());
                $('#summary-price').text('$' + item.pack_price.toFixed(2));
                
                if (item.original_price) {
                    $('#summary-original').text('$' + item.original_price.toFixed(2)).show();
                    if (item.savings > 0) {
                        $('#summary-savings').html(`Save $${item.savings.toFixed(2)} (${item.savings_percentage}%)`).show();
                    } else {
                        $('#summary-savings').hide();
                    }
                } else {
                    $('#summary-original').hide();
                    $('#summary-savings').hide();
                }
            }

            // Calculate prices function
            function calculatePrices() {
                console.log('Calculating with options:', selectedOptions);
                
                $.ajax({
                    url: '{{ route("calculate.price") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        category_id: categoryId,
                        size: selectedOptions.size,
                        finish: selectedOptions.finish,
                        corners: selectedOptions.corners
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('Response received:', response);
                            
                            // Update selected options in summary
                            $('#summary-size').text(response.selected_options.size.name);
                            $('#summary-finish').text(response.selected_options.finish.name);
                            $('#summary-corners').text(response.selected_options.corners.name);
                            
                            // Update adjustment display
                            const adjustment = response.adjustment_per_card;
                            if (adjustment > 0) {
                                $('#adjustment-per-card').text('+$' + adjustment.toFixed(2) + ' per card');
                            } else {
                                $('#adjustment-per-card').text('+$0.00 per card');
                            }
                            
                            // Update pricing table
                            currentPricingTable = response.pricing_table;
                            updatePricingTable(response.pricing_table);
                            
                            // Update summary with current quantity
                            const currentItem = response.pricing_table.find(item => item.quantity === selectedQuantity) || 
                                               response.pricing_table[0];
                            
                            if (currentItem) {
                                selectedQuantity = currentItem.quantity;
                                updateSummaryForQuantity(currentItem, selectedQuantity);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Error calculating prices. Please try again.');
                    }
                });
            }

            // Update pricing table function
            function updatePricingTable(pricingTable) {
                const tableBody = $('#pricing-table-body');
                tableBody.empty();
                
                pricingTable.forEach(item => {
                    const isSelected = item.quantity === selectedQuantity;
                    const rowClass = isSelected ? 'selected' : '';
                    const badge = isSelected ? '<span class="quantity-badge">Selected</span>' : '';
                    const recommendedBadge = item.is_recommended ? 
                        '<span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">Recommended</span>' : '';
                    
                    const savingsHtml = item.original_price && item.savings > 0 ? 
                        `<div class="text-green-600 text-sm font-medium mt-1">
                            Save $${item.savings.toFixed(2)} (${item.savings_percentage}%)
                        </div>` : '';
                    
                    const originalPriceHtml = item.original_price ? 
                        `<div class="original-price mt-1">$${item.original_price.toFixed(2)}</div>` : '';
                    
                    const row = `
                        <tr class="quantity-row border-b hover:bg-gray-50 cursor-pointer ${rowClass}"
                            data-quantity="${item.quantity}">
                            <td class="py-4 px-4">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-900">${item.quantity.toLocaleString()}</span>
                                    ${badge}
                                    ${recommendedBadge}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-medium text-gray-900">$${item.price_per_card.toFixed(2)}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-gray-900 text-lg">$${item.pack_price.toFixed(2)}</div>
                                ${originalPriceHtml}
                                ${savingsHtml}
                            </td>
                        </tr>
                    `;
                    
                    tableBody.append(row);
                });
            }

            // Initial calculation
            calculatePrices();
        });
    </script>
</body>
</html>