<?php

use Envor\Datastore\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function createRequest($method, $uri) : \Illuminate\Http\Request
{

    $symphonyRequest = \Symfony\Component\HttpFoundation\Request::create($uri, $method);

    return \Illuminate\Http\Request::createFromBase($symphonyRequest);
}