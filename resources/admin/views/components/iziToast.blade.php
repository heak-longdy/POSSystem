<script>
    var property = {
        position: 'topCenter',
        timeout: 2500,
        animateInside: true,
        transitionIn: 'fadeIn',
        progressBarEasing: 'linear',
        pauseOnHover: true
    };

    // Define an array of notification types and their configurations
    var notifications = [
        { type: 'success', title: 'Success', method: 'success', message: @json(session('success')) },
        { type: 'error', title: 'Error', method: 'error', message: @json(session('error')) },
        { type: 'info', title: 'Info', method: 'info', message: @json(session('info')) },
        { type: 'warning', title: 'Warning', method: 'warning', message: @json(session('warning')) }
    ];

    // Loop through the notifications array
    notifications.forEach(function(notification) {
        if (notification.message) {
            iziToast[notification.method]({
                title: notification.title,
                message: notification.message,
                ...property
            });
        }
    });
</script>



