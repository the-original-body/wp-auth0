<?php

declare(strict_types=1);

namespace PsrMock\Psr7\Contracts;

use Psr\Http\Message\UriInterface;

interface UriContract extends UriInterface
{
    public function __toString(): string;

    public function getAuthority(): string;

    public function getFragment(): string;

    public function getHost(): string;

    public function getPath(): string;

    public function getPort(): ?int;

    public function getQuery(): string;

    public function getScheme(): string;

    public function getUserInfo(): string;

    public function withFragment($fragment): UriInterface;

    public function withHost($host): UriInterface;

    public function withPath($path): UriInterface;

    public function withPort($port): UriInterface;

    public function withQuery($query): UriInterface;

    public function withScheme($scheme): UriInterface;

    public function withUserInfo($user, $password = null): UriInterface;
}
