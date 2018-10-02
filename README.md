# WP PHPCS
PHP Codesniffer setup for a WordPress Plugin

## Installation

Use composer to install. Although this may be added directly to your plugins composer.json, it is recommended to install somewhere globally to reuse across projects. 

```bash
composer require lipemat/wp-phpcs
```

Copy the phpcs-sample.xml file to the root of your plugin and rename to phpcs.xml. Adjust the configuration as desired.

## Setup

Running this command will tell phpcs where to find the WordPress and PHPCompatibility standards. 

```bash
phpcs --config-set installed_paths '../../wimg/php-compatibility/,../../wp-coding-standards/wpcs/,../../automattic/vipwpcs/'
```
**Composer will likely do this for you when you run composer install due to the included `dealerdirect/phpcodesniffer-composer-installer` library.**

## Running

The vendor/bin folder includes the scripts to run on either Windows or Unix. Open a terminal and cd to your plugin directory then run

``` bash
{project dir}/vendor/bin/phpcs ./
```
OR
``` bash
{project dir}/vendor/bin/phpcbf ./
```

You may also create your own script somewhere on your PATH. Here is an example phpcs.cmd for Windows. This assumes you created a folder name wp-phpcs in your root and ran composer require there. 
``` text
@echo off
C:\wp-phpcs\vendor\bin\phpcs %*
```

## Automating

Once you have scripts added to your path for phpcs and phpcbf, you can use the included git-hooks/pre-commit to run PHP lint and PHPCS automatically before making any commit. 

Copy the pre-commit file to your plugins .git/hooks directory and the rest is automatic.


## Other Notes

The sample phpcs.xml has many things excluded. This is partially because some things don't really fit in with WordPress standards, and partially because I get lazy sometimes with comments. You can remove any of <exclude> items to make more strict. Remove them all if you really really want to make your code strict. 

There is one in particular worth mentioning. This one is an important security item, but it throws a lot of false positives unless you always late escape. I recommend removing it unless you really are conscious of escaping. Even if you don't remove it, you will get warnings in PHPStorm if you enable CodeSniffer.

``` xml
<rule ref="WordPress.XSS.EscapeOutput.OutputNotEscaped">
      <type>warning</type>
</rule>
```
 
## Enabling Code Sniffer in PHPStorm

```
1. Editor -> Inspections -> PHP Code Sniffer Validation
2. Coding Standard = {plugin dir}/phpcs.xml
```
You will probably also want to use WordPress code styles
```
1. Editor -> Code Style -> PHP
    2. Can most likely use the Default one
    3. Use 'Set from' and select WordPress. It won't be exact but good starting point.
```
