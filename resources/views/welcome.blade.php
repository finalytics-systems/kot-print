<!DOCTYPE html>
<html>

<head>
    <style>
        /* Reset some default styles */
        body,
        h1,
        h2,
        p {
            margin: 0;
            padding: 0;
        }

        /* Define page size for thermal printer */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* Style for the entire invoice */
        .invoice {
            border: 1px solid #ccc;
            padding: 1px;
        }

        /* Style for the header section */
        .header {
            text-align: center;
            /* display: flex; */
        }

        .header img {
            max-width: 100px;
        }

        /* Style for the content section */
        .content {
            margin-top: 10px;
        }

        /* Style for invoice information and customer information */
        .invoice-info,
        .customer-info {
            display: inline-block;
            width: 48%;
            font-size: 18px !important;
            font-weight: bold;
        }

        /* Style for the itemized list */
        .itemized-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .itemized-list th,
        .itemized-list td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        /* Style for the totals section */
        .totals {
            margin-top: 10px;
            text-align: left;
            font-weight: bold;
        }

        /* Style for the footer section */
        .footer {
            margin-top: 10px;
            text-align: center;
        }

        /* Regular styles for the div */
        .hide-on-print {
            text-align: right;
            margin: 10px;
        }

        /* Hide the div when printing */
        @media print {
            .hide-on-print {
                display: none;
            }
        }

        .comments {
            padding: 10px 5px 10px 5px;
            border: 1px solid #ccc;
            text-align: justify;
        }

        .print-btn:hover {
            background: black !important;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="invoice">
        <div class="font-size: large"
            style="
            font-size: xx-large;
            font-weight: bolder;
            background: #2a2c2d;
            color: white;
            text-align: center;
        ">
            <p>Kitchen QT</p>
        </div>
        <div class="invoice-info">
            <p>Order No:</p>
        </div>
        <div class="customer-info">
            <p>12345</p>
        </div>


        <table class="itemized-list">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                        <tr style="font-size: larger;">
                            <td>
                                item 1
                            </td>
                            <td>
                                5
                            </td>
                        </tr>
                <!-- Add more rows for other items -->
            </tbody>
        </table>

    </div>
    <p class="comments">Comments: </p>
    <p>Print.Date-Time: {{ date('Y-m-d h:i A') }}</p>

    
</body>

</html>