import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/garagehq.css",
                "resources/js/app.js",
                "resources/css/documentation.css",
                "resources/js/api-docs.js",
                "resources/css/assistant.css",
                "resources/js/assistant.js",
            ],

            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
});
