<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Inventory dan Kasir</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body class="login">
    <div class="login-container">
        <h1>Sistem Inventory dan Kasir</h1>
        <p class="login-dtc-multimedia">DTC MULTIMEDIA</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="login-input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="login-input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="login-input-group">
                <input type="submit" value="Login">
            </div>
        </form>

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</body>
</html>
