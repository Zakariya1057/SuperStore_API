<?php

namespace App\Exceptions;

use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
        'remember_token'
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    
     // public function render($request, Throwable $exception)
    // {
    //     return parent::render($request, $exception);
    // }

    public function render($request, $exception){
        if($exception instanceof QueryException || $exception instanceof ErrorException || $exception instanceOf MassAssignmentException){
            return parent::render($request, $exception);
        } elseif($exception instanceof AuthenticationException) {
            return response()->json([ 'data' => ['error' => 'User Unauthenticated.'] ], 401);
        } elseif($exception instanceof ValidationException){
            $messages = [];
            foreach(array_values($exception->errors()) as $error){
                $messages[] = str_replace('data.','',$error[0]);
            }
            return response()->json([ 'data' => ['error' => join(' ',$messages)]], 422);
        } elseif (!($exception instanceof NotFoundHttpException) && $request->isJson()) {
            return response()->json([ 'data' => ['error' => $exception->getMessage()] ], $exception->getCode());
        }  else {
            return parent::render($request, $exception);
        }
    }

}
