<?php

declare(strict_types=1);

namespace PsrMock\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\{MessageInterface, StreamInterface};
use PsrMock\Psr7\Collections\Headers;
use PsrMock\Psr7\Contracts\MessageContract;

use function is_array;
use function is_string;

abstract class Message implements MessageContract, MessageInterface
{
    public function __construct(
        private string $protocolVersion = '1.1',
        private ?Headers $headers = null,
        private ?StreamInterface $stream = null,
    ) {
    }

    private function headers(): Headers
    {
        return $this->headers ??= new Headers();
    }

    final public function getBody(): StreamInterface
    {
        if (! $this->stream instanceof StreamInterface) {
            $this->stream = new Stream();
        }

        return $this->stream;
    }

    final public function getHeader($name): array
    {
        return $this->headers()->get($name);
    }

    final public function getHeaderLine($name): string
    {
        return $this->headers()->getString($name);
    }

    final public function getHeaders(): array
    {
        return $this->headers()->all();
    }

    final public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    final public function hasHeader($name): bool
    {
        return $this->headers()->has($name);
    }

    final public function withAddedHeader($name, $value): static
    {
        $clone = clone $this;

        if (is_array($value)) {
            foreach ($value as $v) {
                if (is_string($v)) {
                    $clone->headers()->add($name, $v);
                }
            }
        } elseif (is_string($value)) {
            $clone->headers()->add($name, $value);
        }

        return $clone;
    }

    final public function withBody(
        StreamInterface $body,
    ): static {
        if ($body === $this->stream) {
            return $this;
        }

        $clone         = clone $this;
        $clone->stream = $body;

        return $clone;
    }

    final public function withHeader($name, $value): static
    {
        $clone = clone $this->withoutHeader($name);

        return $clone->withAddedHeader($name, $value);
    }

    final public function withHeaders(array $headers): static
    {
        $clone = clone $this;

        foreach ($headers as $name => $value) {
            if (! is_string($name)) {
                continue;
            }

            $clone->headers()->remove($name);

            if (is_array($value)) {
                foreach ($value as $v) {
                    $clone->headers()->add($name, $v);
                }
            } elseif (is_string($value)) {
                $clone->headers()->add($name, $value);
            }
        }

        return $clone;
    }

    final public function withoutHeader($name): static
    {
        $clone = clone $this;
        $clone->headers()->remove($name);

        return $clone;
    }

    final public function withProtocolVersion($version): static
    {
        if (! is_string($version)) {
            throw new InvalidArgumentException('Protocol version must be a string');
        }

        $clone                  = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }
}
