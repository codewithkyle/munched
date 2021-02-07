<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(["prefix" => "v1"], function() use ($router) {
    $router->post("register", "AuthController@register");
    $router->post("login", "AuthController@login");
    $router->get("verify-email", "AuthController@verifyEmail");
    $router->post("forgot-password", "AuthController@forgotPassword");
    $router->post("reset-password", "AuthController@resetPassword");

    $router->group(["middleware" => "user"], function() use ($router) {
        $router->post("refresh-token", "AuthController@refreshToken");
        $router->post("resend-verification-email", "AuthController@resendVerificationEmail");
        $router->post("logout", "AuthController@logout");
    });

    $router->group(["prefix" => "user", "middleware" => "auth"], function() use ($router) {
        $router->get("profile", "UserController@profile");
        $router->post("profile", "UserController@updateProfile");
        $router->post("update-email", "UserController@updateEmail");
        $router->post("update-password", "AuthController@updatePassword");
    });

    $router->group(["prefix" => "admin", "middleware" => ["admin"]], function() use($router) {
        $router->get("verify", "AdminController@verify");
        $router->get("users", "AdminController@getUsers");
    });
});

