<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription Client</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            background: #ECECEC;
            padding: 30px 15px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 150px;
            height: auto;
        }

        .content-table {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        .header {
            background: #87CEEB;
            /* Bleu pour clients */
            border-radius: 15px 15px 0 0;
            padding: 15px;
            text-align: center;
        }

        .header span {
            font-size: 20px;
            font-weight: bold;
            color: #fff;
        }

        .body {
            background: #f0f7fa;
            padding: 20px;
        }

        .body p {
            font-size: 14px;
            color: #000;
            line-height: 1.5;
            margin: 15px 0;
        }

        .body a {
            color: #0047AB;
            text-decoration: none;
        }

        .button {
            display: inline-block;
            padding: 15px 25px;
            font-size: 16px;
            color: #fff !important;
            background-color: #87CEEB;
            /* Bleu pour clients */
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        .button:hover {
            background-color: #6DB4E8;
            /* Bleu plus foncé */
        }

        .button-container {
            text-align: center;
        }

        .info-container {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .info-image {
            width: 100%;
            max-width: 200px;
            margin-right: 38px;
            margin-bottom: 20px;
        }

        .info-content {
            flex: 1;
            min-width: 300px;
        }

        .footer {
            background: #87CEEB;
            /* Bleu pour clients */
            border-radius: 0 0 15px 15px;
            color: #fff;
            font-size: 14px;
        }

        .footer table {
            width: 100%;
        }

        .footer td {
            padding: 10px;
        }

        @media only screen and (max-width: 600px) {
            .content-table {
                width: 100% !important;
            }

            .header span {
                font-size: 18px;
            }

            .footer td {
                display: block;
                text-align: center !important;
                width: 100% !important;
            }

            .info-container {
                flex-direction: column;
            }

            .info-image {
                margin-right: 0;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <h2 style="color: #87CEEB; margin: 0; font-size: 24px; font-weight: bold;">WAP CLIENTS</h2>
        </div>
        <table class="content-table">
            <tr>
                <td class="header">
                    <span>Confirmation d'inscription Client</span>
                </td>
            </tr>
            <tr>
                <td class="body">
                    <div class="info-container">
                        <div class="info-content">
                            <p><strong>Bienvenue, {{ $user->name }} !</strong></p>
                            <p>Merci de vous être inscrit en tant que client sur notre plateforme. Votre compte a été
                                créé avec succès.</p>

                            <p><strong>Nom :</strong> {{ $user->name }}</p>
                            <p><strong>Email :</strong> {{ $user->email }}</p>
                            <p><strong>Rôle :</strong> Client</p>

                            <p>Vous pouvez maintenant vous connecter et profiter de tous nos services.</p>

                            <div class="button-container">
                                <a href="{{ $verificationUrl }}" class="button">Vérifier mon adresse email</a>
                            </div>

                            <p>Découvrez dès maintenant notre catalogue de services et trouvez le professionnel qui
                                répondra à vos besoins.</p>

                            <p>Si vous avez des questions concernant l'utilisation de notre plateforme, notre équipe
                                support est à votre disposition : <a href="mailto:clients@wap.com">clients@wap.com</a>
                            </p>

                            <p>Merci,<br><strong>L'équipe WAP Clients</strong></p>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <table>
                        <tr>
                            <td style="text-align: left; width: 33%;">
                                <span style="color: #fff; font-size: 12px;">Contact clients: clients@wap.com</span>
                            </td>
                            <td style="text-align: center; width: 34%;">
                                © {{ now()->year }} WAP. Tous droits réservés.
                            </td>
                            <td style="text-align: right; width: 33%;">
                                <span style="color: #fff; font-size: 12px;">www.wap.com/clients</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
