<?php

declare(strict_types=1);

namespace PsrMock\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use PsrMock\Psr7\Contracts\UriContract;
use Stringable;

use function in_array;
use function is_int;
use function is_string;
use function strlen;

/**
 * @psalm-api
 */
final class Uri implements Stringable, UriContract, UriInterface
{
    public function __construct(
        string $url = '',
        public string $scheme = '',
        public string $host = '',
        public ?int $port = null,
        public string $user = '',
        public string $pass = '',
        public string $path = '',
        public string $query = '',
        public string $fragment = '',
    ) {
        if ('' !== $url) {
            $url = parse_url($url);

            if (false === $url || ! isset($url['host']) || ! isset($url['scheme']) || ! in_array($url['scheme'], ['http', 'https'], true)) {
                throw new InvalidArgumentException('Invalid URL');
            }

            $this->scheme   = $url['scheme'] ?? '';
            $this->host     = $url['host'] ?? '';
            $this->port     = $url['port'] ?? null;
            $this->user     = $url['user'] ?? '';
            $this->pass     = $url['pass'] ?? '';
            $this->path     = $url['path'] ?? '';
            $this->query    = $url['query'] ?? '';
            $this->fragment = $url['fragment'] ?? '';
        }
    }

    public function __toString(): string
    {
        $built = '';

        if ('' !== $this->scheme) {
            $built .= $this->scheme . '://';
        }

        if ('' !== $this->user) {
            $built .= $this->user;

            if ('' !== $this->pass) {
                $built .= ':' . $this->pass;
            }

            $built .= '@';
        }

        if ('' !== $this->host) {
            $built .= $this->host;
        }

        if (null !== $this->port) {
            $built .= ':' . $this->port;
        }

        if ('' !== $this->path) {
            $built .= $this->path;
        }

        if ('' !== $this->query) {
            $built .= '?' . $this->query;
        }

        if ('' !== $this->fragment) {
            $built .= '#' . $this->fragment;
        }

        return $built;
    }

    public function getAuthority(): string
    {
        $userInfo = $this->getUserInfo();

        if ('' !== $userInfo) {
            $userInfo .= '@';
        }

        return $userInfo . $this->host . (null !== $this->port ? ':' . $this->port : '');
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getUserInfo(): string
    {
        return $this->user . (strlen($this->pass) > 0 ? ':' . $this->pass : '');
    }

    public function withFragment($fragment): self
    {
        if (! is_string($fragment)) {
            throw new InvalidArgumentException('Fragment must be a string');
        }

        $clone           = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    public function withHost($host): self
    {
        if (! is_string($host)) {
            throw new InvalidArgumentException('Host must be a string');
        }

        $clone       = clone $this;
        $clone->host = $host;

        return $clone;
    }

    public function withPath($path): self
    {
        if (! is_string($path)) {
            throw new InvalidArgumentException('Path must be a string');
        }

        $clone       = clone $this;
        $clone->path = $path;

        return $clone;
    }

    public function withPort($port): self
    {
        if (! is_int($port)) {
            throw new InvalidArgumentException('Port must be an integer');
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('Port must be between 1 and 65535');
        }

        $clone       = clone $this;
        $clone->port = $port;

        return $clone;
    }

    public function withQuery($query): self
    {
        if (! is_string($query)) {
            throw new InvalidArgumentException('Query must be a string');
        }

        $clone        = clone $this;
        $clone->query = $query;

        return $clone;
    }

    public function withScheme($scheme): self
    {
        if (! is_string($scheme)) {
            throw new InvalidArgumentException('Scheme must be a string');
        }

        $clone         = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    public function withUserInfo($user, $password = null): self
    {
        if (! is_string($user)) {
            throw new InvalidArgumentException('User must be a string');
        }

        if (null !== $password && ! is_string($password)) {
            throw new InvalidArgumentException('Password must be a string');
        }

        $clone       = clone $this;
        $clone->user = $user;
        $clone->pass = $password ?? '';

        return $clone;
    }
}
