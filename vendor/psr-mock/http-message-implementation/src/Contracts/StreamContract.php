<?php

declare(strict_types=1);

namespace PsrMock\Psr7\Contracts;

use Psr\Http\Message\StreamInterface;

interface StreamContract extends StreamInterface
{
    public function __toString(): string;

    public function close(): void;

    public function detach(): mixed;

    public function eof(): bool;

    public function getContents(): string;

    public function getMetadata($key = null): mixed;

    public function getSize(): ?int;

    public function isReadable(): bool;

    public function isSeekable(): bool;

    public function isWritable(): bool;

    public function read($length): string;

    public function rewind(): void;

    public function seek($offset, $whence = SEEK_SET): void;

    public function tell(): int;

    public function write($string): int;
}
