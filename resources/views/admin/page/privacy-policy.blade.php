<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['page']->name ?? '' }}</title>
    <style>
        /* Your CSS styles here */
    </style>
</head>
<body>
    <div class="container">
        {!! $data['page']->content ?? '' !!}
    </div>
</body>
</html>
