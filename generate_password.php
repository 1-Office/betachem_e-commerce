<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Password Hash Generator
|--------------------------------------------------------------------------
|
| Temporary development tool.
| Delete this file when im finished using it.
|
*/

$password = "password";

$hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Betachem Password Hash</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: #102b49;
            margin-top: 0;
        }

        .label {
            font-weight: bold;
            color: #333333;
            margin-top: 25px;
            margin-bottom: 8px;
        }

        .password {
            background: #f1f5f9;
            padding: 12px;
            border-radius: 6px;
        }

        textarea {
            width: 100%;
            min-height: 110px;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #dce3ea;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
            resize: vertical;
        }

        .warning {
            margin-top: 25px;
            padding: 12px 15px;
            background: #fff3cd;
            border: 1px solid #ffecb5;
            border-radius: 6px;
            color: #664d03;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>Betachem Password Hash</h2>

    <div class="label">
        Plain Password
    </div>

    <div class="password">
        <?= htmlspecialchars($password, ENT_QUOTES, 'UTF-8') ?>
    </div>

    <div class="label">
        Password Hash
    </div>

    <textarea
        readonly
        onclick="this.select();"
    ><?= htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') ?></textarea>

    <div class="warning">
        <strong>Important:</strong>
        This is a temporary development tool. Delete this PHP file
        when you are finished using it, especially before putting the
        website online.
    </div>

</div>

</body>

</html>