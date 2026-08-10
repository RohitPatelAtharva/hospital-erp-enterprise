<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\MasterData;

use App\Http\Requests\BaseFormRequest;

/** Create organization relationship (10-API §10). Organizations:update permission. */
final class CreateOrganizationRelationshipRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('organizations:update') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'related_org_id' => ['required', 'uuid'],
            'relation_type_id' => ['required', 'uuid'],
        ];
    }
}
