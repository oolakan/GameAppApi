<?php
namespace Deployer;
//
require 'recipe/laravel.php';
//set('default_stage', 'development');
//
//// Project name
//set('application', 'Lottospot');
//set('repository', 'git@github.com:oolakan/GameAppApi.git');
//
//// Project repository
////set('repository', 'git@bitbucket.org:flextaapp/flexta-project.git');
//
//// [Optional] Allocate tty for git clone. Default value is false.
//set('git_tty', false);
//
//// Shared files/dirs between deploys
//add('shared_files', []);
//add('shared_dirs', []);
//
//// Writable dirs by web server
//add('writable_dirs', []);
//
////set('default_stage', 'development');
//
//// Hosts
//host('134.209.248.175')
//    ->user('root')
//    ->identityFile('~/.ssh/deployerkey')
//    ->set('deploy_path', '/home/GameAppApi')
//// Tasks
//

//
//
//task('build', function () {
//    run('cd {{release_path}} && build');
//});
//
//// [Optional] if deploy fails automatically unlock.
//after('deploy:failed', 'deploy:unlock');
//// Migrate database before symlink new release.
//
//before('deploy:symlink', 'artisan:migrate');


set('application', 'lottospot');

// Project repository
set('repository', 'git@github.com:oolakan/GameAppApi.git');


// Hosts

host('134.209.248.175')
    ->user('deployer')
    ->identityFile('~/.ssh/lottospotkey')
    ->set('deploy_path', '/home/GameAppApi');

task('deploy', [
    'deploy:prepare',
    'deploy:update_code',
    'deploy:vendors',
    'deploy:writable',
    'artisan:cache:clear',
    'artisan:optimize',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);