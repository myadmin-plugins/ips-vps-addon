# MyAdmin VPS IP Address Addon

Additional IP address addon module for VPS services in the MyAdmin hosting control panel. This package provides IP allocation, assignment, and lifecycle management as a purchasable addon for virtual private servers.

[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-ips-vps-addon/version)](https://packagist.org/packages/detain/myadmin-ips-vps-addon)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-ips-vps-addon/downloads)](https://packagist.org/packages/detain/myadmin-ips-vps-addon)
[![License](https://poser.pugx.org/detain/myadmin-ips-vps-addon/license)](https://packagist.org/packages/detain/myadmin-ips-vps-addon)
[![Tests](https://github.com/detain/myadmin-ips-vps-addon/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-ips-vps-addon/actions/workflows/tests.yml)

## Features

- Sell additional IP addresses as a VPS addon
- Automatic IP allocation from available server pool
- IP enable/disable lifecycle management with invoice tracking
- Admin override for maximum IP limits
- Configurable pricing and quantity limits via settings panel
- Symfony EventDispatcher integration for hook-based architecture

## Installation

Install via Composer:

```sh
composer require detain/myadmin-ips-vps-addon
```

## Configuration

The addon exposes two settings through the MyAdmin settings panel:

- **VPS Additional IP Cost** -- per-IP pricing for the addon
- **Max Addon IP Addresses** -- maximum number of additional IPs per VPS

## Running Tests

```sh
composer install
vendor/bin/phpunit
```

## License

Licensed under the LGPL-2.1-only license. See [LICENSE](https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html) for details.
