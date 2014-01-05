<?php

namespace Iridium\Components\Router\tests\units;

require_once __DIR__ . '/../../vendor/autoload.php';

use atoum ,
    Iridium\Components\Router\Router as IrRouter;

class Router extends atoum
{

    public function testMatchDirectRoute()
    {
        $_SERVER[ 'REQUEST_URI' ]    = '/test';
        $_SERVER[ 'REQUEST_METHOD' ] = 'GET';
        $reqMock                     = new \mock\Iridium\Components\HttpStack\Request();

        $router = new IrRouter( $reqMock );
        $router->defineRoute( '/test' , function () {
            echo "test";
        } );

        $result = $router->match();

        $this->mock( $reqMock )
                ->wasCalled()
                ->array( $result )
                ->variable( $result[ 'callback' ] )
                ->isCallable()
                ->output( function () use ($result) {
                    call_user_func_array( $result[ 'callback' ] , $result[ 'parameters' ] );
                } )
                ->isEqualTo( 'test' );
    }

    public function testMatchRouteWithParams()
    {
        $_SERVER[ 'REQUEST_URI' ]    = '/test/test';
        $_SERVER[ 'REQUEST_METHOD' ] = 'GET';
        $reqMock                     = new \mock\Iridium\Components\HttpStack\Request();

        $router = new IrRouter( $reqMock );
        $router->defineRoute( '/test/:slug' , function ($slug) {
            echo $slug;
        } );

        $result = $router->match();

        $this->mock( $reqMock )
                ->wasCalled()
                ->array( $result )
                ->variable( $result[ 'callback' ] )
                ->isCallable()
                ->output( function () use ($result) {
                    call_user_func_array( $result[ 'callback' ] , $result[ 'parameters' ] );
                } )
                ->isEqualTo( 'test' );
    }

    public function testMatchRouteWithMultipleRoutes()
    {
        $_SERVER[ 'REQUEST_URI' ]    = '/test/test';
        $_SERVER[ 'REQUEST_METHOD' ] = 'GET';
        $reqMock                     = new \mock\Iridium\Components\HttpStack\Request();

        $router = new IrRouter( $reqMock );

        $slugfunction = function ($slug) {
            echo $slug;
        };

        $basicfunction = function () {
            echo 'another test';
        };

        $datefunction = function ($date) {
            echo $date;
        };

        $router->defineMultipleRoutes(
                [
                    '/test/:slug' => $slugfunction ,
                    'test'        => $basicfunction ,
                    '/test/:date' => $datefunction
                ]
        );

        $this->when( $result = $router->match() )
                ->mock( $reqMock )
                ->wasCalled()
                ->array( $result )
                ->variable( $result[ 'callback' ] )
                ->isCallable()
                ->output( function () use ($result) {
                    call_user_func_array( $result[ 'callback' ] , $result[ 'parameters' ] );
                } )
                ->isEqualTo( 'test' );

        $this->when( $_SERVER[ 'REQUEST_URI' ] = '/test' )
                ->and( $result2                  = $router->match() )
                ->variable( $result2[ 'callback'] )
                ->isCallable()
                ->output( function () use ($result2) {
                    call_user_func_array( $result2[ 'callback' ] , $result2[ 'parameters' ] );
                } )
                ->isEqualTo( 'another test' );

        $this->when( $_SERVER[ 'REQUEST_URI' ] = '/test/01-01-2014' )
                ->and( $result3                  = $router->match() )
                ->variable( $result3[ 'callback' ] )
                ->isCallable()
                ->output( function () use ($result3) {
                    call_user_func_array( $result3[ 'callback' ] , $result3[ 'parameters' ] );
                } )
                ->isEqualTo( '01-01-2014' );
    }

}
