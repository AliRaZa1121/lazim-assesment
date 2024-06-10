<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>{{ $app_name }}</title>
    <meta name="description" content="{{ $app_name }}">
    <style type="text/css">
        a:hover {
            text-decoration: underline !important;
            color: #fff;
        }

        p {
            text-align: left;
            padding: 15px;
        }

        body {
            margin: 0;
            background-color: #f2f3f8;
            color: #333;
        }

        table {
            font-family: 'Open Sans', sans-serif;
        }
    </style>
</head>

<body>
    <!-- 100% body table -->
    <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8">
        <tr>
            <td>
                <table
                    style="background-color: #ffffff; border-radius: 3px; max-width: 670px; margin: 0 auto; padding: 20px; box-shadow: 0 6px 18px 0 rgba(0,0,0,.06);"
                    width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="height:40px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:0 15px;">
                            <p
                                style="font-size:18px; font-weight:600; margin:0; line-height:24px; text-align:left; padding-bottom: 15px; color: #0056b3;">
                                <strong>Hey {{ $name }}</strong>.
                            </p>
                            <p
                                style="font-size:16px; line-height:24px; text-align:left; padding-bottom: 15px; color: #333;">
                                {!! $body !!}
                            </p>

                            @if (isset($link))
                                <a href="{{ $link }}"
                                    style="background:#0056b3;text-decoration:none !important; display:inline-block; font-weight:500; margin-top:24px; color:#fff;text-transform:uppercase; font-size:14px;padding:10px 24px;display:inline-block;border-radius:50px;">
                                    {{ $linkText }} </a>
                            @endif

                            {{-- add space --}}
                            <p style="font-size:13px; line-height:24px; text-align:left; padding-top: 15px;">
                                &nbsp;
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:40px;">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!--/100% body table-->
</body>

</html>
