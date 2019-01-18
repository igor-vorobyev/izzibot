<html>
    <head>
        <title>Test form</title>
    </head>
    <body>
        <form action="/thumb/" method="post">
            @csrf

            Link:
            <input type="text" name="link" />
            <hr/>
            <input type="submit" value="Get" />
        </form>
    </body>
</html>