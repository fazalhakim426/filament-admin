<div class="p-6 space-y-4">
    <h3 class="text-2xl font-semibold text-gray-800">Order Details</h3>

    <!-- Order Info -->
    <div class="space-y-2">
        <p class="text-sm"><strong class="text-gray-800">Order Number:</strong> <span class="font-medium">{{ $order->warehouse_number }}</span></p>
        <p class="text-sm"><strong class="text-gray-800">Customer Name:</strong> <span class="font-medium">{{ $order->customerUser->name }}</span></p>
        <p class="text-sm"><strong class="text-success-800">Status:</strong> <span class="font-medium">{{ $order->status }}</span></p>
        <p class="text-sm"><strong class="text-gray-800">Total Price:</strong> <span class="font-medium">{{ $order->total_price }}</span></p>
    </div>


    <!-- Recipient Details -->
    <div class="space-y-2">
        <h4 class="font-semibold text-lg text-gray-800">Recipient Details  </h4>
        
        <h6 class="font-semibold text-lg text-gray-780"> {{$recipient->name}}</h6>
        <h6 class="font-semibold text-lg text-gray-780">{{$recipient->email}}</h6>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse" style="width: 100%;">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Name</th> 
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Phone</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Street</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Zip</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">City</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">State </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white">
                        <td class="p-2 text-gray-500">{{ $recipient->name }}</td> 
                        <td class="p-2 text-gray-500">{{ $recipient->phone }}</td>
                        <td class="p-2 text-gray-500">{{ $recipient->street }}</td>
                        <td class="p-2 text-gray-500">{{ $recipient->zip }}</td>
                        <td class="p-2 text-gray-500">{{ $recipient->city->name }}</td>
                        <td class="p-2 text-gray-500">{{ $recipient->state->name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sender Details -->
    <div class="space-y-2">
        <h4 class="font-semibold text-lg text-gray-800">Sender Details  </h4>
        <h6 class="font-semibold text-lg text-gray-780"> {{$sender->name}}</h6>
        <h6 class="font-semibold text-lg text-gray-780">{{$sender->email}}</h6>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse" style="width: 100%;">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Name</th> 
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Phone</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Street</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Zip</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">City</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">State</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white">
                        <td class="p-2 text-gray-500">{{ $sender->name }}</td> 
                        <td class="p-2 text-gray-500">{{ $sender->phone }}</td>
                        <td class="p-2 text-gray-500">{{ $sender->street }}</td>
                        <td class="p-2 text-gray-500">{{ $sender->zip }}</td>
                        <td class="p-2 text-gray-500">{{ $sender->city->name }}</td>
                        <td class="p-2 text-gray-500">{{ $sender->state->name  }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Items Table -->
    <div class="space-y-2">
        <h4 class="font-semibold text-lg text-gray-800">Items Details</h4>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse " style="width: 100%;">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">No #</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Product Name</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Supplier Name</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Quantity</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Price</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Total Value</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr class="bg-white">

                        <td class="p-2 text-gray-500">{{ $loop->index+1 }}</td>
                        <td class="p-2 text-gray-500">{{ $item->product->name }}</td>
                        <td class="p-2 text-gray-500">{{ $item->supplierUser->name }}</td>
                        <td class="p-2 text-gray-500">{{ $item->quantity }}</td>
                        <td class="p-2 text-gray-500">{{ $item->price }}</td>
                        <td class="p-2 text-gray-500">{{ $item->price  * $item->quantity }}</td>
                        <td class="p-2 text-gray-500">{{ $item->status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>