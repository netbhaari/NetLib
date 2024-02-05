<?php

namespace NetBhaari\NetLib\Routing;

/**
 * Class RouteParser
 * 
 * Responsible for parsing routes and matching requested URIs.
 */
class RouteParser
{
    /**
     * Match requested URI with route URI and extract parameters.
     *
     * @param  string $routeUri      The URI pattern defined in the route
     * @param  string $requestedUri  The URI requested by the user
     * @return mixed                 An array of parameters if matched, false otherwise
     */
    public static function matchUri($routeUri, $requestedUri)
    {
        // Split the route and requested URIs into segments
        $routeSegments = explode('/', trim($routeUri, '/'));
        $requestedSegments = explode('/', trim($requestedUri, '/'));

        // Check if the number of segments in both URIs match
        if (count($routeSegments) !== count($requestedSegments)) {
            return false;  // Return false if the number of segments doesn't match
        }

        $params = [];  // Initialize an array to store parameters

        // Iterate through each segment of the route
        for ($i = 0; $i < count($routeSegments); $i++) {
            $routeSegment = $routeSegments[$i];
            $requestedSegment = $requestedSegments[$i];

            // Check if the route segment contains curly braces indicating a parameter
            if (strpos($routeSegment, '{') !== false && strpos($routeSegment, '}') !== false) {
                $paramName = trim($routeSegment, '{}');  // Extract parameter name
                $params[$paramName] = $requestedSegment;  // Assign parameter value
            } 
            // Check if the route segment doesn't match the requested segment
            elseif ($routeSegment !== $requestedSegment) {
                return false;  // Return false if segments don't match
            }
        }

        return $params;  // Return the array of parameters if all segments match
    }
}