import { defineConfig } from 'vite';
import { resolve, join } from 'path';

export default defineConfig({
    resolve: {
        alias: {
            '@slugify': resolve(
                join(__dirname, '..', 'node_modules', 'slugify')
            )
        },
    },
});