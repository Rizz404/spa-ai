<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Spa;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ResolvesCurrentSpa
{
    protected function currentSpa(Request $request): Spa
    {
        $spa = $request->user()->currentSpa();

        if (! $spa) {
            throw new HttpException(403, 'No spa is linked to this account yet.');
        }

        return $spa;
    }
}
