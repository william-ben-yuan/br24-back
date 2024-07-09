<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Bitrix24Repository;

class Bitrix24Controller extends Controller
{
    private $bitrix24Repository;

    public function __construct(Bitrix24Repository $bitrix24Repository)
    {
        $this->bitrix24Repository = $bitrix24Repository;
    }

    public function redirectToProvider()
    {
        return $this->bitrix24Repository->redirectToProvider();
    }

    public function handleProviderCallback(Request $request)
    {
        return $this->bitrix24Repository->handleProviderCallback($request);
    }
}
