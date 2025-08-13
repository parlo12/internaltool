<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
    <style type="text/css">
        body {
            font-family: "Times New Roman", Georgia, serif;
            font-size: 16px;
            color: #333333;
            line-height: 1.6;
            background-color: #ffffff;
        }



        .email-container {

            /* ensures left alignment */
        }

        @media only screen and (max-width: 620px) {
            .email-container {
                width: 100%;
            }
        }
    </style>

</head>

<body>
    <div class="email-container">
        {!! nl2br(e($details['message'])) !!}

        @if (isset($details['signature']))
            <p>{!! nl2br(e($details['signature'])) !!}</p>
        @endif
    </div>
</body>

</html>
