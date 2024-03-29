# Vultr API v2 PHP class wrapper


PHP class built for the [Vultr API v2](https://www.vultr.com/api). Do all server actions along with Vultr account services like Block storage, backups, DNS, network, snapshots, Custom ISO's, saved ip addresses and much more.


### Usage

Install with:
```
composer require corbpie/vultr-api-v2
```

Example usage:
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Corbpie\VultrAPIv2\VultrAPI;

$vultr = new VultrAPI();

echo $vultr->listServers();//Lists all server instances for account
```

### Current version

2.3 Feb 21 2024

### Requires
* PHP 8.2

Vultr API key obtained form your Vultr account menu.

Add your Vultr API key line 8: ```src/class.php```

### Examples

List all instances:

```php
$vultr->listServers();
```

Create a server
```php
$vultr->serverCreateDC(19);//Sydney Australia location
$vultr->serverCreatePlan(202);//(2048 MB RAM,55 GB SSD,2.00 TB BW)
$vultr->serverCreateType('ISO', 146817);//Deploy with my custom ISO id:146817
$vultr->serverCreateLabel('Created with API');//label instance as "Created with API"
echo $vultr->serverCreate();//Creates instance/server with parameters set above (returns subid)
```

See ```example.php``` for more.


### TODO

* A proper readme
