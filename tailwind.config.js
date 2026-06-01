import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        require('daisyui'),
    ],

    daisyui: {
        themes: [
            {
                garage: {
                    "primary": "#8A5A44",
                    "primary-content": "#ffffff",
                    "secondary": "#B08A74",
                    "secondary-content": "#ffffff",
                    "accent": "#8A5A44",
                    "accent-content": "#ffffff",
                    "neutral": "#1A1814",
                    "neutral-content": "#ffffff",
                    "base-100": "#ffffff",
                    "base-200": "#F7F4ED",
                    "base-300": "#E0DAC9",
                    "base-content": "#1A1814",
                    "info": "#7A95BD",
                    "info-content": "#ffffff",
                    "success": "#4D8A69",
                    "success-content": "#ffffff",
                    "warning": "#C28A2E",
                    "warning-content": "#1A1814",
                    "error": "#B65B52",
                    "error-content": "#ffffff",
                    "primary-light": "#A6745B",
                    "primary-dark": "#6E4635",
                    "secondary-light": "#C6A691",
                    "secondary-dark": "#8F6D58",
                },
            },
            {
                garageDark: {
                    "primary": "#D49A7A",
                    "primary-content": "#131210",
                    "secondary": "#B08A74",
                    "secondary-content": "#131210",
                    "accent": "#D49A7A",
                    "accent-content": "#131210",
                    "neutral": "#1F1D1A",
                    "neutral-content": "#F2EFE7",
                    "base-100": "#1F1D1A",
                    "base-200": "#131210",
                    "base-300": "#2D2A25",
                    "base-content": "#F2EFE7",
                    "info": "#86A6D0",
                    "info-content": "#131210",
                    "success": "#7DAF8F",
                    "success-content": "#131210",
                    "warning": "#D3A255",
                    "warning-content": "#131210",
                    "error": "#D58A80",
                    "error-content": "#131210",
                },
            },
            "light",
            "dark",
        ],
        darkTheme: "garageDark",
        base: true,
        styled: true,
        utils: true,
        logs: false,
    },
};
