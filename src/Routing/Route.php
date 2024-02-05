<?php

namespace NetBhaari\NetLib\Routing;

/**
 * Class Route
 * 
 * Responsible for managing and handling routes.
 */
class Route
{
    private static $routes = [];        // Array to store defined routes
    private static $fallbackRoute;      // Callback for the fallback route
    private static $routePrefix = '';    // Prefix for routes

    /**
     * Define a route for a specific domain.
     *
     * @param  string   $domain    The domain to match
     * @param  callable $callback  The callback to execute if the domain matches
     * @return void
     */
    public static function domain($domain, $callback)
    {
        $currentDomain = $_SERVER['HTTP_HOST'];

        // Check if the current domain matches the specified domain
        if ($currentDomain === $domain) {
            call_user_func($callback);  // Execute the callback if the domain matches
        }
    }

    /**
     * Define a route prefix.
     *
     * @param  string   $prefix    The prefix to add to subsequent routes
     * @param  callable $callback  The callback to execute with the prefix
     * @return void
     */
    public static function prefix($prefix, $callback)
    {
        $previousPrefix = self::$routePrefix;  // Store the previous prefix
        self::$routePrefix .= '/' . trim($prefix, '/');  // Add the new prefix
        call_user_func($callback);  // Execute the callback with the new prefix
        self::$routePrefix = $previousPrefix;  // Restore the previous prefix
    }

    /**
     * Add a route with the specified HTTP methods, URI, and callback.
     *
     * @param  mixed    $methods   The HTTP methods for the route
     * @param  string   $uri       The URI pattern for the route
     * @param  callable $callback  The callback to execute when the route is matched
     * @return void
     */
    private static function addPrefixedRoute($methods, $uri, $callback)
    {
        $prefixedUri = self::$routePrefix . '/' . ltrim($uri, '/');
        self::addRoute($methods, $prefixedUri, $callback);
    }

    /**
     * Add a route with the specified HTTP methods, URI, and callback.
     *
     * @param  mixed    $methods   The HTTP methods for the route
     * @param  string   $uri       The URI pattern for the route
     * @param  callable $callback  The callback to execute when the route is matched
     * @return void
     */
    private static function addRoute($methods, $uri, $callback)
    {
        // Transform callback to standard format if it's a string or a single-element array
        if (is_string($callback)) {
            if (strpos($callback, '@') !== false) {
                list($controller, $method) = explode('@', $callback);
                $callback = [trim($controller), trim($method)];
            } else {
                $callback = [trim($callback), 'index'];
            }
        } elseif (is_array($callback) && count($callback) === 1 && is_string($callback[0])) {
            $callback = [trim($callback[0]), 'index'];
        }

        // Iterate through each specified HTTP method
        foreach ((array)$methods as $method) {
            self::$routes[] = ['method' => strtoupper($method), 'uri' => $uri, 'callback' => $callback];
        }
    }

    /**
     * Define a route that responds to any HTTP method.
     *
     * @param  string   $uri       The URI pattern for the route
     * @param  callable $callback  The callback to execute when the route is matched
     * @return void
     */
    public static function any($uri, $callback)
    {
        self::addPrefixedRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $callback);
    }

    /**
     * Define a route that responds to specified HTTP methods.
     *
     * @param  array    $methods   The HTTP methods for the route
     * @param  string   $uri       The URI pattern for the route
     * @param  callable $callback  The callback to execute when the route is matched
     * @return void
     */
    public static function match(array $methods, $uri, $callback)
    {
        self::addPrefixedRoute($methods, $uri, $callback);
    }

    /**
     * Define a route that responds to GET requests.
     *
     * @param  string   $uri       The URI pattern for the route
     * @param  callable $callback  The callback to execute when the route is matched
     * @return void
     */
    public static function get($uri, $callback)
    {
        self::addPrefixedRoute('GET', $uri, $callback);
    }

    /**
     * Define a route that maps to a controller action for specified HTTP method.
     *
     * @param  string   $uri       The URI pattern for the route
     * @param  array    $callback  The callback representing the controller and action
     * @param  string   $method    The HTTP method for the route (default is 'GET')
     * @return void
     */
    public static function controller($uri, $callback, $method = "GET")
    {
        $controller = '\App\Http\Controllers\\' . $callback[0];
        $action = $callback[1] ?? 'index';
        $callback = [$controller, $action];
        self::addPrefixedRoute($method, $uri, $callback);
    }


    /**
     * Define a route that responds to POST requests.
     *
     * @param  string   $uri       The URI pattern for the route
     * @param  callable $callback  The callback to execute when the route is matched
     * @return void
     */
    public static function post($uri, $callback)
    {
        self::addPrefixedRoute('POST', $uri, $callback);
    }

    /**
     * Define a route that responds to PUT requests.
     *
     * @param  string   $uri       The URI pattern for the route
     * @param  callable $callback  The callback to execute when the route is matched
     * @return void
     */
    public static function put($uri, $callback)
    {
        self::addPrefixedRoute('PUT', $uri, $callback);
    }

    /**
     * Define a route that responds to PATCH requests.
     *
     * @param  string   $uri       The URI pattern for the route
     * @param  callable $callback  The callback to execute when the route is matched
     * @return void
     */
    public static function patch($uri, $callback)
    {
        self::addPrefixedRoute('PATCH', $uri, $callback);
    }

    /**
     * Define a route that responds to DELETE requests.
     *
     * @param  string   $uri       The URI pattern for the route
     * @param  callable $callback  The callback to execute when the route is matched
     * @return void
     */
    public static function delete($uri, $callback)
    {
        self::addPrefixedRoute('DELETE', $uri, $callback);
    }

    /**
     * Define a route that renders a view.
     *
     * @param  string $uri   The URI pattern for the route
     * @param  string $view  The name of the view to render
     * @param  array  $data  The data to pass to the view
     * @return void
     */
    public static function view($uri, $view, $data = [])
    {
        self::$routes[] = ['method' => 'GET', 'uri' => $uri, 'view' => $view, 'data' => $data];
    }

    /**
     * Define a resourceful route for a controller.
     *
     * @param  string $uri         The URI pattern for the route
     * @param  string $controller  The name of the controller
     * @return void
     */
    public static function resource($uri, $controller)
    {
        // Example: Define routes for CRUD operations related to a resource
        self::prefix($uri, function () use ($controller) {
            self::get('/', "$controller@index");
            self::get('/create', "$controller@create");
            self::post('/', "$controller@store");
            self::get('/{id}', "$controller@show");
            self::get('/{id}/edit', "$controller@edit");
            self::put('/{id}', "$controller@update");
            self::patch('/{id}', "$controller@update");
            self::delete('/{id}', "$controller@destroy");
        });
    }

    /**
     * Define a route that performs a redirect.
     *
     * @param  string $from       The URI pattern to redirect from
     * @param  string $to         The URI or URL to redirect to
     * @param  int    $statusCode The HTTP status code for the redirect
     * @return void
     */
    public static function redirect($from, $to, $statusCode = 302)
    {
        $from = self::$routePrefix . '/' . ltrim($from, '/');
        self::$routes[] = [
            'method' => 'GET',
            'uri' => $from,
            'redirect' => $to,
            'statusCode' => $statusCode,
        ];
    }

    /**
     * Define a route that performs a permanent redirect (HTTP 301).
     *
     * @param  string $from  The URI pattern to redirect from
     * @param  string $to    The URI or URL to redirect to
     * @return void
     */
    public static function permanentRedirect($from, $to)
    {
        self::redirect($from, $to, 301);
    }

    /**
     * Handle incoming HTTP requests.
     *
     * @return void
     */
    public static function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $domain = $_SERVER['HTTP_HOST'];
        $fallbackContent = '';

        // Example: Instantiate RouteHandler to handle the request
        $routeHandler = new RouteHandler(self::$routes, self::$fallbackRoute, self::$routePrefix);
        $routeHandler->handleRequest($method, $uri, $domain, $fallbackContent);
    }

    /**
     * Define a fallback route.
     *
     * @param  callable $callback  The callback to execute as a fallback route
     * @return void
     */
    public static function fallback($callback)
    {
        self::$fallbackRoute = $callback;
    }

}



// Register the handleRequest method to be called on script shutdown
register_shutdown_function(['NetBhaari\NetLib\Routing\Route', 'handleRequest']);