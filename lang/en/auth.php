<?php

return [
    'sign-in' => [
        'title' => 'Sign In',
        'form' => [
            'username' => [
                'label' => 'Email',
                'placeholder' => 'Enter Email',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter Password',
            ],
            'remember' => 'keep signed in',
            'forgot-password' => 'Forgot Password?',
            'button' => 'Sign In',
            'error_login' => 'Username or Password is incorrect.',
        ],
    ],
    'forgot-password' => [
        'title' => 'Forgot Password',
        'form' => [
            'email' => [
                'label' => 'Email',
                'placeholder' => 'Enter Email',
            ],
            'has-account' => 'Has an account? Sign In',
            'button' => 'Send to Email',
        ],
    ],
    'reset-password' => [
        'title' => 'Reset Password',
        'form' => [
            'new-password' => [
                'label' => 'New Password',
                'placeholder' => 'Enter New Password',
            ],
            'confirm-new-password' => [
                'label' => 'Confirm New Password',
                'placeholder' => 'Enter Confirm New Password',
            ],
            'has-account' => 'Has an account? Sign In',
            'button' => 'Reset Now',
        ],
    ]
];
