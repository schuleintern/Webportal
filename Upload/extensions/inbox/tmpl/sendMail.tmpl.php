<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Neue Nachricht</title>
    <style>
        img {
            border: none;
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
        }

        body {
            background-color: #f6f6f6;
            font-family: sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            width: 100%;
        }

        table {
            border-collapse: separate;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%;
        }

        table td {
            font-family: sans-serif;
            font-size: 14px;
            vertical-align: top;
        }


        .container {
            display: block;
            Margin: 0 auto !important;
            /* makes it centered */
            max-width: 580px;
            padding: 10px;
            width: 580px;
        }

        .content {
            box-sizing: border-box;
            display: block;
            Margin: 0 auto;
            max-width: 580px;
            padding: 10px;
        }

        .main {
            background: #ffffff;
            border-radius: 3px;
            width: 100%;
        }

        .wrapper {
            box-sizing: border-box;
            padding: 20px;
        }


        h1,
        h2,
        h3,
        h4 {
            color: #000000;
            font-family: sans-serif;
            font-weight: 400;
            line-height: 1.4;
            margin: 0;
            Margin-bottom: 30px;
        }

        h1 {
            font-size: 35px;
            font-weight: 300;
            text-align: center;
            text-transform: capitalize;
        }

        h2 {
            font-size: 22px;
            font-weight: 300;
            text-align: center;
        }

        p,
        ul,
        ol {
            font-family: sans-serif;
            font-size: 14px;
            font-weight: normal;
            margin: 0;
            Margin-bottom: 15px;
        }

        p li,
        ul li,
        ol li {
            list-style-position: inside;
            margin-left: 5px;
        }

        a {
            color: #3498db;
            text-decoration: underline;
        }

        .logo {
            width: 5rem;
        }

        .header td {
            background-color: {SKINCOLOR};
            color: #fff;
            padding: 1rem;

            text-align: left;
            horiz-align: left;

            font-size: 16pt;
            font-weight: 400;
            line-height: 1.4;
            margin: 0;
        }

        hr {
            border: 0;
            border-bottom: 1px solid #f6f6f6;
            Margin: 20px 0;
        }

        .footer {
            padding: 1rem;
            text-align: center;
            opacity: 0.4;
        }

        @media only screen and (max-width: 620px) {
            table[class=body] h1 {
                font-size: 28px !important;
                margin-bottom: 10px !important;
            }

            table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
                font-size: 16px !important;
            }

            table[class=body] .wrapper,
            table[class=body] .article {
                padding: 10px !important;
            }

            table[class=body] .content {
                padding: 0 !important;
            }

            table[class=body] .container {
                padding: 0 !important;
                width: 100% !important;
            }

            table[class=body] .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }

            table[class=body] .btn table {
                width: 100% !important;
            }

            table[class=body] .btn a {
                width: 100% !important;
            }

            table[class=body] .img-responsive {
                height: auto !important;
                max-width: 100% !important;
                width: auto !important;
            }
        }


    </style>
</head>
<body class="">
<table border="0" cellpadding="0" cellspacing="0" class="body">
    <tr>
        <td>&nbsp;</td>
        <td class="container">
            <div class="content">

                <table class="main">

                    <tr>
                        <td class="wrapper">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr class="header">
                                    <td>
                                        <img src="{LOGO}" class="logo">
                                    </td>
                                    <td>
                                        Neue Nachricht im Portal<br>{SITENAME}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">

                                        <br>

                                        <p><b>Betreff:</b> {SUBJECT} </p>
                                        <p><b>Absender:</b> {SENDER}</p>
                                        <p><b>Betrifft Sie als:</b> {EMPFAENGER}</p>
                                        <p><b>Empfänger:</b> {EMPFAENGERS}</p>

                                        <hr>
                                        <p><b>{FILES}</b></p>
                                        <p><b>{CONFIRM}</b></p>

                                        <hr>

                                        <b>Bitte beachten Sie: Sie können NICHT mit der Antwortfunktion Ihres
                                            E-Mailprogramms auf diese Nachricht antworten. <a href="{REPLAYLINK}" target="_blank">Zum Antworten</a> oder loggen Sie sich
                                            bitte im <a href="{PORTALLINK}" target="_blank">Portal</a> ein und antworten Sie dort unter "Nachrichten".</b>

                                        <hr>

                                        <p>{BODY}</p>

                                        <hr><hr>
                                        <br>

                                        <p><b>Impressum:</b><br/>{IMPRESSUM}</p>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

                <div class="footer">
                    Sie können den Empfang dieser E-Mailbenachrichtigungen hier beenden: <a href="{PORTALLINK}">Benachrichtigungen beenden</a>.</p>
                    <hr>
                    Automatische E-Mail der Software: <a href="https://schule-intern.de">Schule-intern.de</a>
                </div>

            </div>
        </td>
        <td>&nbsp;</td>
    </tr>
</table>
</body>
</html>