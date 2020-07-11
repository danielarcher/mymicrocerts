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

use Illuminate\Support\Facades\Route;
use Laravel\Lumen\Routing\Router;

$router->get('/', function () use ($router) {
    return 'Welcome to MyMicroCerts ' . config('mycerts.version');
});

$router->group(['middleware' => 'jsonApiContentType'], function (Router $router) {
    /**
     * Login Url
     */
    $router->get('/login', 'LoginController@login');

    /**
     * External exam with guest user
     */
    $router->get('link/exam/{id}', ['as' => 'external.index', 'uses' => 'ExternalExamController@index']);
    $router->post('link/exam/{id}/start', ['as' => 'external.start', 'uses' => 'ExternalExamController@start']);
    $router->post('link/exam/{id}/finish', ['as' => 'external.finish', 'uses' => 'ExternalExamController@finish']);
});

$router->group(['prefix' => 'api', 'middleware' => 'jsonApiContentType'], function (Router $router) {
    /**
     * No Authentication needed
     */
    $router->post('guest-candidate','CandidateController@createGuest');
    $router->get('plans', 'PlansController@list');
    $router->get('plans/{id}', 'PlansController@findOne');

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
            $router->delete('company/{id}', 'CompaniesController@delete');
            $router->post('plans', 'PlansController@create');
            $router->delete('plans/{id}', 'PlansController@delete');

        });
        /**
         * Company Owner only
         */
        $router->group(['middleware' => 'companyOwner'], function(Router $router) {
            $router->post('exam', 'ExamController@create');
            $router->delete('exam/{id}', 'ExamController@delete');

            $router->get('candidate', 'CandidateController@list');
            $router->post('candidate', 'CandidateController@create');
            $router->get('candidate/{id}', 'CandidateController@findOne');
            $router->delete('candidate/{id}', 'CandidateController@delete');

            $router->get('category', 'CategoryController@list');
            $router->post('category', 'CategoryController@create');
            $router->get('category/{id}', 'CategoryController@findOne');
            $router->delete('category/{id}', 'CategoryController@delete');

            $router->post('plans/{id}/buy', 'PlansController@buy');

            $router->get('question', 'QuestionController@list');
            $router->post('question', 'QuestionController@create');
            $router->get('question/{id}', 'QuestionController@findOne');
            $router->delete('question/{id}', 'QuestionController@delete');
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


    });
});
