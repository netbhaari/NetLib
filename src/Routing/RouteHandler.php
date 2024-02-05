<?php

namespace NetBhaari\NetLib\Routing;

/**
 * Class RouteHandler
 * 
 * Responsible for handling the logic of incoming HTTP requests.
 */
class RouteHandler
{
    private $routes;          // Array of defined routes
    private $fallbackRoute;   // Callback for the fallback route
    private $routePrefix;      // Prefix for routes

    /**
     * RouteHandler constructor.
     *
     * @param array  $routes        Array of defined routes
     * @param mixed  $fallbackRoute Callback for the fallback route
     * @param string $routePrefix   Prefix for routes
     */
    public function __construct($routes, $fallbackRoute, $routePrefix)
    {
        $this->routes = $routes;
        $this->fallbackRoute = $fallbackRoute;
        $this->routePrefix = $routePrefix;
    }

    /**
     * Handle incoming HTTP requests.
     *
     * @param  string $method            The HTTP method of the request
     * @param  string $uri               The URI pattern for the route
     * @param  string $domain            The domain of the request
     * @param  string $fallbackContent   Content to be used in case of fallback
     * @return void
     */
    public function handleRequest($method, $uri, $domain, &$fallbackContent)
    {
        // Iterate through defined routes
        foreach ($this->routes as $route) {
            // Check if the route's domain matches the request's domain
            if (isset($route['domain']) && $route['domain'] !== $domain) {
                continue;  // Skip to the next iteration if the domain doesn't match
            }

            // Example: Call the matchUri method from RouteParser
            $params = RouteParser::matchUri($route['uri'], $uri);

            // Check if the URI matches the route pattern and extract parameters
            if ($params !== false) {
                // Check if the HTTP method matches
                if ($route['method'] !== $method) {
                    continue;  // Skip to the next iteration if the method doesn't match
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
                
                // Check if the route has a domain set
                if (isset($route['domain'])) {
                    return;  // If the route has a domain set, return without further processing
                }

                // Check if the route is a redirect
                if (isset($route['redirect'])) {
                    // Handle redirect
                    $statusCode = isset($route['statusCode']) ? $route['statusCode'] : 302;
                    header("Location: {$route['redirect']}", true, $statusCode);
                    exit;  // Exit script after performing the redirect
                }

                // Check if the route is a view
                if (isset($route['view'])) {
                    // Example: Call the renderView method from RouteHandler
                    $this->renderView($route['view'], $route['data']);
                    return;  // Return after rendering the view
                } 
                // Check if the route callback is callable
                elseif (is_callable($route['callback'])) {
                    // Execute the callback function with the parameters
                    echo call_user_func_array($route['callback'], $params);
                    return;  // Return after executing the callback
                } 
                // Check if the route callback is a controller-based route
                elseif (is_array($route['callback']) && count($route['callback']) === 2 && is_string($route['callback'][0])) {
                    // Handle controller-based route
                    $controllerClass = $route['callback'][0];
                    $controllerMethod = $route['callback'][1];

                    // Check if the controller class exists
                    if (class_exists($controllerClass)) {
                        $controllerInstance = new $controllerClass();

                        // Check if the controller method exists
                        if (method_exists($controllerInstance, $controllerMethod)) {
                            // Execute the controller method with the parameters
                            echo call_user_func_array([$controllerInstance, $controllerMethod], $params);
                            return;  // Return after executing the controller method
                        } else {
                            echo error('Controller method not found');
                            return;  // Return if the controller method is not found
                        }
                    } else {
                        error('Controller class not found');
                        return;  // Return if the controller class is not found
                    }
                } else {
                    echo error('Invalid callback');
                    return;  // Return if the callback is invalid
                }
            }
        }

        // Example: Call the matchUri method from RouteParser
        if (isset($this->fallbackRoute) && is_callable($this->fallbackRoute)) {
            // Execute the fallback callback and store the result in fallbackContent
            $fallbackContent = call_user_func($this->fallbackRoute);
        }

        // Handle 404 Not Found
        echo $fallbackContent ? $fallbackContent : error('404 Page Not Found');
    }

    /**
     * Render a view with provided data.
     *
     * @param  string $view The name of the view to render
     * @param  array  $data The data to pass to the view
     * @return void
     */
    private function renderView($view, $data)
    {
        // Build the file path for the view
        $viewFilePath = __DIR__ . "/views/{$view}.php";

        // Check if the view file exists
        if (file_exists($viewFilePath)) {
            // Extract data variables and include the view file
            extract($data);
            include $viewFilePath;
        } else {
            echo 'View not found';  // Output an error message if the view file is not found
        }
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