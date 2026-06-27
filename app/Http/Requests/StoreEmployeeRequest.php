<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Employee::class) ?? false;
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
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists(User::class, 'id')->where(
                    fn (Builder $query): Builder => $query->where('tenant_id', $tenantId),
                ),
                Rule::unique('employees', 'user_id'),
            ],
            'department_id' => [
                'required',
                'integer',
                Rule::exists(Department::class, 'id')->where(
                    fn (Builder $query): Builder => $query->where('tenant_id', $tenantId),
                ),
            ],
            'employee_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees', 'employee_code')->where(
                    fn (Builder $query): Builder => $query->where('tenant_id', $tenantId),
                ),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'hire_date' => ['required', 'date'],
            'employment_status' => ['required', 'string', Rule::in(['active', 'inactive', 'on_leave', 'terminated'])],
            'job_title' => ['nullable', 'string', 'max:255'],
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
        return (string) ($this->input('tenant_id') ?? $this->user()?->tenant_id ?? '');
    }
}
