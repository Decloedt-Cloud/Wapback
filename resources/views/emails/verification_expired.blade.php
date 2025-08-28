<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lien de vérification expiré</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
        }

        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 30px;
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        form {
            display: inline-block;
        }

        .message {
            margin-bottom: 20px;
            color: #333;
        }
    </style>
</head>

<body>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h1>Lien de vérification expiré</h1>
    <p>Le lien de vérification que vous avez utilisé a expiré ou est invalide.</p>

    <div class="message">
        Si vous souhaitez recevoir un nouveau lien de vérification, cliquez sur le bouton ci-dessous :
    </div>

    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <button type="submit">Renvoyer le lien</button>
    </form>

</body>

</html>
