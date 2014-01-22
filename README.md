# EnhancedUrlGenerator

This is Symfony [Url Generator](http://api.symfony.com/2.4/Symfony/Component/Routing/Generator/UrlGenerator.html) class enhanced by two features:

* It preserves given set of query parameters in every generated url.
* It allows to generate random token for user transaction. This token is preserved
  in every url until the user leaves the site. It can be used for clicktracking
  or to track the users in simple access log.

It's boundled with [Silex](http://silex.sensiolabs.org/) Service provider. It works
exactly the same as the original
[Silex Url Generator Provider](http://silex.sensiolabs.org/doc/providers/url_generator.html).
To install Enhanced Url Generator add following
line into your composer.json

	mtrunkat/php-enhanced-urlgenerator: "*"

and call update/install command of composer. Then register provider to Silex:

```php
$app->register(new \Trunkat\EnhancedUrlGeneratorProvider(), array(
    'url_generator.preserve' => array('key1', 'keyb'),
));
```

You can use it the same as original Silex Url Generator:

```php
$app['url_generator']->generate('blog', array('someParam' => 'someValue'));
```

Resulting url will contain "key1" and "key2" as query parameters. To activate
the random token feature configure provider following way:

```php
$app->register(new \Trunkat\EnhancedUrlGeneratorProvider(), array(
    'url_generator.preserve' => array('key1', 'keyb'),
    'url_generator.token' => 'keyNameForToken',
    'url_generator.token_length' => 5,
));
```