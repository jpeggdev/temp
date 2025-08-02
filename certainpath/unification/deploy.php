<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

require 'recipe/symfony.php';

// Config

// Determine branch
$BRANCH = getenv('BRANCH');
if (!$BRANCH) {
    throw new GracefulShutdownException('Branch not set');
}
// Project name
set('application', 'Unification App');

// Project repository
set('repository', 'git@github.com:serviceline/unification.git');
set('branch', $BRANCH);

// Shared files/dirs between deploys
set('shared_dirs', [
    'var/data',
    'var/log',
    'var/sessions',
    'var/tailwind',
    'var/tmp',
]);

set('shared_files', [
    '.env.local',
]);

set('log_files', 'var/log/*.log');

set('writable_dirs', [
    'var',
    'var/cache',
    'var/data',
    'var/log',
    'var/sessions',
    'var/tailwind',
    'var/tmp',
]);

set('migrations_config', '');

set('doctrine_schema_validate_config', '');

set('console_options', function () {
    return '--no-interaction';
});

// Other configuration
set('keep_releases', 15);

// Allow Dev Packages In Non-Production Hosts
$nonProdComposerOptions = str_replace(
    ' --no-dev ',
    ' ',
    get('composer_options')
);

// Hosts
host('nexus.mycertainpath.com')
    ->setLabels(['environment' => 'production'])
    ->set('remote_user', 'appadmin')
    ->set('deploy_path', '/srv/unification')
    ->set('http_user', 'www-data')
    ->set('environment', 'prod')
    ->set('php-root', dirname('/usr/bin/php', 2))
    ->set('bin/php', '/usr/bin/php')
    ->set('bin/composer', '{{bin/php}} /usr/local/bin/composer')
    ->set('php-fpm', 'php*-fpm.service')
    // [Optional] Allocate tty for git clone. Default value is false.
    ->set('git_tty', true)
    // SSH settings
    ->set('ssh_type', 'native')
;

host('nexus-qa.mycertainpath.com')
    ->setLabels(['environment' => 'qa'])
    ->set('remote_user', 'appadmin')
    ->set('deploy_path', '/srv/unification')
    ->set('http_user', 'www-data')
    ->set('environment', 'prod')
    ->set('php-root', dirname('/usr/bin/php', 2))
    ->set('bin/php', '/usr/bin/php')
    ->set('bin/composer', '{{bin/php}} /usr/local/bin/composer')
    ->set('php-fpm', 'php*-fpm.service')
    // [Optional] Allocate tty for git clone. Default value is false.
    ->set('git_tty', true)
    // SSH settings
    ->set('ssh_type', 'native')
;

host('nexus-labs.mycertainpath.com')
    ->setLabels(['environment' => 'qa'])
    ->set('remote_user', 'appadmin')
    ->set('deploy_path', '/srv/unification')
    ->set('http_user', 'www-data')
    ->set('environment', 'prod')
    ->set('php-root', dirname('/usr/bin/php', 2))
    ->set('bin/php', '/usr/bin/php')
    ->set('bin/composer', '{{bin/php}} /usr/local/bin/composer')
    ->set('php-fpm', 'php*-fpm.service')
    // [Optional] Allocate tty for git clone. Default value is false.
    ->set('git_tty', true)
    // SSH settings
    ->set('ssh_type', 'native')
;

host('beefy')
    ->setLabels(['environment' => 'production'])
    ->set('remote_user', 'appadmin')
    ->set('deploy_path', '/srv/unification')
    ->set('http_user', 'www-data')
    ->set('environment', 'prod')
    ->set('php-root', dirname('/usr/bin/php', 2))
    ->set('bin/php', '/usr/bin/php')
    ->set('bin/composer', '{{bin/php}} /usr/local/bin/composer')
    ->set('php-fpm', 'php*-fpm.service')
    // [Optional] Allocate tty for git clone. Default value is false.
    ->set('git_tty', true)
    // SSH settings
    ->set('ssh_type', 'native')
;

localhost('localhost')
    ->set('remote_user', get_current_user())
    ->set('deploy_path', __DIR__ . '/../uni-deployments')
    ->set('http_user', get_current_user())
    ->set('environment', 'dev')
    ->set('php-root', dirname(PHP_BINARY, 2))
    ->set('bin/php', PHP_BINARY)
    ->set('bin/composer', '{{bin/php}} /usr/local/bin/composer')
    ->set('composer_options', $nonProdComposerOptions)
    ->set('php-fpm', 'php-fpm')
;

// Initialization Tasks
desc('[Unification] Initialize Deployment');
task('unification:initialize', function () {
});

// Build Tasks
desc('[Unification] Publish Tailwind');
task('unification:publish-tailwind', function () {
    $command = '{{bin/php}} {{release_or_current_path}}/bin/console tailwind:build \
    --minify \
    --no-interaction \
    --quiet \
    --env={{environment}}';
    run($command, ['timeout' => null]);
});

desc('[Unification] Compile Asset Map');
task('unification:compile-asset-map', function () {
    $command = '{{bin/php}} {{release_or_current_path}}/bin/console asset-map:compile \
    --no-interaction \
    --quiet \
    --env={{environment}}';
    run($command, ['timeout' => null]);
});

// Deployment Tasks
desc('[Unification] Migrate Database');
task('unification:migrate', function () {
    $command = '{{bin/php}} {{release_or_current_path}}/bin/console doctrine:migrations:migrate \
    --no-interaction \
    --quiet \
    --env={{environment}}';
    run($command, ['timeout' => null]);
});

desc('[Unification] Warmup Cache');
task('unification:warmup', function () {
    run('{{bin/php}} {{release_or_current_path}}/bin/console cache:warmup --env={{environment}}');
});

desc('[PHP-FPM] Restart');
task('php-fpm:restart', function () {
    run('sudo /usr/bin/systemctl restart {{php-fpm}}');
})->select('environment=production|qa');

desc('[Supervisor] Stop Workers');
task('supervisor:stop-workers', function () {
    run('{{bin/php}} {{deploy_path}}/current/bin/console messenger:stop-workers');
})->select('environment=production|qa');

$tasks = [
    // Initialization Tasks
    'unification:initialize' => 'unification:initialize',

    // Preparation Tasks
    'deploy:prepare' => 'deploy:prepare',
    'deploy:vendors' => 'deploy:vendors',

    // Build Tasks
    'unification:publish-tailwind' => 'unification:publish-tailwind',
    'unification:compile-asset-map' => 'unification:compile-asset-map',

    // Deployment Tasks
    'unification:migrate' => 'unification:migrate',
    'unification:warmup' => 'unification:warmup',

    'supervisor:stop-workers' => 'supervisor:stop-workers',

    // Cleanup Tasks
    'deploy:symlink' => 'deploy:symlink',
    'deploy:unlock' => 'deploy:unlock',
    'deploy:cleanup' => 'deploy:cleanup',
    'deploy:success' => 'deploy:success',

    // Server Tasks
    'php-fpm:restart' => 'php-fpm:restart',
];

desc('[Unification] Deploy');
task('deploy', $tasks);

after('deploy:failed', 'deploy:unlock');

// Define the task to write the branch info to a file
desc('Write deployed branch info to a file');
task('deploy:write_branch_info', function () {
    $branch = get('branch');
    $filePath = '{{release_or_current_path}}/deployed_branch.txt';
    run("echo 'Deployed branch: $branch' > $filePath");
});

// Add the task to the deployment sequence
after('deploy:symlink', 'deploy:write_branch_info');
