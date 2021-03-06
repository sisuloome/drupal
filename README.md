# Sisuloome

## Installation

* Make sure that server meets the
  [requirements](https://www.drupal.org/docs/8/system-requirements) + make sure
  that `composer` is installed in the system (both global and local
  installations should work just fine)
* Clone the repository and run `composer install` (or `php composer.phar
  install` in case of non-global installation)
* Only the `web/` catalog should be exposed to the `Apache` web server
* Finish the installation with either using `drush` or a web-based installer
  * NB! Please make sure that you choose the **Estonian/Eesti** as a language as
  that would preinstall the translations
  * One might be required to create the `settings.php` file under
  `web/sites/default` by using `default.settings.php` as an example
* Enable all the required modules and configure them
  * Enabling modules with `drush` could be the easiest and quickest way. Below
  is the list of commands that should do the trick. NB! Please note that
  configurations would have to be made manually (at least for now)

  ```
  drush en h5peditor
  drush en openid_connect_harid
  drush en google_tag
  drush en h5p_google_tag
  drush en bootstrap_site_alert
  drush en h5p_math_input
  drush en h5p_x_frame_options
  drush en mailsystem
  drush en mimemail
  drush en h5p_challenge
  drush en override_node_options
  drush en h5p_analytics
  drush en sisuloome
  drush then bootstrap
  drush config-set system.theme default bootstrap
  drush cr
  ```
* Configure the system as needed
  * Please note that `h5p_challenge` would require `mailsystem` and `mimemail`
  modules to be configure in order to send results as an attachment to an email
  notification when challenge ends. Please see
  [this](https://git.drupalcode.org/project/h5p_challenge#installation) resource
  for detailed guide and explanations
  * Please consider configuring the Cron jobs according to
  [this](https://www.drupal.org/node/23714) page. Hourly job should be enough
  * H5P Analytics module would need an LRS to be present and configured. It also
  required Cron job to be present in order to send data to the xAPI endpoint.

## Updating

### Development

Please follow
[this](https://www.drupal.org/docs/8/update/update-core-via-composer) guide to
apply updates locally. Please make sure to remember that
`drupal-composer/drupal-project` is being used and some of the commands are
specific to that kind of installation. Commit the changes as you are finished.

### Production

Run `git pull` to get the latest code and apply the updates, use the same
[guide](https://www.drupal.org/docs/8/update/update-core-via-composer), though
it should only require to run the `composer install` to update the code. Below
is a line-by-line guide:

* Make sure to create a backup of both the database and files
  - `drush sql-dump` should help with the database part and the rest could be
  done with something like `zip -r backup.zip <DRUPAL-BASE-DIR>`
* Activate maintenance mode and clear cache
  - `drush sset system.maintenance_mode 1`
  - `drush cr`
* Run `composer install` to update the code
* Apply database updates and clear the cache again
  - `drush updatedb`
  - `drush cr`
* Deactivate maintenance mode and clear caches
  - `drush sset system.maintenance_mode 0`
  - `drush cr`

**NB! Please sure to check if your instance is working as expected after
applying database updates and finishing the process!**

## Development

* Make sure that all the modules are added by running `composer require MODULE`
  command
* Make sure to update the command for enabling modules above (at least until
  some better way is found)
* Any own custom module should be added in the same manner. Please see the
  current `composer.json` file for a working example

## Composer template for Drupal projects (original file contents)

[![Build Status](https://travis-ci.org/drupal-composer/drupal-project.svg?branch=8.x)](https://travis-ci.org/drupal-composer/drupal-project)

This project template provides a starter kit for managing your site
dependencies with [Composer](https://getcomposer.org/).

If you want to know how to use it as replacement for
[Drush Make](https://github.com/drush-ops/drush/blob/8.x/docs/make.md) visit
the [Documentation on drupal.org](https://www.drupal.org/node/2471553).

### Usage

First you need to [install composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx).

> Note: The instructions below refer to the [global composer installation](https://getcomposer.org/doc/00-intro.md#globally).
You might need to replace `composer` with `php composer.phar` (or similar)
for your setup.

After that you can create the project:

```
composer create-project drupal-composer/drupal-project:8.x-dev some-dir --no-interaction
```

With `composer require ...` you can download new dependencies to your
installation.

```
cd some-dir
composer require drupal/devel:~1.0
```

The `composer create-project` command passes ownership of all files to the
project that is created. You should create a new git repository, and commit
all files not excluded by the .gitignore file.

### What does the template do?

When installing the given `composer.json` some tasks are taken care of:

* Drupal will be installed in the `web`-directory.
* Autoloader is implemented to use the generated composer autoloader in `vendor/autoload.php`,
  instead of the one provided by Drupal (`web/vendor/autoload.php`).
* Modules (packages of type `drupal-module`) will be placed in `web/modules/contrib/`
* Theme (packages of type `drupal-theme`) will be placed in `web/themes/contrib/`
* Profiles (packages of type `drupal-profile`) will be placed in `web/profiles/contrib/`
* Creates default writable versions of `settings.php` and `services.yml`.
* Creates `web/sites/default/files`-directory.
* Latest version of drush is installed locally for use at `vendor/bin/drush`.
* Latest version of DrupalConsole is installed locally for use at `vendor/bin/drupal`.
* Creates environment variables based on your .env file. See [.env.example](.env.example).

### Updating Drupal Core

This project will attempt to keep all of your Drupal Core files up-to-date; the
project [drupal-composer/drupal-scaffold](https://github.com/drupal-composer/drupal-scaffold)
is used to ensure that your scaffold files are updated every time drupal/core is
updated. If you customize any of the "scaffolding" files (commonly .htaccess),
you may need to merge conflicts if any of your modified files are updated in a
new release of Drupal core.

Follow the steps below to update your core files.

1. Run `composer update drupal/core webflo/drupal-core-require-dev "symfony/*" --with-dependencies` to update Drupal Core and its dependencies.
1. Run `git diff` to determine if any of the scaffolding files have changed.
   Review the files for any changes and restore any customizations to
  `.htaccess` or `robots.txt`.
1. Commit everything all together in a single commit, so `web` will remain in
   sync with the `core` when checking out branches or running `git bisect`.
1. In the event that there are non-trivial conflicts in step 2, you may wish
   to perform these steps on a branch, and use `git merge` to combine the
   updated core files with your customized files. This facilitates the use
   of a [three-way merge tool such as kdiff3](http://www.gitshah.com/2010/12/how-to-setup-kdiff-as-diff-tool-for-git.html). This setup is not necessary if your changes are simple;
   keeping all of your modifications at the beginning or end of the file is a
   good strategy to keep merges easy.

### Generate composer.json from existing project

With using [the "Composer Generate" drush extension](https://www.drupal.org/project/composer_generate)
you can now generate a basic `composer.json` file from an existing project. Note
that the generated `composer.json` might differ from this project's file.


### FAQ

#### Should I commit the contrib modules I download?

Composer recommends **no**. They provide [argumentation against but also
workrounds if a project decides to do it anyway](https://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md).

#### Should I commit the scaffolding files?

The [drupal-scaffold](https://github.com/drupal-composer/drupal-scaffold) plugin can download the scaffold files (like
index.php, update.php, …) to the web/ directory of your project. If you have not customized those files you could choose
to not check them into your version control system (e.g. git). If that is the case for your project it might be
convenient to automatically run the drupal-scaffold plugin after every install or update of your project. You can
achieve that by registering `@composer drupal:scaffold` as post-install and post-update command in your composer.json:

```json
"scripts": {
    "post-install-cmd": [
        "@composer drupal:scaffold",
        "..."
    ],
    "post-update-cmd": [
        "@composer drupal:scaffold",
        "..."
    ]
},
```
#### How can I apply patches to downloaded modules?

If you need to apply patches (depending on the project being modified, a pull
request is often a better solution), you can do so with the
[composer-patches](https://github.com/cweagans/composer-patches) plugin.

To add a patch to drupal module foobar insert the patches section in the extra
section of composer.json:
```json
"extra": {
    "patches": {
        "drupal/foobar": {
            "Patch description": "URL or local path to patch"
        }
    }
}
```
#### How do I switch from packagist.drupal-composer.org to packages.drupal.org?

Follow the instructions in the [documentation on drupal.org](https://www.drupal.org/docs/develop/using-composer/using-packagesdrupalorg).

#### How do I specify a PHP version ?

This project supports PHP 5.6 as minimum version (see [Drupal 8 PHP requirements](https://www.drupal.org/docs/8/system-requirements/drupal-8-php-requirements)), however it's possible that a `composer update` will upgrade some package that will then require PHP 7+.

To prevent this you can add this code to specify the PHP version you want to use in the `config` section of `composer.json`:
```json
"config": {
    "sort-packages": true,
    "platform": {
        "php": "5.6.40"
    }
},
```
