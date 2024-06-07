composer require nedrug/sitemapgenerator:"@dev"

пример:
```php
<?php
namespace Nedrug\Sitemapgenerator;
require 'vendor/autoload.php';
$pages = [
    [
        'loc' => 'https://example.com/',
        'lastmod' => '2023-10-26',
        'priority' => '1.0',
        'changefreq' => 'daily',
    ],
    [
        'loc' => 'https://example.ввв/about',
        'lastmod' => '2023-10-25',
        'priority' => '0.8',
        'changefreq' => 'weekly',
    ],
];
$sitemap = new SitemapGenerator($pages, 'json', '/var/www/site.ru/upload/sitemap.json');
```
