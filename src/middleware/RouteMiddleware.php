<?php


namespace Bermuda\Router\Middleware;


use Bermuda\Router\Exception\ExceptionFactory;
use Bermuda\Router\RouteInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Bermuda\MiddlewareFactory\MiddlewareFactoryInterface;


/**
 * Class RouteMiddleware
 * @package Bermuda\Router\Middleware
 */
final class RouteMiddleware implements MiddlewareInterface, RequestHandlerInterface, RouteInterface
{
    private RouteInterface $route;
    private MiddlewareFactoryInterface $factory;

    public function __construct(MiddlewareFactoryInterface $factory, RouteInterface $route)
    {
        $this->route = $route;
        $this->factory = $factory;
    }

     /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->factory->make($this->route->getHandler())
            ->process($request, $handler)
            ->withHeader('Allow', implode(', ', array_map('strtoupper', $this->route->methods())));
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $req): ResponseInterface
            {
                ExceptionFactory::emptyHandler()->throw();
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->route->getName();
    }

    /**
     * @inheritDoc
     */
    public function getHandler()
    {
        return $this->route->getHandler();
    }

    /**
     * @inheritDoc
     */
    public function addPrefix(string $prefix): RouteInterface
    {
        $this->route->addPrefix($prefix);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addSuffix(string $suffix): RouteInterface
    {
        $this->route->addSuffix($suffix);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->route->getPath();
    }

    /**
     * @inheritDoc
     */
    public function tokens(array $tokens = [], bool $replace = false): array
    {
        return $this->route->tokens($tokens, $replace);
    }

    /**
     * @inheritDoc
     */
    public function methods($methods = null): array
    {
        return $this->route->methods($methods);
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
       return $this->route->getAttributes();
    }

    /**
     * @inheritDoc
     */
    public function withAttributes(array $attributes): RouteInterface
    {
        $this->route = $this->route->withAttributes($attributes);
        return clone $this;
    }
    
    /**
     * @inheritDoc
     */
    public function before($middleware) : RouteInterface
    {
        $this->route->before($middleware);
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function after($middleware) : RouteInterface
    {
        $this->route->after($middleware);
        return $this;
    }
}
