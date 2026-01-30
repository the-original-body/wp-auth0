<?php

declare(strict_types=1);

namespace PsrMock\Psr7\Contracts;

use Psr\Http\Message\{MessageInterface, StreamInterface};

interface MessageContract extends MessageInterface
{
    public function getBody(): StreamInterface;

    public function getHeader($name): array;

    public function getHeaderLine($name): string;

    public function getHeaders(): array;

    public function getProtocolVersion(): string;

    public function hasHeader($name): bool;

    /**
     * @param string          $name
     * @param string|string[] $value
     */
    public function withAddedHeader($name, $value): static;

    public function withBody(StreamInterface $body): static;

    /**
     * @param string          $name
     * @param string|string[] $value
     */
    public function withHeader($name, $value): static;

    /**
     * @param string[][] $headers
     */
    public function withHeaders(array $headers): static;

    public function withoutHeader($name): static;

    public function withProtocolVersion($version): static;
}
