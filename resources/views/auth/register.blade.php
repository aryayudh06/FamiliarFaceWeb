<!DOCTYPE html>
<html>

<head>
    <title>Register - FamiliarFace</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap");
        @import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap");
        @import url("https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap");
        @import url("https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&display=swap");

        body {
            background-image: url('/images/backgroundHD.jpg');
            background-position: center;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-content: center;
            min-height: 100vh;
            width: 100%;
            margin: 0;
        }

        .register-form-container {
            background-color: rgb(233, 235, 255, 0.8);
            padding: 2rem;
            border-radius: 45px;
            align-self: center;
            min-height: 500px;
            width: 90%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0.5rem;
        }

        form {
            width: 100%;
            padding: 1rem;
        }

        h2 {
            margin: 1rem 0 2rem 0;
            display: flex;
            justify-content: center;
            font-family: Montserrat;
            letter-spacing: 6px;
            font-size: 2rem;
            color: white;
            text-shadow:
                -2px -2px 2px #cc8fae,
                2px -2px 2px #cc8fae,
                -2px 2px 2px #cc8fae,
                2px 2px 2px #cc8fae;
        }

        .input-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin: 1rem 0;
            font-family: Roboto Condensed;
        }

        .input-wrapper label {
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .input-wrapper input {
            padding: 0.75rem 1rem;
            border: 1px solid #ccc;
            border-radius: 30px;
            font-size: 1rem;
            width: 100%;
        }

        button {
            width: 50%;
            margin: 1.5rem auto;
            display: block;
            font-family: Montserrat;
            font-weight: bold;
            border-radius: 30px;
            border: 1px solid #ccc;
            font-size: 1rem;
            padding: 0.75rem;
            background-color: #cc8fae;
            color: aliceblue;
            letter-spacing: 2px;
            cursor: pointer;
        }

        button:hover {
            background-color: #b87f9e;
        }

        .other-container {
            background-color: rgb(233, 235, 255, 0.8);
            padding: 1rem 2rem;
            border-radius: 45px;
            align-self: center;
            min-height: 60px;
            width: 90%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0.5rem;
        }

        .other-container p {
            margin: 0%;
            text-align: center;
            text-decoration: none;
            color: black;
            font-family: Roboto Condensed;
            font-size: medium;
        }

        .other-container a {
            font-weight: bold;
            margin: 0%;
            text-align: center;
            text-decoration: none;
            font-family: Montserrat;
            font-size: medium;
            color: #cc8fae;
            text-shadow:
                -1px -1px 2px white,
                1px -1px 2px white,
                -1px 1px 2px white,
                1px 1px 2px white;
        }

        .other-container a:hover {
            color: white;
            text-shadow:
                -1px -1px 2px #cc8fae,
                1px -1px 2px #cc8fae,
                -1px 1px 2px #cc8fae,
                1px 1px 2px #cc8fae;
        }

        .error-message {
            color: #dc2626;
            font-size: small;
            margin-top: 0.5rem;
            text-align: center;
            font-family: Roboto;
        }
    </style>
</head>

<body>
    <div class="register-form-container">
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <h2>REGISTER</h2>

            <div class="input-wrapper">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
            </div>

            <div class="input-wrapper">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="input-wrapper">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="input-wrapper">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <button type="submit">Register</button>
        </form>
    </div>

    <div class="other-container">
        <p>Already have an account? <a href="{{ route('login') }}" class="loginRegister">Login Here</a></p>
    </div>
</body>

</html>
