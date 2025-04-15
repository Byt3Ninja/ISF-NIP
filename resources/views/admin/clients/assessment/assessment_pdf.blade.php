<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Questions</title>
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
                    @foreach ($subcategory['questions'] as $key => $question)
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
</html>
