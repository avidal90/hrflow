<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(401);
        }

        $query = Employee::query()->with(['tenant', 'department', 'user'])->visibleTo($user);

        if ($user->isDepartmentManager() && ! $user->isCompanyAdmin() && ! $user->isHr()) {
            $managerEmployeeId = $user->employee?->getKey();

            if ($managerEmployeeId === null) {
                return response()->json([]);
            }

            $query->whereHas('department', function (Builder $departmentQuery) use ($managerEmployeeId): void {
                $departmentQuery->where('manager_employee_id', $managerEmployeeId);
            });
        }

        return response()->json($query->orderBy('last_name')->orderBy('first_name')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::create($request->validated());

        return response()->json($employee->fresh(['tenant', 'department', 'user']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): JsonResponse
    {
        Gate::authorize('view', $employee);

        return response()->json($employee->load(['tenant', 'department', 'user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validated());

        return response()->json($employee->fresh(['tenant', 'department', 'user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        Gate::authorize('delete', $employee);

        $employee->delete();

        return response()->json(status: 204);
    }
}
