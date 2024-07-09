<?php

namespace App\Http\Controllers;

use App\Repositories\Bitrix24\BaseRepository;
use Illuminate\Http\Request;

class Bitrix24Controller extends Controller
{
    private $bitrix24Repository;

    public function __construct(BaseRepository $bitrix24Repository)
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
