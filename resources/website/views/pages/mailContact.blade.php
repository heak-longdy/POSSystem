<!DOCTYPE html>
<html>
<head>
    <title>{{$name}}</title>
    <style>
        p{
            padding: 0 !important;
            margin: 0 !important;
        }
    </style>
</head>
<body>
    <p><strong>Full Name:</strong>&nbsp;{{$name}}</p>
    <p><strong>Email:</strong>&nbsp;{{ $email }}</p>
    <p><strong>Subject:</strong>&nbsp;{{ isset($subject) ? $subject : "" }}</p>
    <p><strong>Description:</strong></p>
    <p>{{$description}}</p>
</body>
</html>