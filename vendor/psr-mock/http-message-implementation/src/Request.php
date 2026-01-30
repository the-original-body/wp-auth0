<?php

declare(strict_types=1);

namespace PsrMock\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\{RequestInterface, StreamInterface, UriInterface};
use PsrMock\Psr7\Collections\Headers;
use PsrMock\Psr7\Contracts\RequestContract;

use function is_string;

/**
 * @psalm-api
 */
final class Request extends Message implements RequestContract, RequestInterface
{
    public string $requestTarget = '';

    public function __construct(
        private string $method = 'GET',
        private UriInterface | string $uri = '',
        private string $protocolVersion = '1.1',
        private ?Headers $headers = null,
        private ?StreamInterface $stream = null,
    ) {
        $this->uri = is_string($this->uri) ? new Uri($this->uri) : $this->uri;
        parent::__construct($this->protocolVersion, $this->headers, $this->stream);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    public function getUri(): UriInterface
    {
        // @codeCoverageIgnoreStart
        if (is_string($this->uri)) {
            $this->uri = new Uri($this->uri);
        }
        // @codeCoverageIgnoreEnd

        return $this->uri;
    }

    public function withMethod($method): static
    {
        $clone         = clone $this;
        $clone->method = $method;

        return $clone;
    }

    public function withRequestTarget(mixed $requestTarget): static
    {
        if (! is_string($requestTarget)) {
            throw new InvalidArgumentException('Request target must be a string');
        }

        $clone                = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $clone      = clone $this;
        $clone->uri = $uri;

        return $clone;
    }
}
