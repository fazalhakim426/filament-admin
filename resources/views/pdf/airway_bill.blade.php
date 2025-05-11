<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shipping Label</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }


        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        .barcode {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div>

        <table>
            <thead>
                <tr>
                    <td style="border-right: none;padding:25px 10px;">
                        <h2>PostEx</h2>
                    </td>
                    <td style="border-left: none;text-align:right;padding:25px 10px;">
                        <img src="{{ $post_ex_barcode }}" alt="Barcode" height="50">


                    </td>
                </tr>
                <tr>
                    <td>
                        <h3>Courier Information</h3>
                    </td>
                    <td>
                        <h3>Consignee Information</h3>
                    </td>
                </tr>
                <tr>
                    <th width="50%">
                        <p><strong>Tracking ID:</strong> {{ $warehouse_number }}</p>
                        <p><strong>Weight:</strong> 1000 grams</p>
                        <p><strong>Payment Method:</strong> COD</p>
                        <p><strong>Destination:</strong>{{ $destination['state'] }}, {{ $destination['city'] }},{{ $destination['country'] }} </p>

                        <div style="text-align:center; margin-top: 10px;">
                            <div style="display: inline-block;">
                                {!! DNS1D::getBarcodeHTML($total_price, 'C128', 2, 50) !!}
                            </div>
                            <p>Rs. {{ $total_price }}</p>
                        </div>


                    </th width="50%">
                    <th>

                        <p><strong>Receiver:</strong> {{ $destination['name'] }}</p>
                        <p><strong>Phone #:</strong> {{ $destination['phone'] }}</p>
                        <p><strong>Address:</strong>{{ $destination['address'] }}</p>
                        <p><strong>City:</strong> {{ $destination['city'] }}</p>
                        <p><strong>Street:</strong> {{ $destination['street'] }}</p>
                        <p><strong>MashoorJaga:</strong>{{ $destination['near_by']??'Nil' }}</p>

                    </th>
                </tr>
                <tr>
                    <td>
                        <h3>Sender Information</h3>
                    </td>
                    <td>
                        <h3>Return Address</h3>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p><strong>Address:</strong>{{ $sender['address'] }}
                            ,<strong>City:</strong> {{ $sender['city'] }}
                            ,<strong>Street:</strong> {{ $sender['street'] }}</p>
                    </td>
                    <td>
                        <p><strong>ID:</strong> {{ $warehouse_number }}
                            ,<strong>Address:</strong>{{ $sender['address'] }}
                            ,<strong>City:</strong> {{ $sender['city'] }}
                            ,<strong>Street:</strong> {{ $sender['street'] }}</p>
                    </td>
                </tr>
            </thead>
        </table>
        <table>
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Qty</th>
                    <th>Item Name</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                <tr>
                    <td>{{ $item['sku'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $item['product_name'] }}{{ $item['variant_name'] }}</td>
                </tr>
                @endforeach


                <tr>
                    <td style=" border-right: none;" colspan="2"><strong>Total Qty:</strong>{{$items_count}} Items</td>
                    <td style=" border-left: none;text-align:right"><strong>Order ID:</strong>{{ $warehouse_number }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center;">
                        <div style="text-align:center; margin-top: 10px;">
                            <div style="display: inline-block;">
                                {!! DNS1D::getBarcodeHTML($warehouse_number, 'C128', 2, 50) !!}
                            </div>
                            <p>{{ $warehouse_number }}</p>
                        </div>

                    </td>
                </tr>
            </tbody>
        </table>


</body>

</html>