# WP PHPCS

<p>
<a href="https://github.com/lipemat/wp-phpcs/releases">
<img src="https://img.shields.io/packagist/v/lipemat/wp-phpcs.svg?label=version" />
</a>
    <img alt="" src="https://img.shields.io/badge/wordpress->=4.8.0-green.svg">
    <img src="https://img.shields.io/packagist/php-v/lipemat/wp-phpcs.svg?color=brown" />
    <img alt="Packagist" src="https://img.shields.io/packagist/l/lipemat/wp-phpcs.svg">
</p>


PHP Codesniffer setup for a WordPress plugin.

## Installation

Use composer to install. Although this may be added directly to your plugins composer.json, it is recommended to install somewhere globally to reuse across projects. 

```bash
composer require lipemat/wp-phpcs
```

Copy the `phpcs-sample.xml` file to the root of your plugin and rename to `phpcs.xml`. Adjust the configuration as desired.

## Running

The vendor/bin folder includes the scripts to run on either Windows or Unix. You may either add that directory to your PATH or call it verbosely like so:
``` bash
{project dir}/vendor/bin/phpcs ./
```
OR
``` bash
{project dir}/vendor/bin/phpcbf ./
```

You may also create your own script somewhere on your PATH. Here is an example phpcs.bat for Windows. This assumes you created a folder named wp-phpcs in your root and ran composer require there. 
``` text
@echo off
C:\wp-phpcs\vendor\bin\phpcs %*
```

## Automating

Once you have scripts added to your path for phpcs and phpcbf, you can use the included `git-hooks/pre-commit` to run PHP lint and PHPCS automatically before making any commit. 

Copy the pre-commit file to your plugin's .git/hooks directory, and the rest is automatic.

## Included Sniffs
1. [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards#rulesets)
2. [WordPress VIP Coding Standards](https://github.com/Automattic/VIP-Coding-Standards)
   3. @notice Will be removed in version 3.
3. [PHPCompatibilityWP](https://github.com/PHPCompatibility/PHPCompatibilityWP)
4. [PHPCSExtra](https://github.com/PHPCSStandards/PHPCSExtra#sniffs)

## Lipe Sniffs

This package ships with some `Lipe` namespaced sniffs.
1. `<rule ref="Lipe" />` for all our default standards.
2. `<rule ref="Lipe.JS" />` for our JavaScript security sniffs, which support dompurify.

## Other Notes

The `phpcs-sample.xml` has many things excluded. This is partially because some things don't really fit in with WordPress standards. You can remove any of `<exclude>` items to make more strict. Remove them all if you really want to make your code strict.
