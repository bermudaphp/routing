 # Installation
 ```bash
 composer require bermudaphp/router
 ````
 ## Usage

 ```php
 $routes = new Routes;
 $router = Router::fromDnf($routes);

 $routes->get('home', '/hello/[name]', static function(string $name): void {
     echo sprintf('Hello, %s!', $name)
 }); 
 
 $route = $router->match($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
 if (!route) {
     // route not found logics
 }
 
 call_user_func($route->handler, $route->params['name']);
 ```
 ## Route path generation
 ```php
 echo $router->generate('home', ['name' => 'Jane Doe']); // Output /hello/Jane%20Doe
 ```
 ## Usage with PSR-15
 
 ```php
 
 $pipeline = new \Bermuda\Pipeline\Pipeline();
 $factory = new \Bermuda\MiddlewareFactory\MiddlewareFactory($container, $responseFactory);
 
 class Handler implements RequestHandlerInterface
 {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new TextResponse(sprintf('Hello, %s!', $request->getAttribute('name')))
    }
 };
 
 $router->get('home', '/hello/[name:[a-z]]', Handler::class);
 
 $pipeline->pipe($factory->make(Middleware\MatchRouteMiddleware::class));
 $pipeline->pipe($factory->make(Middleware\DispatchRouteMiddleware::class)
     ->setFallbackHandler($container->get(Middleware\RouteNotFoundHandler::class)));
  
 $response = $pipeline->handle($request);

 send($response)
 ```
 ## Get current route data
 
 ```php
 class Handler implements RequestHandlerInterface
 {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $request->getAttribute('Bermuda\Router\Middleware\RouteMiddleware')->route; // MatchedRoute instance
    }
 }; 
 ```
 ## RouteMap HTTP Methods
 
 ```php
 $routes->get(string $name, string $path, mixed $handler): RouteRecord ;
 $routes->post(string $name, string $path, mixed $handler): RouteRecord ;
 $routes->patch(string $name, string $path, mixed $handler): RouteRecord ;
 $routes->put(string $name, string $path, mixed $handler): RouteRecord ;
 $routes->delete(string $name, string $path, mixed $handler): RouteRecord ;
 $routes->options(string $name, string $path, mixed $handler): RouteRecord ;
 $routes->head(string $name, string $path, mixed $handler): RouteRecord ;
 $routes->any(string $name, string $path, mixed $handler): RouteRecord ;
 ```
 
 ## Set attribute placeholder pattern
 
 ```php
 $routes->get('users.get, '/api/v1/users/[id:[a-zA-Z]]', static function(ServerRequestInterface $request): ResponseInterface {
     return findUser($request->getAttribute('id'));
 });

 alternative:
 $routes->get('users.get, '/api/v1/users/[id]', static function(ServerRequestInterface $request): ResponseInterface {
     return findUserById($request->getAttribute('id'));
 })->setToken('id', '[a-zA-Z]');
 ```
 ## Optional attribute
 
 ```php
 $routes->get('users.get, '/api/v1/users/[?id]', static function(ServerRequestInterface $request): ResponseInterface {
     if (($id = $request->getAttribute('id')) !== null) {
         return findUserById($id);
     }
     
     return get_all_users();
 });
 ```
 
 ## Predefined placeholders
 
 ````
 id: \d+
 ````
 
 Other placeholders passed to path as a string without being explicitly defined will match the pattern `.*`
  
 ## Routes Group
 
 ```php
 $group = $routes->group(name: 'api', prifix: '/api'); // set routes group

 $group->get('users.get, 'users/[?id]', GetUserHandler::class);
 $group->post(user.create, 'users', CreateUserHandler::class);

 $group = $routes->group('api') // get routes group from name
 $group->setMiddleware(GuardMiddleware::class) // set middleware for all routes in group
 $group->setTokens(['id' => '[a-zA-Z]']) // set tokens for all routes in group
 ```

## Cache
 
Once all routes are registered in the route map and they will no longer be changed. Call the $routes->cache method to cache the route map in a php file. Then use the `Routes::createFromCache('/path/to/cached/routes/filename.php')` method to create a map instance with preloaded routes.

```php
 
 $routes->cache('path/to/cached/routes/file.php');
 $routes = Routes::createFromCache('path/to/cached/routes/file.php')
 
 $router = new Router($routes, $routes, $routes);
 ```
# Cache context
If you are using a parent-context-bound closure (the use construct) as a route handler, then you must pass an array of bound variables to the `Routes::createFromCache` method. See example below
```php
 $app = new App;
 $repository = new UserRepository;
 $routes->get('user.get', '/user/{id}', static function(int $id) use ($app, $repository): ResponseInterface {
    return $app->respond(200, $repository->findById($id));
 });

 $routes->cache('path/to/cached/routes/file.php');
 $routes = Routes::createFromCache('path/to/cached/routes/file.php', compact('app', 'repository'));
 ```
 
 # Cache limitations
 Currently, the caching implementation does not allow caching routes using object instances and callback functions based on object instances.

 # Benchmark
 ```
+---------------------------+-------------------+------------+-----------------+-------+--------------+-------------------+
| benchmark                 | registered_routes | cache_mode | exec_time       | its   | memory_usage | memory_peak_usage |
+---------------------------+-------------------+------------+-----------------+-------+--------------+-------------------+
| Benchmark\RouterBenchmark | 1001              | disable    | 32.680938005447 | 10000 | 16 MB        | 16 MB             |
| Benchmark\RouterBenchmark | 1001              | enable     | 1.298574924469  | 10000 | 16 MB        | 16 MB             |
+---------------------------+-------------------+------------+-----------------+-------+--------------+-------------------+
 ````

