<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – {{ config('app.name') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f5;
            font-family: Arial, Helvetica, sans-serif;
            color: #18181b;
        }
        .container {
            max-width: 480px;
            margin: 0 auto;
            padding: 24px;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 32px;
            border: 1px solid #e4e4e7;
        }
        .heading {
            font-size: 20px;
            font-weight: bold;
            color: #18181b;
            margin: 0 0 8px;
        }
        .text {
            font-size: 14px;
            line-height: 1.6;
            color: #3f3f46;
        }
        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            padding: 12px 28px;
            border-radius: 8px;
            margin: 16px 0;
        }
        .token-url {
            font-size: 12px;
            color: #71717a;
            word-break: break-all;
        }
        .muted {
            font-size: 12px;
            color: #a1a1aa;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1 class="heading">Reset Password</h1>
            <p class="text">Halo, kami menerima permintaan reset password untuk akun <strong>{{ $email }}</strong> ({{ ucfirst($role) }}) pada <strong>{{ config('app.name') }}</strong>.</p>
            <p class="text">Klik tombol di bawah ini untuk memilih kata sandi baru. Tautan berlaku selama <strong>60 menit</strong>.</p>
            <a href="{{ url('/reset-password/' . $token) }}" class="btn" style="color: #ffffff;">Reset Password</a>
            <p class="text">Jika tombol tidak berfungsi, salin tautan berikut ke browser Anda:</p>
            <p class="token-url">{{ url('/reset-password/' . $token) }}</p>
            <p class="text">Jika Anda tidak meminta reset password, abaikan email ini dan kata sandi Anda akan tetap aman.</p>
            <p class="muted">Email ini dikirim otomatis oleh {{ config('app.name') }}. Mohon jangan membalas email ini.</p>
        </div>
    </div>
</body>
</html>