<?php

declare(strict_types=1);

namespace PsrMock\Psr7;

use const SEEK_SET;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use PsrMock\Psr7\Contracts\StreamContract;
use RuntimeException;
use Stringable;

use function array_key_exists;
use function fclose;
use function feof;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function fwrite;
use function is_array;
use function is_resource;
use function is_string;
use function pclose;
use function rewind;
use function stream_get_contents;
use function stream_get_meta_data;

/**
 * @psalm-api
 */
final class Stream implements StreamContract, StreamInterface, Stringable
{
    private ?bool $isPipe = null;

    /**
     * @var null|array<mixed>
     */
    private ?array $meta      = null;
    private ?bool  $readable  = null;
    private ?bool  $seekable  = null;
    private ?int   $size      = null;
    private ?bool  $writable  = null;

    /**
     * @param null|resource|string $stream A PHP resource handle.
     *
     * @throws InvalidArgumentException If argument is not a resource.
     */
    public function __construct(
        private mixed $stream = null,
    ) {
        $resource = is_resource($stream) ? $stream : fopen('php://temp', 'r+wb');

        // @codeCoverageIgnoreStart
        if (false === $resource) {
            throw new InvalidArgumentException('Invalid resource provided; must be a stream resource');
        }
        // @codeCoverageIgnoreEnd

        $this->attach($resource);

        if (! is_string($stream) && ! is_resource($stream)) {
            $stream = '';
        }

        if (is_string($stream)) {
            $this->write($stream);
            $this->rewind();
        }
    }

    public function __toString(): string
    {
        // @codeCoverageIgnoreStart
        if (! is_resource($this->stream)) {
            return '';
        }
        // @codeCoverageIgnoreEnd

        if ($this->isSeekable()) {
            $this->rewind();
        }

        return $this->getContents();
    }

    /**
     * Attach new resource to this object.
     *
     * @internal This method is not part of the PSR-7 standard.
     *
     * @param resource $stream A PHP resource handle.
     *
     * @throws InvalidArgumentException If argument is not a valid PHP resource.
     */
    private function attach($stream): void
    {
        // @codeCoverageIgnoreStart
        if (! is_resource($stream)) {
            throw new InvalidArgumentException(__METHOD__ . ' argument must be a valid PHP resource');
        }

        if (is_resource($this->stream)) {
            $this->detach();
        }
        // @codeCoverageIgnoreEnd

        $this->stream = $stream;
    }

    /**
     * @codeCoverageIgnore
     */
    public function close(): void
    {
        if (! is_resource($this->stream)) {
            return;
        }

        if ($this->isPipe()) {
            pclose($this->stream);
        } else {
            fclose($this->stream);
        }

        $this->detach();
    }

    /**
     * @codeCoverageIgnore
     */
    public function detach(): mixed
    {
        $oldResource = $this->stream;

        $this->stream   = null;
        $this->meta     = null;
        $this->readable = null;
        $this->writable = null;
        $this->seekable = null;
        $this->size     = null;
        $this->isPipe   = null;

        if (! is_resource($oldResource)) {
            return null;
        }

        return $oldResource;
    }

    public function eof(): bool
    {
        return ! is_resource($this->stream) || feof($this->stream);
    }

    public function getContents(): string
    {
        $contents = false;

        if (is_resource($this->stream)) {
            $contents = stream_get_contents($this->stream);
        }

        if (is_string($contents)) {
            return $contents;
        }

        throw new RuntimeException('Could not get contents of stream.');
    }

    /**
     * @param null|mixed $key
     *
     * @return array<string>|mixed
     */
    public function getMetadata($key = null): mixed
    {
        // @codeCoverageIgnoreStart
        if (! is_resource($this->stream)) {
            return null;
        }
        // @codeCoverageIgnoreEnd

        $this->meta = stream_get_meta_data($this->stream);

        if (! is_string($key)) {
            return $this->meta;
        }

        if (array_key_exists($key, $this->meta)) {
            return $this->meta[$key];
        }

        return null;
    }

    public function getSize(): ?int
    {
        if (is_resource($this->stream) && null === $this->size) {
            $stats = fstat($this->stream);

            if (is_array($stats) && isset($stats['size'])) {
                $this->size = $this->isPipe() ? null : $stats['size'];
            }
        }

        return $this->size;
    }

    public function isPipe(): bool
    {
        if (null === $this->isPipe) {
            $this->isPipe = false;

            if (is_resource($this->stream)) {
                $stats = fstat($this->stream);

                if (is_array($stats)) {
                    $this->isPipe = ($stats['mode'] & octdec('0010000')) !== 0;
                }
            }
        }

        return $this->isPipe;
    }

    public function isReadable(): bool
    {
        // @codeCoverageIgnoreStart
        if (null !== $this->readable) {
            return $this->readable;
        }
        // @codeCoverageIgnoreEnd

        $this->readable = false;

        if (is_resource($this->stream)) {
            $mode = $this->getMetadata('mode');

            if (is_string($mode) && (str_contains($mode, 'r') || str_contains($mode, '+'))) {
                $this->readable = true;
            }
        }

        return $this->readable;
    }

    public function isSeekable(): bool
    {
        if (null === $this->seekable) {
            $this->seekable = false;

            if (is_resource($this->stream)) {
                $this->seekable = ! $this->isPipe() && null !== $this->getMetadata('seekable');
            }
        }

        return $this->seekable;
    }

    public function isWritable(): bool
    {
        if (null === $this->writable) {
            $this->writable = false;

            if (is_resource($this->stream)) {
                $mode = $this->getMetadata('mode');

                if (! is_string($mode) || ! str_contains($mode, 'w') && ! str_contains($mode, '+')) {
                    return $this->writable;
                }

                return $this->writable = true;
            }
        }

        // @codeCoverageIgnoreStart
        return $this->writable;
        // @codeCoverageIgnoreEnd
    }

    public function read($length): string
    {
        $data = false;

        if (is_resource($this->stream) && $this->isReadable() && $length >= 0) {
            $data = fread($this->stream, $length);
        }

        if (is_string($data)) {
            return $data;
        }

        throw new RuntimeException('Could not read from stream.');
    }

    public function rewind(): void
    {
        if (! is_resource($this->stream) || ! $this->isSeekable() || ! rewind($this->stream)) {
            throw new RuntimeException('Could not rewind stream.');
        }
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (! is_resource($this->stream) || ! $this->isSeekable() || -1 === fseek($this->stream, $offset, $whence)) {
            throw new RuntimeException('Could not seek in stream.');
        }
    }

    public function tell(): int
    {
        $position = false;

        if (is_resource($this->stream)) {
            $position = ftell($this->stream);
        }

        // @codeCoverageIgnoreStart
        if (false === $position || $this->isPipe()) {
            throw new RuntimeException('Could not get the position of the pointer in stream.');
        }
        // @codeCoverageIgnoreEnd

        return $position;
    }

    public function write($string): int
    {
        $written = false;

        if (is_resource($this->stream) && $this->isWritable()) {
            $written = fwrite($this->stream, $string);
        }

        if (false !== $written) {
            $this->size = null;

            return $written;
        }

        throw new RuntimeException('Could not write to stream.');
    }
}
