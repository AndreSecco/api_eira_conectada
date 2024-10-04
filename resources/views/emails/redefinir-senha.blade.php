<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <style>
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            border: 1px solid #ddd;
        }

        .button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #3498db;
            color: white!important;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background-color: #2980b9;
        }

        .message {
            text-align: center;
        }

        .ii a[href] {
            color: white!important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="message">Redefinir Senha</h1>
        <p class="message">Clique no botão abaixo para redefinir sua senha:</p>
        <a href="{{ url('http://localhost:5173/auth/redefinir-senha/' . $token . '/' . urlencode($email)) }}" class="button">
            Redefinir Senha
        </a>
    </div>
</body>
</html>
