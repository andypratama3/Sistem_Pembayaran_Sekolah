<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        body { font-family: 'Inter', Helvetica, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f8fafc; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: #3454D1; padding: 32px; text-align: center; color: #ffffff; }
        .content { padding: 40px; color: #334155; line-height: 1.6; }
        .footer { padding: 24px; text-align: center; color: #94a3b8; font-size: 12px; background-color: #f1f5f9; }
        .button { display: inline-block; padding: 12px 24px; background-color: #3454D1; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 20px; }
        .info-card { background-color: #f1f5f9; border-radius: 8px; padding: 20px; margin: 24px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px; }
        .label { font-weight: 600; }
        h1 { margin: 0; font-size: 24px; }
        h2 { font-size: 18px; color: #1e293b; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ config('app.name') }}</h1>
            </div>
            <div class="content">
                @yield('content')
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Semua Hak Dilindungi.<br>
                Jl. Pendidikan No. 123, Indonesia
            </div>
        </div>
    </div>
</body>
</html>
