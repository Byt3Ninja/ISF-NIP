<!-- resources/views/import.blade.php -->

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع شيت Excel للمستخدمين</title>
</head>
<body>
    <h1>رفع شيت Excel للمستخدمين</h1>

    @if(session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('import.users') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="file">اختر ملف Excel:</label>
            <input type="file" name="file" id="file" required>
        </div>
        <button type="submit">رفع الشيت</button>
    </form>
</body>
</html>
