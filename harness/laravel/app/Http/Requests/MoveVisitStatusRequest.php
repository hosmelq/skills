<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

final class MoveVisitStatusRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(
        #[RouteParameter('clinic')] Clinic $clinic,
        #[RouteParameter('visit_status')] VisitStatus $visitStatus,
    ): array {
        return [
            'move_after_id' => [
                'nullable',
                Rule::notIn([$visitStatus->id]),
                Rule::exists(VisitStatus::class, 'id')
                    ->where('clinic_id', $clinic->id)
                    ->withoutTrashed(),
            ],
        ];
    }
}
