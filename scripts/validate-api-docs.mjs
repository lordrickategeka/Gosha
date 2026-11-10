import SwaggerParser from '@apidevtools/swagger-parser';
import { fileURLToPath } from 'node:url';

for (const service of ['garagehq', 'cream-theme', 'onepass', 'assistant']) {
    const file = fileURLToPath(new URL(`../docs/openapi/${service}.json`, import.meta.url));
    const spec = await SwaggerParser.validate(file, { resolve: { external: false } });
    const count = Object.values(spec.paths).reduce((sum, path) => sum + Object.keys(path).filter(method => ['get', 'post', 'put', 'patch', 'delete', 'head', 'options'].includes(method)).length, 0);
    process.stdout.write(`${spec.info.title}: valid OpenAPI ${spec.openapi}, ${count} operations\n`);
}
