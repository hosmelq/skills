<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;
use Closure;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Stringable;

final class UpdateVisitStatusRequest extends FormRequest
{
    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->has('stage')) {
                    return;
                }

                $visitStatus = $this->route('visit_status');

                assert($visitStatus instanceof VisitStatus);

                if (
                    ! $visitStatus->is_initial
                    || $this->enum('stage', VisitStage::class) === VisitStage::CheckedIn
                ) {
                    return;
                }

                $validator->errors()->add(
                    'stage',
                    trans()->string('visit_status.validation.initial_must_be_checked_in'),
                );
            },
        ];
    }

    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(
        #[RouteParameter('clinic')] Clinic $clinic,
        #[RouteParameter('visit_status')] VisitStatus $visitStatus,
    ): array {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique(VisitStatus::class)
                    ->ignore($visitStatus)
                    ->where('clinic_id', $clinic->id)
                    ->withoutTrashed(),
            ],
            'stage' => ['sometimes', 'required', Rule::enum(VisitStage::class)],
        ];
    }
}
