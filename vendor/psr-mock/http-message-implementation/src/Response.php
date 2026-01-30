<?php

declare(strict_types=1);

namespace PsrMock\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\{ResponseInterface, StreamInterface};
use PsrMock\Psr7\Collections\Headers;
use PsrMock\Psr7\Contracts\ResponseContract;

use function is_int;
use function is_string;

/**
 * @psalm-api
 */
final class Response extends Message implements ResponseContract, ResponseInterface
{
    public function __construct(
        private int $statusCode = 200,
        private string $reasonPhrase = '',
        private string $protocolVersion = '1.1',
        private ?Headers $headers = null,
        private ?StreamInterface $stream = null,
    ) {
        parent::__construct($this->protocolVersion, $this->headers, $this->stream);
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        if (! is_int($code)) {
            throw new InvalidArgumentException('Status code must be an integer');
        }

        if (! is_string($reasonPhrase)) {
            throw new InvalidArgumentException('Reason phrase must be a string');
        }

        $clone               = clone $this;
        $clone->statusCode   = $code;
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }
}
