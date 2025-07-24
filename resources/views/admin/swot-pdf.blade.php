<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            margin: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section label {
            font-weight: bold;
        }
        .section div {
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="section">
        <label>Strengths</label>
        <div>{{ $strength }}</div>
    </div>
    
    <div class="section">
        <label>Weaknesses</label>
        <div>{{ $weakness }}</div>
    </div>

    <div class="section">
        <label>Strengths Recommendations</label>
        <div>{{ $srecommendation }}</div>
    </div>

   
    <div class="section">
        <label>Weaknesses Recommendations</label>
        <div>{{ $wrecommendation }}</div>
    </div>

    <div class="section">
        <label>Opportunities</label>
        <div>{{ $opportunity }}</div>
    </div>

    <div class="section">
        <label>Threats</label>
        <div>{{ $threat }}</div>
    </div>

    <div class="section">
        <label>Opportunities Recommendations</label>
        <div>{{ $orecommendation }}</div>
    </div>

   
    <div class="section">
        <label>Threats Recommendations</label>
        <div>{{ $trecommendation }}</div>
    </div>
</div>

</body>
</html>
