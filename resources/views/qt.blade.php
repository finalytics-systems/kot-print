<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        @page { 
            size: 7.5cm 21cm;
            margin: 0px;
            padding: 0px; 
        }
        body, html { 
            size: 7.5cm 21cm;
            margin: 0px;
            padding: 0px;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }

        h1 {
            font-size: 50px;
            text-align: center;
            background: gray;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }

        h2 {
            font-size: 40px;
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }

        .invoice-info, .customer-info {
            font-size: 30px;
            display: inline-block;
            width: 48%;
            font-family: 'Courier New', monospace;
            font-weight: 500;
            padding: 0px;
            margin: 0px;
        }

        .itemized-list{
            font-size: 25px;
            border: 2px Solid black;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }

        table {
            width: 100%;
        }

        tr, td, th, p{
            font-size: larger;
            font-family: 'Courier New', monospace;
            font-weight: 500;
            text-align: left;
            border: 1px solid black;
            vertical-align: top;
        }

        p{
            font-size: larger;
            font-family: 'Courier New', monospace;
            font-weight: 500;
            text-align: left;
            vertical-align: top;
        }
        span {
            font-size: small !important;
        }

        .comments {
            font-size: smaller;
            padding: 0px;
            margin: 0px;
        }

        .cancelled {
            background: black;
            color: white;
        }
    </style>
</head>
<body>
    <h1>{{ ucfirst($section) }} KOT</h1>
    @if($reprinted==1)
        <h2>Reprinted</h2>
    @endif
    <div class="invoice-info">
        Order ID:
    </div>
    <div class="customer-info">
        {{ $orderId }}
    </div>

    <div class="invoice-info">
        Table No:
    </div>
    <div class="customer-info">
        {{ $tableNo }}
    </div>

    <div class="invoice-info">
        Covers:
    </div>
    <div class="customer-info">
        {{ $covers }}
    </div>

    <div class="invoice-info">
        Order Taker:
    </div>
    <div class="customer-info">
        {{ $orderTaker }}
    </div>

    <div class="invoice-info">
        Order Date Time:
    </div>
    <div class="customer-info" style="font-size: x-small !important;">
        {{ \Carbon\Carbon::parse($orderDateTime)->format('Y-m-d h:i A') }}
    </div>

    <table class="itemized-list">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($items as $item)
            @if($item['is_cancelled'] == 1)
                <tr class="cancelled">
                    <td>
                        {{ $item['item_name'] . ($item['item_size'] != "Regular" ? $item['item_size'] : "") }}
                        <span>Cancelled</span>
                    </td>
                    <td style="text-align: center !important;">{{ $item['quantity'] }}</td>
                </tr>
            @else
                <tr>
                    <td>
                        {{ $item['item_name'] . ($item['item_size'] != "Regular" ? $item['item_size'] : "") }}
                        @if($item['comments'])
                            <p class="comments">
                                {{$item['comments']}}
                            </p>
                        @endif
                    </td>
                    <td style="text-align: center !important;">{{ $item['quantity'] }}</td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    <br>
    <span>Print.Date-Time: {{ date('Y-m-d h:i A') }}</span>
</body>
</html>