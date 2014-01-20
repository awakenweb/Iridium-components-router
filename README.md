Iridium Components: Router
=========================

[![Build Status](https://travis-ci.org/awakenweb/Iridium-components-router.png?branch=master)](https://travis-ci.org/awakenweb/Iridium-components-router)

Routing module for the Iridium Framework. Requires Iridium Components HTTP Stack to work.

This component allows you to define routes for your applicaiton, mapping requests to specific handlers, as long as they are valid PHP callbacks or an array containing a classname and a method name.
You can define routes using regular expressions or some predifined patterns for variable parameters such as id, blog post slugs or anything else you want.

This module will **not** execute the matching callback, it will just compare the request string to its list of routes and return an associative array containing:
- `array['callback']` : the callback
- `array['parameters']` : the request parameters, in the order they are found in the request string

If no routes are found

The class is unit tested using [Atoum](https://github.com/atoum/atoum).

Installation
------------
### Prerequisites

***Iridium requires at least PHP 5.4+ to work.***

Some of Iridium components may work on PHP5.3 but no support will be provided for this version.

### Using Composer
First, install [Composer](http://getcomposer.org/ "Composer").
Create a composer.json file at the root of your project. This file must at least contain :
```json
{
    "require": {
        "awakenweb/iridium-components-router": "dev-master"
        }
}
```
and then run
```bash
~$ composer install
```
---
Usage
-----

First, you have to create the router and pass an instance of HttpStack\Request to it.
```php
<?php
include('path/to/vendor/autoload.php');
use Iridium\Components\Router,
    Iridium\Components\HttpStack;
    
$router = new Router(new HttpStack\Request());
```

### Defining routes
You can now define a list of allowed routes that will be matched against the request string.
To do this, you can either define each route one by one, or give an array of routes.
For your routes, you have 4 predefined tokens you can use to match variable parameters:
- **:string** - equivalent to regex pattern `([a-zA-Z]+)`
- **:number** - equivalent to regex pattern `([0-9]+)`
- **:slug** - equivalent to regex pattern `([a-zA-Z0-9-_]+)`
- **:date** - equivalent to regex pattern `([0-9]{2}-[0-9]{2}-[0-9]{4})`
             
If you have variable parameters in your route, the callback **must** accept its parameters in the same ordre as the request for later execution.

 Begining and ending `/` in the route are optionnal, as they are automatically added during the matching process.
 However, if you explicitely use an ending `/` , it will become mandatory:
- if the route is `mySuperRoute`, `/mySuperRoute` and `/mySuperRoute/` will  both match
- if the route is `mySuperRoute`, only `/mySuperRoute/` will match 

**Get request parameters have no impact on the matching**.
For the route `article/:slug`, both `article/this-is-an-example` and `article/this-is-an-example?id=123456` will match the same.

```php
$router->defineRoute('/article/:date/', function($articledate){...});
$router->defineMultipleRoutes(array(
    'admin'=> array($adminController, 'indexAction'),
    'tags/:slug'=> function($categoryname){...},
    'place/:number/:string/:string/:number' => function($number, $street, $city, $zipcode) {...},
    '/whatYouWant/:number' => array('\namespace\of\some\Class', 'someMethod)
));
```

As you can see, callbacks can be either:
- an array containing the name of class and method to call. Instance of this class will be lazy loaded when required
- an array containing an object and a method name
- a closure
- a procedural function name

An array with a classname and a static method name are not allowed due to conflicts with the lazy loading process.

### Matching the routes and calling the callback
The next step is to actaully match the request against the list of defined routes, and execute the result or send a negative response

```php
$result = $router->match();
if(is_callable($result['callback'])) {
    // the callback has been found, process the request
    call_user_func_array($result['callback'], $result['parameters']);
}
else {
    // return a 404: Not Found error to the client
    ...
}
```
