<?php

namespace NetBhaari\NetLib\Routing;

class RouteMix
{
    // Store all routes
    private static $routes = [];
    
    // Store the fallback route
    private static $fallbackRoute;
    
    // Store the current route prefix
    private static $routePrefix = '';
    
    /**
     * Match the current domain with the provided domain and execute the callback if matched.
     *
     * Example:
     *     Route::domain('example.com', function () {
     *         // Your routes for 'example.com'
     *     });
     *
     * @param  string   $domain   The domain to match against
     * @param  callable $callback The callback to execute if the domain matches
     * @return void
     */



    public static function domain($domain, $callback)
    {
        $currentDomain = $_SERVER['HTTP_HOST'];

        if ($currentDomain === $domain) {
            call_user_func($callback);
        }
    }

    /**
     * Add a prefix to the routes defined within the provided callback.
     *
     * Example:
     *     Route::prefix('admin', function () {
     *         Route::get('/dashboard', 'AdminController@dashboard');
     *         // Other admin routes...
     *     });
     *
     * @param  string   $prefix   The prefix to add to the routes
     * @param  callable $callback The callback containing routes with the prefix
     * @return void
     */


    public static function prefix($prefix, $callback)
    {
        $previousPrefix = self::$routePrefix;
        self::$routePrefix .= '/' . trim($prefix, '/');
        call_user_func($callback);
        self::$routePrefix = $previousPrefix;
    }

    /**
     * Add a route with the current prefix applied for multiple HTTP methods.
     *
     * Example:
     *     Route::match(['GET', 'POST'], '/example', 'ExampleController@index');
     *
     * @param  array    $methods  The HTTP methods to match (GET, POST, etc.)
     * @param  string   $uri      The URI pattern for the route
     * @param  mixed    $callback The callback for the route (controller method or closure)
     * @return void
     */
    private static function addPrefixedRoute($methods, $uri, $callback)
    {
        $prefixedUri = self::$routePrefix . '/' . ltrim($uri, '/');
        self::addRoute($methods, $prefixedUri, $callback);
    }

    /**
     * Add a route for specified HTTP methods.
     *
     * Example:
     *     Route::get('/example', 'ExampleController@index');
     *
     * @param  mixed    $methods  The HTTP methods to match (string or array)
     * @param  string   $uri      The URI pattern for the route
     * @param  mixed    $callback The callback for the route (controller method or closure)
     * @return void
     */
    private static function addRoute($methods, $uri, $callback)
    {
        // Normalize callback to controller method format
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

        // Add the route for each specified method
        foreach ((array)$methods as $method) {
            self::$routes[] = ['method' => strtoupper($method), 'uri' => $uri, 'callback' => $callback];
        }
    }

    /**
     * Add a route that matches any HTTP method.
     *
     * Example:
     *     Route::any('/example', 'ExampleController@index');
     *
     * @param  string   $uri      The URI pattern for the route
     * @param  mixed    $callback The callback for the route (controller method or closure)
     * @return void
     */
    public static function any($uri, $callback)
    {
        self::addPrefixedRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $callback);
    }

    /**
     * Add a route that matches specific HTTP methods.
     *
     * Example:
     *     Route::match(['GET', 'POST'], '/example', 'ExampleController@index');
     *
     * @param  array    $methods  The HTTP methods to match (GET, POST, etc.)
     * @param  string   $uri      The URI pattern for the route
     * @param  mixed    $callback The callback for the route (controller method or closure)
     * @return void
     */
    public static function match(array $methods, $uri, $callback)
    {
        self::addPrefixedRoute($methods, $uri, $callback);
    }

    /**
     * Add a route for HTTP GET method.
     *
     * Example:
     *     Route::get('/example', 'ExampleController@index');
     *
     * @param  string   $uri      The URI pattern for the route
     * @param  mixed    $callback The callback for the route (controller method or closure)
     * @return void
     */
    public static function get($uri, $callback)
    {
        self::addPrefixedRoute('GET', $uri, $callback);
    }

    /**
     * Add a route for HTTP POST method.
     *
     * Example:
     *     Route::post('/example', 'ExampleController@store');
     *
     * @param  string   $uri      The URI pattern for the route
     * @param  mixed    $callback The callback for the route (controller method or closure)
     * @return void
     */
    public static function post($uri, $callback)
    {
        self::addPrefixedRoute('POST', $uri, $callback);
    }

    /**
     * Add a route for HTTP PUT method.
     *
     * Example:
     *     Route::put('/example', 'ExampleController@update');
     *
     * @param  string   $uri      The URI pattern for the route
     * @param  mixed    $callback The callback for the route (controller method or closure)
     * @return void
     */
    public static function put($uri, $callback)
    {
        self::addPrefixedRoute('PUT', $uri, $callback);
    }

    /**
     * Add a route for HTTP PATCH method.
     *
     * Example:
     *     Route::patch('/example', 'ExampleController@update');
     *
     * @param  string   $uri      The URI pattern for the route
     * @param  mixed    $callback The callback for the route (controller method or closure)
     * @return void
     */
    public static function patch($uri, $callback)
    {
        self::addPrefixedRoute('PATCH', $uri, $callback);
    }

    /**
     * Add a route for HTTP DELETE method.
     *
     * Example:
     *     Route::delete('/example', 'ExampleController@destroy');
     *
     * @param  string   $uri      The URI pattern for the route
     * @param  mixed    $callback The callback for the route (controller method or closure)
     * @return void
     */
    public static function delete($uri, $callback)
    {
        self::addPrefixedRoute('DELETE', $uri, $callback);
    }

    /**
     * Add a route for rendering a view.
     *
     * Example:
     *     Route::view('/about', 'about', ['title' => 'About Us']);
     *
     * @param  string   $uri      The URI pattern for the route
     * @param  string   $view     The name of the view to render
     * @param  array    $data     The data to pass to the view
     * @return void
     */
    public static function view($uri, $view, $data = [])
    {
        self::$routes[] = ['method' => 'GET', 'uri' => $uri, 'view' => $view, 'data' => $data];
    }

    /**
     * Add resourceful routes for a controller.
     *
     * Example:
     *     Route::resource('/posts', 'PostController');
     *
     * @param  string   $uri        The base URI for the resource
     * @param  string   $controller The controller handling the resource
     * @return void
     */
    public static function resource($uri, $controller)
    {
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
     * Add a route for redirection.
     *
     * Example:
     *     Route::redirect('/old', '/new', 301);
     *
     * @param  string   $from       The old URI to redirect from
     * @param  string   $to         The new URI to redirect to
     * @param  int      $statusCode The HTTP status code for the redirect (default: 302)
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
     * Add a route for permanent redirection.
     *
     * Example:
     *     Route::permanentRedirect('/old', '/new');
     *
     * @param  string   $from The old URI to redirect from
     * @param  string   $to   The new URI to redirect to
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

        foreach (self::$routes as $route) {
            if (isset($route['domain']) && $route['domain'] !== $domain) {
                continue;
            }

            $params = self::matchUri($route['uri'], $uri);

            if ($params !== false) {
                if ($route['method'] !== $method) {
                    continue;
                }
                if ($method === 'POST' && !isset($_POST['csrf_token'])) {
                    // Handle CSRF token missing for POST requests
                    echo 'CSRF Token is missing for POST request';
                    return;
                }

                // Validate CSRF token
                if ($method === 'POST' && !self::validateCsrfToken($_POST['csrf_token'])) {
                    // Handle invalid CSRF token for POST requests
                    echo 'Invalid CSRF Token for POST request';
                    return;
                }

                if (isset($route['domain'])) {
                    return;
                }

                if (isset($route['redirect'])) {
                    // Handle redirect
                    $statusCode = isset($route['statusCode']) ? $route['statusCode'] : 302;
                    header("Location: {$route['redirect']}", true, $statusCode);
                    exit;
                }
                
                if (isset($route['view'])) {
                    // Render the view
                    self::renderView($route['view'], $route['data']);
                    return;
                } elseif (is_callable($route['callback'])) {
                    // Call the callback function with the parameters
                    echo call_user_func_array($route['callback'], $params);
                    return;
                } elseif (is_array($route['callback']) && count($route['callback']) === 2 && is_string($route['callback'][0])) {
                    // Handle controller-based route
                    $controllerClass = $route['callback'][0];
                    $controllerMethod = $route['callback'][1];

                    if (class_exists($controllerClass)) {
                        $controllerInstance = new $controllerClass();

                        if (method_exists($controllerInstance, $controllerMethod)) {
                            echo call_user_func_array([$controllerInstance, $controllerMethod], $params);
                            return;
                        } else {
                            echo 'Controller method not found';
                            return;
                        }
                    } else {
                        echo 'Controller class not found';
                        return;
                    }
                } else {
                    // Handle invalid callback
                    echo 'Invalid callback';
                    return;
                }
            }
        }

        // If a fallback route is set, use its content
        if (isset(self::$fallbackRoute) && is_callable(self::$fallbackRoute)) {
            $fallbackContent = call_user_func(self::$fallbackRoute);
        }

        // Handle 404 Not Found
        echo $fallbackContent ? $fallbackContent : '404 Not Found';
    }

    /**
     * Match requested URI with route URI and extract parameters.
     *
     * @param  string $routeUri      The URI pattern defined in the route
     * @param  string $requestedUri  The URI requested by the user
     * @return mixed                 An array of parameters if matched, false otherwise
     */
    private static function matchUri($routeUri, $requestedUri)
    {
        $routeSegments = explode('/', trim($routeUri, '/'));
        $requestedSegments = explode('/', trim($requestedUri, '/'));

        if (count($routeSegments) !== count($requestedSegments)) {
            return false;
        }

        $params = [];

        for ($i = 0; $i < count($routeSegments); $i++) {
            $routeSegment = $routeSegments[$i];
            $requestedSegment = $requestedSegments[$i];

            if (strpos($routeSegment, '{') !== false && strpos($routeSegment, '}') !== false) {
                $paramName = trim($routeSegment, '{}');
                $params[$paramName] = $requestedSegment;
            } elseif ($routeSegment !== $requestedSegment) {
                return false;
            }
        }
        return $params;
    }

    /**
     * Render a view with provided data.
     *
     * @param  string $view The name of the view to render
     * @param  array  $data The data to pass to the view
     * @return void
     */
    private static function renderView($view, $data)
    {
        $viewFilePath = __DIR__ . "/views/{$view}.php";
        
        if (file_exists($viewFilePath)) {
            extract($data);
            include $viewFilePath;
        } else {
            echo 'View not found';
        }
    }

    /**
     * Set the fallback route content.
     *
     * @param  callable $callback The callback for the fallback route
     * @return void
     */
    public static function fallback($callback)
    {
        self::$fallbackRoute = $callback;
    }

    /**
     * Validate CSRF token.
     *
     * @param  string $token The CSRF token to validate
     * @return bool   True if the token is valid, false otherwise
     */
    private static function validateCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
    }

}

// Register the handleRequest method to be called on script shutdown
register_shutdown_function(['NetBhaari\NetLib\Routing\Route', 'handleRequest']);