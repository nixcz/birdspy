# BirdSpy - Web Application for Querying BIRD Servers

## Security

It's not recommended to run this application with public access. Ideally, run it on private network.

## Installation

This is a basic [Symfony](https://symfony.com) PHP application and the requirements are:

* PHP >= 7.3
* BCMath PHP Extension
* Ctype PHP Extension
* Iconv PHP Extension
* JSON PHP Extension
* Mbstring PHP Extension
* [Redis](https://redis.io) + PHP Extension

For Apache or Nginx, [setup a virtual host](https://symfony.com/doc/current/setup/web_server_configuration.html) to point to the `public/` directory of the project.

Make sure that `var/` directory is writable by `www-data` user or the appropriate web server user.

Create `.env.local` with custom variables (how to and which is described in `.env`).

Configuration files are in `config/packages/` directory (a specially: `app.yaml`, `bird.yaml`, `cache.yaml`). They could be rewritten with environment equivalents in `dev/`, `prod/`, `test/` subdirectories.

You need to give the `www-data` user permission to run the `birdc` script. Add to `/etc/sudoers`:

```
www-data        ALL=(ALL)       NOPASSWD: /project_path/bin/birdc
```

The best way to install dependencies is using [Composer](https://getcomposer.org) and [Yarn](https://yarnpkg.com) from `project_path`:

```sh
$ composer install
$ yarn install
```

And then build assets:

```sh
$ yarn build
```

## Commands

For Commands List run from `project_path`:

```sh
$ php bin/console
```

Import invalid routes for all servers:

```sh
$ php bin/console app:import-invalid-routes
```

Or specifically for one server:

```sh
$ php bin/console app:import-server-invalid-routes nix-rs-1
```

Import filtered routes for all servers:

```sh
$ php bin/console app:import-filtered-routes
```

Or specifically for one server:

```sh
$ php bin/console app:import-server-filtered-routes nix-rs-1
```

## CRON

For automation commands use:

```sh
$ crontab -u www-data -e
```

And add similar:

```
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
MAILTO=""

# NIX/BirdSpy Invalid Routes
*/5 * * * * /usr/bin/php /project_path/bin/console app:import-server-invalid-routes nix-rs-1 -q

# NIX/BirdSpy Filtered Routes
*/7 * * * * /usr/bin/php /project_path/bin/console app:import-server-filtered-routes nix-rs-1 -q
```

## BIRD Configuration

It's recommended, but not necessary to change time format to match BirdSpy looking glass:
```
timeformat base         iso long;
timeformat log          iso long;
timeformat protocol     iso long;
timeformat route        iso long;
```

## License

This application is open-sourced software licensed under the MIT license - see [the license file](LICENSE.md).
