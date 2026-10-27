<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVisitStatusRequest;
use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Http\RedirectResponse;

final class VisitStatusController
{
    public function update(UpdateVisitStatusRequest $request, Clinic $clinic, VisitStatus $visitStatus): RedirectResponse
    {
        $visitStatus->update($request->validated());

        return back();
    }
}
