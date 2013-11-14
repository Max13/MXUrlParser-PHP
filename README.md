MXUrlParser-PHP
====================

Description
-----------
**MXUrlParser** is capable of parsing a complete URL and extract some parts of it like the URL parts (using php [`parse_url()`](http://php.net/parse_url) function) and some domain name parts (using [Mozilla public suffix list](publicsuffix.org)).

Available parts are (Assuming URL is `https://dev.api.example.co.uk/1/2/3?key=val#anchor`):

- `scheme`: `https`
- `host`: `dev.api.example.co.uk`
- `path`: `/1/2/3`
- `query`: `key=val`
- `fragment`: `anchor`
- `subdomain`: `dev`
- `domain`: `api.example`
- `tld`: `co.uk`

Required
--------
- PHP >= `5.3`

How to download
---------------
There are several ways to download **MxUrlParser-PHP**:

- Install with composer (`"max13/url-parser": "dev-master"`)
- Clone the [github repository](https://github.com/Max13/MXUrlParser-PHP) with `git clone <repo> [<dest>]`
- Download the zip file on [github](https://github.com/Max13/MXUrlParser-PHP) directly
- Try to find another one by yourself :/

Then place it where you want (readable location, in order to load it).

How to use
----------
Let's say your URL is: `dev.api.example.co.uk/1/2/3?key=val#anchor`

You can parse it with the `MX\UrlParser\UrlParser` class:

```
<?php

use MX\UrlParser\UrlParser;

$p_url = new UrlParser('dev.api.example.co.uk/1/2/3?key=val#anchor');

/*
$p_url->scheme; // === null
$p_url->host; // == 'dev.api.example.co.uk'
$p_url->subdomain; // == 'dev'
$p_url->tld; // == 'co.uk'
*/
?>
```

That's it, as simple as this...!