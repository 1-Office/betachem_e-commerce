<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Email Templates
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Base Email Layout
|--------------------------------------------------------------------------
*/

function betachemEmailLayout(
    string $title,
    string $content
): string {

    global $emailConfig;

    $siteName = htmlspecialchars(
        $emailConfig['site_name'] ?? 'Betachem',
        ENT_QUOTES,
        'UTF-8'
    );

    return '
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>

</head>


<body style="
    margin:0;
    padding:0;
    background:#f3f6f9;
    font-family:Arial,Helvetica,sans-serif;
    color:#243447;
">


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="background:#f3f6f9;padding:35px 15px;"
>

<tr>

<td align="center">


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        max-width:620px;
        background:#ffffff;
        border-radius:8px;
        overflow:hidden;
        box-shadow:0 5px 25px rgba(20,50,80,.08);
    "
>


<!-- HEADER -->

<tr>

<td
    style="
        background:#1769aa;
        padding:28px 35px;
        text-align:left;
    "
>

<div
    style="
        color:#ffffff;
        font-size:24px;
        font-weight:bold;
        letter-spacing:1px;
    "
>
    BETACHEM
</div>

<div
    style="
        color:rgba(255,255,255,.75);
        font-size:11px;
        margin-top:5px;
        letter-spacing:1px;
    "
>
    CHEMICALS • TECHNOLOGY • SOLUTIONS
</div>

</td>

</tr>


<!-- CONTENT -->

<tr>

<td
    style="
        padding:40px 35px;
    "
>

' . $content . '

</td>

</tr>


<!-- FOOTER -->

<tr>

<td
    style="
        background:#102b49;
        padding:25px 35px;
        color:rgba(255,255,255,.65);
        font-size:11px;
        line-height:1.6;
    "
>

<strong style="color:#ffffff;">
    ' . $siteName . '
</strong>

<br>

This is an automated email from the Betachem online store.

<br><br>

Please do not reply directly to this automated message.

</td>

</tr>


</table>


</td>

</tr>

</table>

</body>

</html>
';
}


/*
|--------------------------------------------------------------------------
| Account Created Email
|--------------------------------------------------------------------------
*/

function accountCreatedEmail(
    string $firstName,
    string $email
): string {

    global $emailConfig;

    $firstName = htmlspecialchars(
        $firstName,
        ENT_QUOTES,
        'UTF-8'
    );

    $email = htmlspecialchars(
        $email,
        ENT_QUOTES,
        'UTF-8'
    );

    $accountUrl =
        rtrim($emailConfig['site_url'], '/')
        . '/account.php';


    $content = '

<h1
    style="
        margin:0 0 12px;
        color:#102b49;
        font-size:26px;
    "
>
    Welcome to Betachem
</h1>


<p
    style="
        margin:0 0 20px;
        color:#66788a;
        font-size:15px;
        line-height:1.7;
    "
>

Hello <strong>' . $firstName . '</strong>,

<br><br>

Your Betachem online account has been successfully created.

You can now browse our product catalogue, add products to your cart,
place orders and track your orders online.

</p>


<div
    style="
        background:#f3f7fb;
        border-left:4px solid #1769aa;
        padding:18px 20px;
        margin:25px 0;
    "
>

<div
    style="
        font-size:11px;
        color:#8291a0;
        text-transform:uppercase;
        letter-spacing:1px;
        margin-bottom:6px;
    "
>
    Registered Email
</div>

<div
    style="
        font-size:15px;
        font-weight:bold;
        color:#183b5d;
    "
>
    ' . $email . '
</div>

</div>


<p
    style="
        margin:25px 0;
        color:#66788a;
        font-size:14px;
        line-height:1.7;
    "
>

You can access your account at any time using the button below.

</p>


<p style="margin:30px 0;">

<a
    href="' . htmlspecialchars($accountUrl, ENT_QUOTES, 'UTF-8') . '"
    style="
        display:inline-block;
        background:#1769aa;
        color:#ffffff;
        text-decoration:none;
        padding:13px 23px;
        border-radius:4px;
        font-size:13px;
        font-weight:bold;
    "
>
    Open My Account
</a>

</p>


<p
    style="
        margin:25px 0 0;
        color:#8a98a6;
        font-size:12px;
        line-height:1.6;
    "
>

Thank you for choosing Betachem.

</p>
';


    return betachemEmailLayout(
        'Welcome to Betachem',
        $content
    );
}