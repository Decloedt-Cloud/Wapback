<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation de votre mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f9fc;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        a.button {
            background-color: #4CAF50;
            color: white !important;
            padding: 15px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
        }
        a.button:hover {
            background-color: #45a049;
        }
        p {
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Réinitialisation de votre mot de passe</h2>

        <p>Bonjour {{ $user->name }},</p>

        <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>

        <p style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button" target="_blank" rel="noopener noreferrer">
                Réinitialiser mon mot de passe
            </a>
        </p>

        <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>

        <p>Merci,<br>L'équipe WAP</p>
    </div>
</body>
</html>
