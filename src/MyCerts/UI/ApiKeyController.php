<?php

namespace MyCerts\UI;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MyCerts\Application\ApiKeyHandler;
use Ramsey\Uuid\Uuid;

class ApiKeyController extends BaseController
{
    /**
     * @var ApiKeyHandler
     */
    private ApiKeyHandler $handler;

    public function __construct(ApiKeyHandler $handler)
    {
        $this->handler = $handler;
    }

    public function create(Request $request)
    {
        return $this->handler->create($this->retrieveCompany($request)->id, $request->json('name', Uuid::uuid6()));
    }

    public function list(Request $request)
    {
        return $this->handler->list($this->retrieveCompany($request)->id);
    }

    public function revoke(string $id, Request $request)
    {
        $this->handler->revoke($id, $this->retrieveCompany($request)->id);

        return response(null, Response::HTTP_NO_CONTENT);
    }
}