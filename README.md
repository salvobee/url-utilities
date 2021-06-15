# URL Utilities
A simple set of utilities to abstract in a more Object-Oriented fashion existing php url functions. 

## Usage
Build an instance of the parser using a valid URL as constructor's argument.

```php
use SalvoBee\UrlUtilities\Parser;

$url = new Parser('https://www.domain.com');
```

### Query string manipulation
You can manipulate given url's query string parameters and getting a new url as return:

```php
use SalvoBee\UrlUtilities\Parser;

$url = new Parser('https://www.domain.com?arg1=value1&arg2=value2');

$url->manipulateParameter('arg1','new_value');
// returns 'https://www.domain.com?arg1=new_value&arg2=value2'

$url->addParameter('arg3', 'value3');
// returns 'https://www.domain.com?arg1=value1&arg2=value2&arg3=value3'

$url->removeParameter('arg1');
// returns 'https://www.domain.com?arg2=value2'
```

### Multiple Values Fields
⚠️Support to manipulate fields that holds multiple values is still in development and doesn't work as expected, feel free to submit a PR ⚠️
```php
use SalvoBee\UrlUtilities\Parser;

$url = new Parser('https://www.domain.com?arg[]=value1&arg[]=value2');

$url->manipulateMultipleValue('arg','value1','new_value1');
// should return 'https://www.domain.com?arg[]=new_value1&arg[]=value2'

$url->addMultipleValue('arg', 'value3');
// should return 'https://www.domain.com?arg[]=value1&arg[]=value2&arg[]=value3'

$url->removeMultipleValue('arg','value2');
// should return 'https://www.domain.com?arg[]=value1'
```

### Standard Parser
URL components are parsed using standard php `parse_url()`, so if you need, you can access them using getters:

```php
use SalvoBee\UrlUtilities\Parser;

$url = new Parser('https://john_doe:secret@www.domain.com:9999/path/to/resource?foo=bar&bar=foo#anchor');

$url->getScheme();         // returns "https"
$url->getHost();           // returns "www.domain.com"
$url->getPort();           // returns "9999"
$url->getUsername();       // returns "john_doe"
$url->getPassword();       // returns "secret"
$url->getPath();           // returns "/path/to/resource"
$url->getQueryString();    // returns "foo=bar&bar=foo"
$url->getFragment();       // returns "anchor"
```
