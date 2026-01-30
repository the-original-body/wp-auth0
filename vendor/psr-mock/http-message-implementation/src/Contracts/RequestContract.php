<?php

declare(strict_types=1);

namespace PsrMock\Psr7\Contracts;

use Psr\Http\Message\{RequestInterface, UriInterface};

interface RequestContract extends RequestInterface
{
    public function getMethod(): string;

    public function getRequestTarget(): string;

    public function getUri(): UriInterface;

    public function withMethod($method): static;

    public function withRequestTarget(mixed $requestTarget): static;

    public function withUri(UriInterface $uri, $preserveHost = false): static;
}
