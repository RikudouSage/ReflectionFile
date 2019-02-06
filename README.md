# ReflectionFile

[![Build Status](https://img.shields.io/travis/com/RikudouSage/ReflectionFile/master.svg)](https://travis-ci.com/RikudouSage/ReflectionFile)
[![Coverage Status](https://img.shields.io/coveralls/github/RikudouSage/ReflectionFile/master.svg)](https://coveralls.io/github/RikudouSage/ReflectionFile?branch=master)
[![Download](https://img.shields.io/packagist/dt/rikudou/reflection-file.svg)](https://packagist.org/packages/rikudou/reflection-file)

Allows you to get info about PHP file.

## Usage

```php
<?php

use Rikudou\ReflectionFile;
use Rikudou\Exception\ReflectionException;

try {
    $reflection = new ReflectionFile("/path/to/file.php");    
} catch (ReflectionException $exception) {
    var_dump("The file does not exist!");
}

// true if the file contains a class
$reflection->containsClass();
// true if the file contains any namespace
$reflection->containsNamespace();
// true if the file contains any inline html, e.g. content that is not php
$reflection->containsInlineHtml();
// true if the file contains any php code
$reflection->containsPhpCode();
// true if the file contains echo or print statements
$reflection->printsOutput();

try {
    // returns the namespace as a string, throws exception if the
    // file does not contain a namespace
    $reflection->getNamespace();    
} catch (ReflectionException $exception) {
    var_dump("The class does not contain a namespace!");
}
// the previous example can be rewritten as follows
if($reflection->containsNamespace()) {
    $reflection->getNamespace();
}

try {
    // returns instance of \ReflectionClass if the file contains a class
    // otherwise throws an exception
    $reflection->getClass();
} catch (ReflectionException $exception) {
    var_dump("The class does not contain a class!");
}
```

## Limitations

The `ReflectionFile` cannot parse multiple classes or namespaces.
If the file contains more than one class (or namespace) the latest one will be returned.
