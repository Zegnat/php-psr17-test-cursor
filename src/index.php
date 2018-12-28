<?php

declare(strict_types=1);

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

// Load all the externally installed packages.
require(__DIR__ . "/../vendor/autoload.php");

// Create custom instances of tuupola/http-factory
abstract class TuupolaStreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ""): StreamInterface
    {
        $resource = fopen("php://temp", "r+");
        $stream = $this->createStreamFromResource($resource);
        $stream->write($content);
        return $stream;
    }

    public function createStreamFromFile(string $filename, string $mode = "r"): StreamInterface
    {
        $resource = fopen($filename, $mode);
        return $this->createStreamFromResource($resource);
    }

    abstract public function createStreamFromResource($resource): StreamInterface;
}

class TuupolaDiactoros extends TuupolaStreamFactory
{
    public function createStreamFromResource($resource): StreamInterface
    {
        return new \Zend\Diactoros\Stream($resource);
    }
}

class TuupolaNyholm extends TuupolaStreamFactory
{
    public function createStreamFromResource($resource): StreamInterface
    {
        return \Nyholm\Psr7\Stream::create($resource);
    }
}

class TuupolaSlim extends TuupolaStreamFactory
{
    public function createStreamFromResource($resource): StreamInterface
    {
        return new \Slim\Http\Stream($resource);
    }
}

class TuupolaGuzzle extends TuupolaStreamFactory
{
    public function createStreamFromResource($resource): StreamInterface
    {
        return new \GuzzleHttp\Psr7\Stream($resource);
    }
}

$string = 'Hello!';
$file = \tempnam(\sys_get_temp_dir(), 'http_factory_test_');
$resource = \fopen($file, 'r+');
\fwrite($resource, $string);
\fclose($resource);

foreach ([
            'berlioz/http-message' => new \Berlioz\Http\Message\HttpFactory(),
            'http-interop/http-factory-guzzle' => new \Http\Factory\Guzzle\StreamFactory(),
            'http-interop/http-factory-slim' => new \Http\Factory\Slim\StreamFactory(),
            'nyholm/psr7' => new \Nyholm\Psr7\Factory\Psr17Factory(),
            'tuupola/http-factory[Diactoros]' => new TuupolaDiactoros(),
            'tuupola/http-factory[Guzzle]' => new TuupolaGuzzle(),
            'tuupola/http-factory[Nyholm]' => new TuupolaNyholm(),
            'tuupola/http-factory[Slim]' => new TuupolaSlim(),
            'zendframework/zend-diactoros' => new \Zend\Diactoros\StreamFactory(),
        ] as $package => $factory) {
    echo $package . ":\n";
    $stream = $factory->createStream($string);
    echo "                     createStream: " . $stream->tell() . "\n";
    $stream = $factory->createStreamFromFile($file);
    echo "             createStreamFromFile: " . $stream->tell() . "\n";
    echo "         createStreamFromResource:\n";
    $resource = \fopen('php://temp', 'r+');
    \fwrite($resource, $string);
    $stream = $factory->createStreamFromResource($resource);
    echo "                               []: " . $stream->tell() . "\n";
    \fclose($stream->detach());
    $stream = null;
    $resource = \fopen('php://temp', 'r+');
    \fwrite($resource, $string);
    \rewind($resource);
    $stream = $factory->createStreamFromResource($resource);
    echo "                         [rewind]: " . $stream->tell() . "\n";
    \fclose($stream->detach());
    $stream = null;
    $resource = \fopen('php://temp', 'r+');
    \fwrite($resource, $string);
    \fseek($resource, -2, \SEEK_CUR);
    $stream = $factory->createStreamFromResource($resource);
    echo "                          [fseek]: " . $stream->tell() . "\n";
    \fclose($stream->detach());
    $stream = null;
    echo "\n";
}

\unlink($file);
