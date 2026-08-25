import { defineConfig, loadEnv, ViteDevServer } from 'vite';
import vuePlugin from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import * as fs from 'node:fs';
import { fileURLToPath } from 'node:url';

const latteReloadPlugin = {
    name: 'latte-reload',
    configureServer(server: ViteDevServer) {
        server.watcher.add(fileURLToPath(new URL('./templates/**/*/*.latte', import.meta.url)));
        server.watcher.on('change', (path: string) => {
            if (path.endsWith('.latte')) {
                server.ws.send({
                    type: 'full-reload',
                });
            }
        });
    }
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devPort: string = env.VITE_LOCAL_PORT || '3000';

    return {
        base: './',
        publicDir: false,
        plugins: [vuePlugin({}), tailwindcss(), latteReloadPlugin],
        build: {
            outDir: './public/assets',
            emptyOutDir: true,
            rolldownOptions: {
                input: {
                    app: './assets/scripts/app.ts',
                    admin: './assets/scripts/admin.ts',
                    stylesheet: './assets/styles/app.css'
                },
                output: {
                    entryFileNames: `[name].js`,
                    chunkFileNames: `[name].js`,
                    assetFileNames: (assetInfo): string => {
                        const originalName = assetInfo.names?.[0] ?? '';
                        const extType = originalName.split('.').pop()?.toLowerCase() ?? '';

                        if (/\.(png|jpe?g|gif|svg|webp|avif)$/i.test(originalName)) {
                            return `images/[name].[ext]`;
                        }

                        if (/\.(woff2?|eot|ttf|otf)$/i.test(originalName)) {
                            return `fonts/[name].[ext]`;
                        }

                        return extType ? `${extType}/[name].[ext]` : `[name].[ext]`;
                    }
                }
            }
        },
        server: {
            host: '0.0.0.0',
            origin: `${env.DEFAULT_URI}:${devPort}`,
            port: parseInt(env.VITE_SERVER_PORT || '3000'),
            strictPort: true,
            https: {
                key: fs.readFileSync('/etc/nginx/certs/{APP_NAME}.key'),
                cert: fs.readFileSync('/etc/nginx/certs/{APP_NAME}.crt'),
            },
            cors: true,
            watch: {
                usePolling: true,
            },
            hmr: {
                host: '{APP_NAME}.app',
                protocol: 'wss',
                clientPort: parseInt(env.VITE_LOCAL_PORT || '3000')
            }
        }
    }
});