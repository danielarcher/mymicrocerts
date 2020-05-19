<?php

/** @var Router $router */

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

use Laravel\Lumen\Routing\Router;

$router->get('/', function () use ($router) {
    return 'Welcome to MyMicroCerts ' . config('mycerts.version');
});

/**
 * Login Url
 */
$router->get('/login', 'LoginController@login');

$router->group(['prefix' => 'api'], function (Router $router) {
    /**
     * No Authentication needed
     */
    $router->post('guest-candidate','CandidateController@createGuest');
    $router->get('plans', 'PlansController@list');
    #$router->get('plans/{id}', 'PlansController@findOne');

    /**
     * Authentication Required
     */
    $router->group(['middleware' => 'auth'], function(Router $router) {
        /**
         * Admin only
         */
        $router->group(['middleware' => 'admin'], function(Router $router) {
            $router->get('company', 'CompaniesController@list');
            $router->post('company', 'CompaniesController@create');
            $router->post('plans', 'PlansController@create');
            $router->get('candidate', 'CandidateController@list');
        });
        /**
         * Company Owner only
         */
        $router->group(['middleware' => 'companyOwner'], function(Router $router) {
            $router->post('exam', 'ExamController@create');
            $router->get('company/{id}/candidates', 'CandidateController@listPerCompany');
            $router->post('candidate', 'CandidateController@create');
            $router->post('question', 'QuestionController@create');
            $router->get('candidate/{id}', 'CandidateController@findOne');
            $router->post('plans/{id}/buy', 'PlansController@buy');
        });

        /**
         * Logged user routes
         */
        $router->get('me', 'CandidateController@findMe');

        $router->get('company/{id}', 'CompaniesController@findOne');
        $router->get('exam', 'ExamController@list');

        $router->get('exam/{id}', 'ExamController@findOne');
        $router->post('exam/{id}/start', 'ExamController@start');
        $router->post('exam/{id}/finish', 'ExamController@finish');

        $router->get('question/{id}', 'QuestionController@findOne');
    });
});
