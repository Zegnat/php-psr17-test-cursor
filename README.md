# Test Stream Factory Cursor

Testing [psr/http-factory-implementation (PSR-17) providers][providers] to see where they put the file cursor after creating a PSR-7 stream.

## Methodology

Packages were included by author discretion, feel free to open PRs for others. Sorted alphabetically:

* [berlioz/http-message][]
* [http-interop/http-factory-diactoros][]
* [http-interop/http-factory-guzzle][]
* [http-interop/http-factory-slim][]
* [nyholm/psr7][]
* [tuupola/http-factory][]
* [zendframework/zend-diactoros][]

Because [tuupola/http-factory][] will default to Diactoros if multiple packages are installed, a copy of its code it used instead of the actual package.

For each package a Stream is created in the following ways and the output of `tell()` written out:

1. `createStream()` with the string “`Hello!`”.
2. `createStreamFromFile()` with a file containing the string “`Hello!`”.
3. `createStreamFromResource()` with a resource pointing at `php://temp` with the string “`Hello!`” written to it.

The third test is done three times. Once with nothing done after the `fwrite()`, once after a `rewind()`, and once after a `fseek()` moving the cursor back 2 steps.

## Results

* For `createStream()` some implementations set the cursor at the start (`0`), some at the end (`6`).
* For `createStreamFromFile()` most implementations set the cursor at the start (`0`), only [berlioz/http-message][] sets it at the end (`6`).
* For `createStreamFromResource()` all implementations keep the cursor where it is.

```
berlioz/http-message:
                     createStream: 6
             createStreamFromFile: 6
         createStreamFromResource:
                               []: 6
                         [rewind]: 0
                          [fseek]: 4

http-interop/http-factory-guzzle:
                     createStream: 0
             createStreamFromFile: 0
         createStreamFromResource:
                               []: 6
                         [rewind]: 0
                          [fseek]: 4

http-interop/http-factory-slim:
                     createStream: 0
             createStreamFromFile: 0
         createStreamFromResource:
                               []: 6
                         [rewind]: 0
                          [fseek]: 4

nyholm/psr7:
                     createStream: 6
             createStreamFromFile: 0
         createStreamFromResource:
                               []: 6
                         [rewind]: 0
                          [fseek]: 4

tuupola/http-factory[Diactoros]:
                     createStream: 6
             createStreamFromFile: 0
         createStreamFromResource:
                               []: 6
                         [rewind]: 0
                          [fseek]: 4

tuupola/http-factory[Guzzle]:
                     createStream: 6
             createStreamFromFile: 0
         createStreamFromResource:
                               []: 6
                         [rewind]: 0
                          [fseek]: 4

tuupola/http-factory[Nyholm]:
                     createStream: 6
             createStreamFromFile: 0
         createStreamFromResource:
                               []: 6
                         [rewind]: 0
                          [fseek]: 4

tuupola/http-factory[Slim]:
                     createStream: 6
             createStreamFromFile: 0
         createStreamFromResource:
                               []: 6
                         [rewind]: 0
                          [fseek]: 4

zendframework/zend-diactoros:
                     createStream: 0
             createStreamFromFile: 0
         createStreamFromResource:
                               []: 6
                         [rewind]: 0
                          [fseek]: 4
```

## How To

1. Clone this repository.
2. Do `composer install` to get the dependencies.
3. Do `php src/index.php` to run the code.

[berlioz/http-message]: https://packagist.org/packages/berlioz/http-message
[http-interop/http-factory-diactoros]: https://packagist.org/packages/http-interop/http-factory-diactoros
[http-interop/http-factory-guzzle]: https://packagist.org/packages/http-interop/http-factory-guzzle
[http-interop/http-factory-slim]: https://packagist.org/packages/http-interop/http-factory-slim
[nyholm/psr7]: https://packagist.org/packages/nyholm/psr7
[providers]: https://packagist.org/providers/psr/http-factory-implementation
[tuupola/http-factory]: https://packagist.org/packages/tuupola/http-factory
[zendframework/zend-diactoros]: https://packagist.org/packages/zendframework/zend-diactoros