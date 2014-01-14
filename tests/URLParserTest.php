<?php

use MX\UrlParser;

ini_set('error_reporting', 2147483647);
ini_set('display_errors', '1');

class URLParserTest extends PHPUnit_Framework_TestCase
{
    public function testNonExistantTld()
    {
        $url = 'http://www.example.local/';

        $parsed = new UrlParser($url);
        $this->assertTrue($parsed->isValid());
        $this->assertEquals($parsed->scheme, 'http');
        $this->assertEquals($parsed->host, 'www.example.local');
        $this->assertEquals($parsed->subdomain, 'www');
        $this->assertEquals($parsed->domain, 'example');
        $this->assertEquals($parsed->tld, 'local');
    }

    public function testWellFormedCompleteUrl()
    {
        $url = 'https://dev.api.example.co.uk/1/2/3?key=val#anchor';

        $parsed = new UrlParser($url);
        $this->assertTrue($parsed->isValid());
        $this->assertEquals($parsed->scheme, 'https');
        $this->assertEquals($parsed->host, 'dev.api.example.co.uk');
        $this->assertEquals($parsed->subdomain, 'dev');
        $this->assertEquals($parsed->domain, 'api.example');
        $this->assertEquals($parsed->tld, 'co.uk');
        $this->assertEquals($parsed->path, '/1/2/3');
        $this->assertEquals($parsed->query, 'key=val');
        $this->assertEquals($parsed->fragment, 'anchor');
    }

    public function testMalformedSchemeUrl()
    {
        $url = '//dev.api.example.co.uk/1/2/3?key=val#anchor';

        $parsed = new UrlParser($url);
        $this->assertTrue($parsed->isValid());
        $this->assertNull($parsed->scheme);
        $this->assertEquals($parsed->host, 'dev.api.example.co.uk');
        $this->assertEquals($parsed->subdomain, 'dev');
        $this->assertEquals($parsed->domain, 'api.example');
        $this->assertEquals($parsed->tld, 'co.uk');
        $this->assertEquals($parsed->path, '/1/2/3');
        $this->assertEquals($parsed->query, 'key=val');
        $this->assertEquals($parsed->fragment, 'anchor');
    }

    public function testToString()
    {
        $url = '//dev.api.example.co.uk/1/2/3?key=val#anchor';

        $parsed = new UrlParser($url);
        $this->assertEquals(substr($url, 2), $parsed->toString());
    }
}
