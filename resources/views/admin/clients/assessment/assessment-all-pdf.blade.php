<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Details</title>
    <style>
        @font-face {
            font-family: 'Cairo-ExtraLight';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/cairo/Cairo-ExtraLight.ttf') }}") format('truetype');
        }

        body {
            font-family: 'Cairo-ExtraLight', sans-serif;
            direction: rtl;
            text-align: left;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <p><strong>Company:</strong> {{ $companyName }}</p>
    <p><strong>Assessment Start Date:</strong> {{ \Carbon\Carbon::parse($start_date)->format('Y-m-d') }}</p>
    <p><strong>Assessment End Date:</strong> {{ \Carbon\Carbon::parse($end_date)->format('Y-m-d') }}</p>
    <p><strong>Overall Weighted Score:</strong> {{ number_format($scoresTotals, 2) }}</p>
</body>
</html>
