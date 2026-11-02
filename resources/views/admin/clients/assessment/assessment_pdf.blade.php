<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Questions</title>
    <style>
        body {
            font-family: 'Cairo-ExtraLight', sans-serif;
            direction: ltr;
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
    
    <p><strong>{{ __('forms.startup_name', [], 'en') }}:</strong> {{ $companyName }}</p>
    <p><strong>Assessment Start Date:</strong> {{ \Carbon\Carbon::parse($start_date)->format('Y-m-d') }}</p>
    <p><strong>Assessment End Date:</strong> {{ \Carbon\Carbon::parse($end_date)->format('Y-m-d') }}</p>
    <p><strong>Overall Weighted Score:</strong> {{ number_format($scoresTotals, 2) }}</p>

    <h2>Categories</h2>
    @foreach ($user_scores as $key => $assessment)
        <h3>category: {{ $assessment->name }}</h3>
        @foreach ($assessment['subcategories'] as $key => $subcategory)
            <h4>SubCategory: {{ $subcategory->name }}</h4>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Questions</th>
                        <th>Answers</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subcategory['questions']->values() as $key => $question)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $question['QuestionBank'] }}</td>
                            <td>{{ $question['Result_assessment'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endforeach
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function(){ window.print(); }, 250);
    });
</script>
</html>
