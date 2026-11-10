import SwaggerUIBundle from 'swagger-ui-dist/swagger-ui-bundle.js';
import SwaggerUIStandalonePreset from 'swagger-ui-dist/swagger-ui-standalone-preset.js';
import 'swagger-ui-dist/swagger-ui.css';

const container = document.getElementById('swagger-ui');
if (container) {
    SwaggerUIBundle({
        dom_id: '#swagger-ui',
        urls: JSON.parse(container.dataset.specs),
        'urls.primaryName': 'GarageHQ application API',
        presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
        layout: 'StandaloneLayout',
        deepLinking: true,
        filter: true,
        docExpansion: 'list',
        defaultModelsExpandDepth: 0,
        supportedSubmitMethods: [],
        persistAuthorization: false,
        validatorUrl: null,
        queryConfigEnabled: false,
    });
}
