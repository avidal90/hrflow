<?php

namespace App\Http\Requests;

use App\Models\Department;
use App\Models\User;
use App\Support\Validation\PasswordRules;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->route('user');

        return $this->user()?->can('update', $user) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        $tenantId = $this->resolveTenantId($user);

        return [
            'tenant_id' => [
                Rule::requiredIf($this->user()?->isSuperAdmin() ?? false),
                'string',
                'exists:tenants,id',
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
                Rule::unique('users', 'employee_code')
                    ->where(fn (Builder $query): Builder => $query->where('tenant_id', $tenantId))
                    ->ignore($user->getKey()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'password' => ['nullable', 'string', PasswordRules::user()],
            'hire_date' => ['required', 'date'],
            'employment_status' => ['required', 'string', Rule::in(['active', 'inactive', 'on_leave', 'terminated'])],
            'annual_vacation_days' => ['required', 'integer', 'min:0', 'max:365'],
            'job_title' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        /** @var User $user */
        $user = $this->route('user');

        if (! ($this->user()?->isSuperAdmin() ?? false)) {
            $this->merge([
                'tenant_id' => $user->tenant_id,
            ]);
        }
    }

    private function resolveTenantId(User $user): string
    {
        return (string) ($this->input('tenant_id') ?? $user->tenant_id ?? $this->user()?->tenant_id ?? '');
    }
}
