<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Department::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->resolveTenantId();

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
                Rule::unique('departments', 'name')->where(
                    fn (Builder $query): Builder => $query->where('tenant_id', $tenantId),
                ),
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
        if (! ($this->user()?->isSuperAdmin() ?? false)) {
            $this->merge([
                'tenant_id' => $this->user()?->tenant_id,
            ]);
        }
    }

    private function resolveTenantId(): string
    {
        return (string) ($this->input('tenant_id') ?? $this->user()->tenant_id ?? '');
    }
}
