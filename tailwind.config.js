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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
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
                    "primary": "#3b82f6",          // Blue
                    "primary-content": "#ffffff",
                    "secondary": "#64748b",        // Slate
                    "secondary-content": "#ffffff",
                    "accent": "#f59e0b",           // Amber
                    "accent-content": "#000000",
                    "neutral": "#1e293b",          // Slate 800
                    "neutral-content": "#f1f5f9",
                    "base-100": "#ffffff",         // White
                    "base-200": "#f8fafc",         // Slate 50
                    "base-300": "#e2e8f0",         // Slate 200
                    "base-content": "#1e293b",     // Slate 800
                    "info": "#0ea5e9",             // Sky 500
                    "info-content": "#ffffff",
                    "success": "#22c55e",          // Green 500
                    "success-content": "#ffffff",
                    "warning": "#f59e0b",          // Amber 500
                    "warning-content": "#000000",
                    "error": "#ef4444",            // Red 500
                    "error-content": "#ffffff",
                },
            },
            {
                garageDark: {
                    "primary": "#60a5fa",          // Blue 400
                    "primary-content": "#000000",
                    "secondary": "#94a3b8",        // Slate 400
                    "secondary-content": "#000000",
                    "accent": "#fbbf24",           // Amber 400
                    "accent-content": "#000000",
                    "neutral": "#334155",          // Slate 700
                    "neutral-content": "#f1f5f9",
                    "base-100": "#1e293b",         // Slate 800
                    "base-200": "#0f172a",         // Slate 900
                    "base-300": "#334155",         // Slate 700
                    "base-content": "#f1f5f9",     // Slate 100
                    "info": "#38bdf8",             // Sky 400
                    "info-content": "#000000",
                    "success": "#4ade80",          // Green 400
                    "success-content": "#000000",
                    "warning": "#fbbf24",          // Amber 400
                    "warning-content": "#000000",
                    "error": "#f87171",            // Red 400
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
