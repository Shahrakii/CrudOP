<?php

return match(env('CSS_FRAMEWORK','tailwind')) {
    'tailwind' => [
        'text' => 'border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500',
        'number' => 'border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500',
        'select' => 'border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500',
        'textarea' => 'border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500',
        'checkbox' => 'mr-2',
        'email' => 'border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500',
        'password' => 'border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500',
    ],
    'bootstrap' => [
        'text' => 'form-control',
        'number' => 'form-control',
        'select' => 'form-select',
        'textarea' => 'form-control',
        'checkbox' => 'form-check-input',
        'email' => 'form-control',
        'password' => 'form-control',
    ],
    default => []
};