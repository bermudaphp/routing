<?php

namespace Bermuda\Router;

/**
 * @property-read string $name
 * @property-read string $path
 * @property-read mixed $handler
 * @property-read ?string $group
 * @property-read array<string> $tokens
 * @property-read array<string> $methods
 * @property-read ?array<string> $defaults
 */
final class RouteRecord
{
    /**
     * @var array{
     *      handler: mixed,
     *      path: string,
     *      name: string,
     *      group: ?string,
     *      methods: array<string>,
     *      tokens: array
     *  }
     */
    private array $routeData;

    public const id = '\d+';

    public function __construct(string $name, string $path, mixed $handler)
    {
        $this->routeData = [
            'name' => $name,
            'path' => normalize_path($path),
            'handler' => [$handler],
            'tokens' => ['id' => self::id],
            'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
            'group' => null,
            'defaults' => null
        ];
    }

    public function setToken(string $name, ?string $pattern): self
    {
        if (!$pattern) {
            unset($this->tokens[$name]);
            return $this;
        }
        
        $this->routeData['tokens'][$name] = $pattern;
        return $this;
    }

    public function __get(string $name)
    {
        if (isset($this->routeData[$name])) {
            if ($name === 'handler') {
                return \count($this->routeData['handler']) > 1 ?
                    $this->routeData['handler'] : $this->routeData['handler'][0];
            }
            
            return $this->routeData[$name];
        }

        return null;
    }

    public function setDefaults(?array $defaults): self
    {
        $this->routeData['defaults'] = $defaults;
        return $this;
    }

    public function setGroup(?string $name): self
    {
        $this->routeData['group'] = $name;
        return $this;
    }

    public function setMiddleware(?array $middleware): self
    {
        if (!$middleware) {
            $this->routeData['handler'] = array_pop($this->routeData['handler']);
            return $this;
        }
        
        $this->routeData['handler'] = [...$middleware, array_pop($this->routeData['handler'])];
        return $this;
    }
    
    public function addMiddleware(mixed $middleware): self
    {
        $handler = array_pop($this->routeData['handler']);
        $this->routeData['handler'][] = $middleware;
        $this->routeData['handler'][] = $handler;
        
        return $this;
    }

    public function setMethods(array $methods): self
    {
        $this->routeData['methods'] = array_map('strtoupper', $methods);
        return $this;
    }

    public static function fromArray(array $routeData): self
    {
        $route = new self(
            $routeData['name'],
            $routeData['path'],
            $routeData['handler']
        );

        if (isset($routeData['methods'])) $route->setMethods($routeData['methods']);
        if (isset($routeData['middleware'])) $route->setMiddleware($routeData['middleware']);
        if (isset($routeData['defaults'])) $route->setDefaults($routeData['defaults']);
        if (isset($routeData['group'])) $route->setGroup($routeData['group']);
        if (isset($routeData['tokens'])) {
            foreach ($routeData['tokens'] as $token => $pattern) {
                $route->setToken($token, $pattern);
            }
        }

        return $route;
    }
    
    /**
     * @return array{
     *     handler: mixed,
     *     path: string,
     *     name: string,
     *     group: ?string,
     *     methods: array<string>,
     *     tokens: array
     * }
     */
    public function toArray(): array
    {
        return $this->routeData;
    }
}
