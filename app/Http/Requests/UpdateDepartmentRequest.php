<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Department $department */
        $department = $this->route('department');

        return $this->user()?->can('update', $department) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Department $department */
        $department = $this->route('department');

        $tenantId = $this->resolveTenantId($department);

        return [
            'tenant_id' => [
                Rule::requiredIf($this->user()?->isSuperAdmin() ?? false),
                'string',
                'exists:tenants,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')
                    ->where(fn (Builder $query): Builder => $query->where('tenant_id', $tenantId))
                    ->ignore($department->getKey()),
            ],
            'manager_user_id' => [
                'nullable',
                'integer',
                Rule::exists(User::class, 'id')->where(
                    fn (Builder $query): Builder => $query->where('tenant_id', $tenantId),
                ),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        /** @var Department $department */
        $department = $this->route('department');

        if (! ($this->user()?->isSuperAdmin() ?? false)) {
            $this->merge([
                'tenant_id' => $department->tenant_id,
            ]);
        }
    }

    private function resolveTenantId(Department $department): string
    {
        return (string) ($this->input('tenant_id') ?? $department->tenant_id ?? $this->user()->tenant_id ?? '');
    }
}
