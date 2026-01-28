import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#2C72B3',
                    light: '#4A8FCC',
                    dark: '#1E4F7F',
                },
                secondary: {
                    DEFAULT: '#5BA3E0',
                    light: '#7DB9E8',
                    dark: '#2E5F8F',
                },
            },
        },
    },

    plugins: [forms, daisyui],
    daisyui: {
        themes: [
            {
                light: {
                    ...require('daisyui/src/theming/themes')['light'],
                    primary: '#2C72B3',
                    'primary-focus': '#1E4F7F',
                    'primary-content': '#ffffff',
                    secondary: '#5BA3E0',
                    'secondary-focus': '#2E5F8F',
                    'secondary-content': '#ffffff',
                },
            },
        ],
    },
};
