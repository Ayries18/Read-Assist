<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read Assist</title>
</head>
<body>
    <h1>Read Assist</h1>
    <p>Sistem bantuan membaca untuk memahami teks dengan lebih mudah.</p>

    @if ($errors->any())
        <div style="color: red;">
            <p>{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('read.process') }}">
        @csrf

        <label for="text">Masukkan teks bacaan:</label>
        <br>
        <textarea id="text" name="text" rows="10" cols="80">{{ old('text', $text ?? '') }}</textarea>
        <br>
        <button type="submit">Proses Teks</button>
    </form>

    @isset($result)
        <hr>

        <h2>Hasil Analisis</h2>

        <p><strong>Jumlah kata:</strong> {{ $result['word_count'] }}</p>
        <p><strong>Jumlah kalimat:</strong> {{ $result['sentence_count'] }}</p>

        <h3>Ringkasan</h3>
        <p>{{ $result['summary'] ?: 'Ringkasan belum tersedia.' }}</p>

        <h3>Kata Kunci</h3>
        @if ($result['keywords']->isNotEmpty())
            <ul>
                @foreach ($result['keywords'] as $keyword)
                    <li>{{ $keyword }}</li>
                @endforeach
            </ul>
        @else
            <p>Belum ada kata kunci.</p>
        @endif
    @endisset
</body>
</html>