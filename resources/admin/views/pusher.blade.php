<!DOCTYPE html>
<head>
  <title>Pusher Test</title>
  <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
  <script>

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('bfc6103b0be0f92ec248', {
      cluster: 'ap1',
      encrypted: true
    });

    var channel = pusher.subscribe('booking-text-channel');
    channel.bind('booking-text-event', function(data) {
        console.log(data,'pushergggggggggggssssssssssssssssssssssss');
        alert('hiiiii');
    });

  </script>
</head>
<body>
  <h1>Pusher Test</h1>
  <p>
    Try publishing an event to channel <code>my-channel</code>
    with event name <code>my-event</code>.
  </p>
</body>
