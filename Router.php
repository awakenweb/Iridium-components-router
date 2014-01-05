<?php

/*
 * The MIT License
 *
 * Copyright 2013 Mathieu.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Iridium\Components\Router;

use Iridium\Components\HttpStack\Request;

class Router implements RouterInterface
{

    /**
     * @var Request\RequestInterface
     */
    protected $request;
    protected $routes = array();
    protected $tokens = array();

    /**
     *
     * @param \Iridium\Http\Request\RequestInterface $request
     */
    public function __construct(Request\RequestInterface $request)
    {
        $this->request = $request;
        $this->tokens  = array(
            ':string' => '([a-zA-Z]+)' ,
            ':number' => '([0-9]+)' ,
            ':slug'   => '([a-zA-Z0-9-_]+)' ,
            ':date'   => '([0-9]{2}-[0-9]}{2}-[0-9]{4})'
        );
    }

    /**
     * compares the request to all defined routes and search for a matching
     * handller.
     *
     * Lazy loads the required handler if a class name has been provided instead of an object
     *
     * @return array|null
     */
    public function match()
    {
        $uri = $this->request->getPathInfo();

        $found_handller = null;
        $params         = array();

        if ( isset( $this->routes[ $uri ] ) ) { // is it a fixed URI without parameters?
            $found_handller = $this->routes[ $uri ];
        } elseif ($this->routes) { // is it a variable URI with parameters?
            foreach ($this->routes as $pattern => $handller) {
                $pattern = strtr( $pattern , $this->tokens );
                if ( preg_match( '#^/?' . $pattern . '/?$#' , $uri , $matches ) ) {
                    $found_handller = $handller;
                    unset( $matches[ 0 ] );
                    $params         = $matches;
                    break;
                }
            }
        }
        if ( is_array( $found_handller ) && is_string( $found_handller[ 0 ] ) ) {
            $classname = $found_handller[ 0 ];
            if ( class_exists( $classname , true ) ) {
                $found_handller[ 0 ] = new $classname();
            } else {
                $found_handller = null;
            }
        }

        return array( 'callback' => $found_handller , 'parameters' => $params );
    }

    /**
     * define a route. The route follows a pattern that is later matched to
     * request URI
     *
     * @param string         $pattern
     * @param callable|array $handler
     *
     * @return Iridium\Components\Router\RouterInterface
     */
    public function defineRoute($pattern , $handler)
    {
        $this->routes[ $pattern ] = $handler;

        return $this;
    }

    /**
     * bulk define an array of routes
     *
     * @param array $routes
     *
     * @return Iridium\Components\Router\RouterInterface
     */
    public function defineMultipleRoutes(array $routes)
    {
        foreach ($routes as $pattern => $handlerName) {
            $this->defineRoute( $pattern , $handlerName );
        }

        return $this;
    }

}
