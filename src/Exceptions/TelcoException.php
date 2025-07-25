<?php

namespace Aislandener\Telco\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TelcoException extends Exception
{
    public function render(Request $request): Response
    {
        return response([
            'notify' => true,
            'message' => $this->message,
        ], $this->code === 200 ? 400 : ($this->code ?? 422));
    }
}
