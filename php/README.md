# Gimme Bar API Auth Tool for PHP

A simple example/demonstration CLI script for the Gimme Bar API v1 authentication flow

## Requirements

* PHP 5.3 or above
* [Resty.php](https://github.com/fictivekin/resty.php) (included)

## Usage

1. Register your application at [https://gimmebar.com/apps](https://gimmebar.com/apps)
1. Wait for us to approve your application's access
1. Get an email that we've approved your application
1. Copy the file `config.sample.json` to `config.json` (same directory)
1. Remove the comment at the top of the config.json file
1. Enter the application's *client id* and *secret* into the `config.json` file, and save the file
1. Run `./gb-authtool.php` from the command line (same directory)
1. Follow on-screen instructions
1. View the source code to see how it works

## Questions

* [API v1 docs](https://gimmebar.com/api/v1)
* [Mailing List/Discussion Group](https://groups.google.com/forum/#!forum/gimme-bar-api)
* [More support options](https://gimmebar.com/api/v1#developer-support)
