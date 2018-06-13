# TERMINUS MYSQL

A terminus plugin that will locally run mysql commands on your site environment's databases.

# NOT ACTIVELY SUPPORTED
See [terminus pancakes](https://github.com/terminus-plugin-project/terminus-pancakes-plugin)

## Requirments

This plugin requires a local mysql-cli installation. On Ubuntu run:
```
sudo apt-get install mysql-cli
```
Otherwise, please check the interwebs for how to install mysql-cli.

## Installation

### Manually

Download this project and install it in your `$HOME/.terminus/plugins/` folder

## How to use

```
$ terminus mysql [site].[env]
```
This will open the mysql interactive shell.

```
$ terminus mysql [site].[env] -- "[SQL COMMAND]"
```
This will run the sql command and print the output.


# Help

```
$ terminus help mysql
```

Otherwise please refer to the [terminus docs](https://pantheon.io/docs/terminus/).


# Support
Please report any issues here. I'll get to them as I can. Feel free to fork and make your own.

Thanks and I hope you enjoy
