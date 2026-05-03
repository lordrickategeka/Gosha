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
                    "primary": "#2C72B3",
                    "primary-content": "#ffffff",
                    "secondary": "#5BA3E0",
                    "secondary-content": "#ffffff",
                    "accent": "#4A8FCC",
                    "accent-content": "#ffffff",
                    "neutral": "#2B3440",
                    "neutral-content": "#ffffff",
                    "base-100": "#ffffff",
                    "base-200": "#f3f4f6",
                    "base-300": "#e5e7eb",
                    "base-content": "#1f2937",
                    "info": "#4A8FCC",
                    "info-content": "#ffffff",
                    "success": "#36D399",
                    "success-content": "#ffffff",
                    "warning": "#FBBD23",
                    "warning-content": "#2B3440",
                    "error": "#F87272",
                    "error-content": "#ffffff",
                    "primary-light": "#4A8FCC",
                    "primary-dark": "#1E4F7F",
                    "secondary-light": "#7DB9E8",
                    "secondary-dark": "#2E5F8F",
                },
            },
            {
                garageDark: {
                    "primary": "#8da6ea",          // Light blue accent
                    "primary-content": "#111111",
                    "secondary": "#a4adb8",        // Muted steel
                    "secondary-content": "#111111",
                    "accent": "#d6a36c",           // Warm amber
                    "accent-content": "#000000",
                    "neutral": "#221f1a",          // Warm charcoal
                    "neutral-content": "#f7f4ee",
                    "base-100": "#201d19",         // Panel
                    "base-200": "#171511",         // Canvas
                    "base-300": "#36312a",         // Border
                    "base-content": "#f3efe7",     // Text
                    "info": "#7aa0ea",             // Info blue
                    "info-content": "#000000",
                    "success": "#79b18d",          // Muted green
                    "success-content": "#000000",
                    "warning": "#d6a36c",          // Warm amber
                    "warning-content": "#000000",
                    "error": "#df8b80",            // Soft red
                    "error-content": "#000000",
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
