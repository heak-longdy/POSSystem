<!DOCTYPE html>
<html>
<head>
    <title>{{$job_id}}&nbsp;{{$job_title}}</title>
    <style>
        p{
            padding: 0 !important;
            margin: 0 !important;
        }
    </style>
</head>
<body>
    <p><strong>Job Code:</strong>&nbsp;{{$job_id}}</p>
    <p><strong>Job Title:</strong>&nbsp;{{$job_title}}</p>
    <p><strong>Full Name:</strong>&nbsp;{{$name}}</p>
    <p><strong>Email:</strong>&nbsp;{{ $email }}</p>
    <p><strong>Phone:</strong>&nbsp;{{$phone}}</p>
    <p><strong>Description:</strong></p>
    <p>{{$description}}</p>
</body>
</html>