# Terminus Environment Extend Plugin

A terminus plugin that will locally run mysql commands on your site environment's databases. This plugin was built in bash on Windows and should work in any linux environment that has mysql-cli and sftp.

# Other Places to get this functionality:
Unfortunately, others beat me to these ideas so I probably wont owrk too hard on supporting this particular plugin.

For mysql connections See [terminus pancakes](https://github.com/terminus-plugin-project/terminus-pancakes-plugin)

For the sftp connection See [terminus filer](https://github.com/terminus-plugin-project/terminus-filer-plugin)

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

To open the mysql-cli connection or to pass a SQL command use the following:

```
$ terminus env:mysql [site].[env]
```
This will open the mysql interactive shell.

```
$ terminus env:mysql [site].[env] -- "[SQL COMMAND]"
```
This will run the sql command and print the output.

To open the sftp connection use the following:
```
terminus env:sftp [site].[env]
```


# Help

```
$ terminus help mysql
```

Otherwise please refer to the [terminus docs](https://pantheon.io/docs/terminus/).


# Support
Please report any issues here. I'll get to them as I can. Feel free to fork and make your own.

Thanks and I hope you enjoy
