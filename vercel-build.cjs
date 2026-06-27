const { existsSync, unlinkSync } = require('node:fs');
const { execFileSync } = require('node:child_process');
const { join } = require('node:path');

const env = {
  ...process.env,
  npm_config_cache: join(process.cwd(), '.npm'),
  COMPOSER_CACHE_DIR: join(process.cwd(), '.composer-cache'),
};

function run(command, args) {
  console.log(`==> ${[command, ...args].join(' ')}`);
  execFileSync(command, args, {
    stdio: 'inherit',
    shell: process.platform === 'win32',
    env,
  });
}

const npm = process.platform === 'win32' ? 'npm.cmd' : 'npm';

run(npm, [existsSync('package-lock.json') ? 'ci' : 'install']);
run(npm, ['run', 'build']);

console.log('==> Downloading Composer installer');
download('https://getcomposer.org/installer', 'composer-setup.php');
run('php', ['composer-setup.php', '--quiet']);

if (existsSync('composer-setup.php')) {
  unlinkSync('composer-setup.php');
}

run('php', [
  'composer.phar',
  'install',
  '--no-dev',
  '--optimize-autoloader',
  '--no-interaction',
]);

console.log('==> Build complete.');

function download(url, destination) {
  execFileSync(process.execPath, [
    '-e',
    `
      const { createWriteStream } = require('node:fs');
      const https = require('node:https');
      const { pipeline } = require('node:stream/promises');

      (async () => {
        const response = await new Promise((resolve, reject) => {
          https.get(${JSON.stringify(url)}, resolve).on('error', reject);
        });

        if (response.statusCode !== 200) {
          throw new Error('Download failed: ' + response.statusCode);
        }

        await pipeline(response, createWriteStream(${JSON.stringify(destination)}));
      })().catch((error) => {
        console.error(error);
        process.exit(1);
      });
    `,
  ], {
    stdio: 'inherit',
    shell: false,
    env,
  });
}
