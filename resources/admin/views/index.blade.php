<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <link rel="shortcut icon" href="{!! asset('images/logo/ISEA_webicon.png') !!}"/> --}}
    <title>ADMIN</title>
    <link rel="stylesheet" href="{{ mix('admin-public/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-public/css/materialIcon.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-public/css/Material_Symbols.css') }}">
    <link rel="stylesheet" href="{{ asset('plugin/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/iziToast.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('admin-public/css/multiple-select.css') }}"> --}}

    <script src="{{ mix('admin-public/js/app.js') }}"></script>

    <script src="{{ asset('plugin/toastr.min.js') }}"></script>
    <script src="{{ asset('admin-public/js/tinymce/tinymce.min.js') }}"></script>
    {{-- <script src="{{ asset('admin-public/js/jQuery.print.min.js') }}"></script> --}}
    <script src="{{ asset('admin-public/js/feather.min.js') }}"></script>
    <script src="{{ asset('admin-public/js/select2.min.js') }}"></script>
    <script src="{{ asset('admin-public/js/jqueryUi.js') }}"></script>
    <script src="{{ asset('admin-public/js/icheck.min.js') }}"></script>
    <script src="{{ asset('js/iziToast.js') }}"></script>

</head>

<body>
    @yield('index')
    @include('admin::components.iziToast')
    @yield('script')
    <script src="{{ asset('admin-public/js/body.js') }}"></script>

</body>

<script>
    // if ('serviceWorker' in navigator) {
    //     window.addEventListener('load', () => {
    //         navigator.serviceWorker.register('/service-worker.js').then((registration) => {
    //             console.log('ServiceWorker registration successful with scope: ', registration.scope);
    //         }, (error) => {
    //             console.log('ServiceWorker registration failed: ', error);
    //         });
    //     });
    // }
    // function clearLocalStorage() {
    //       localStorage.clear();
    //       console.log('Local storage cleared');
    //     }

    //     function clearSessionStorage() {
    //       sessionStorage.clear();
    //       console.log('Session storage cleared');
    //     }

    //     function clearIndexedDB() {
    //       if (indexedDB && indexedDB.databases) {
    //         indexedDB.databases().then((databases) => {
    //           databases.forEach((dbInfo) => {
    //             var request = indexedDB.deleteDatabase(dbInfo.name);
    //             request.onsuccess = function() {
    //               console.log(`IndexedDB database ${dbInfo.name} deleted`);
    //             };
    //             request.onerror = function() {
    //               console.log(`Error deleting IndexedDB database ${dbInfo.name}`);
    //             };
    //           });
    //         });
    //       }
    //     }

    //     function clearCookies() {
    //       var cookies = document.cookie.split(";");
    //       for (var i = 0; i < cookies.length; i++) {
    //         var cookie = cookies[i];
    //         var eqPos = cookie.indexOf("=");
    //         var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
    //         document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
    //       }
    //       console.log('Cookies cleared');
    //     }

    //     function clearAllClientStorage() {
    //       clearLocalStorage();
    //       clearSessionStorage();
    //       clearIndexedDB();
    //       clearCookies();
    //       if ('serviceWorker' in navigator) {
    //         navigator.serviceWorker.getRegistrations().then((registrations) => {
    //           for (let registration of registrations) {
    //             registration.unregister();
    //           }
    //         });
    //       }
    //       console.log('All client storage cleared');
    //     }
    //     clearAllClientStorage();
    // Global Keyboard Shortcuts
    document.addEventListener('keydown', function(event) {
        // Check for Ctrl+S (Windows) or Cmd+S (Mac)
        if ((event.ctrlKey || event.metaKey) && event.key === 's') {
            event.preventDefault(); // Prevent browser save dialog

            // Find the primary submit button
            // Priority:
            // 1. Button with type="submit" and verified submit capability (basic check)
            // 2. We prefer buttons inside a form.
            
            // Looking for the main submit button usually found in our admin forms
            // Our forms usually have a submit button with type="submit" and often color="primary" or just first submit button
            const forms = document.forms;
            if (forms.length > 0) {
                // If we have a form, try to find its submit button
                // We target the currently active or visible form if there are multiple, but usually there's one main form-wrapper
                const mainForm = document.querySelector('form.form-wrapper') || forms[0];
                
                if (mainForm) {
                    // Try to find the specific "Submit" button first (not "Save & New" or "Cancel")
                    // We look for a submit button that doesn't have specific name='save_opt' (which is Save & New) 
                    // or where the text content suggests it's the main save.
                    // But simpler: just click the first submit button that isn't hidden.
                    const submitBtn = mainForm.querySelector('button[type="submit"]:not([name="save_opt"])') || mainForm.querySelector('button[type="submit"]');
                    
                    if (submitBtn) {
                        submitBtn.click();
                        return;
                    }
                }
            }
        }
    });

</script>

</html>
