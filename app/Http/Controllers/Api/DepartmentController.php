<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
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

        $query = Department::query()->with(['tenant', 'manager.user'])->visibleTo($user);

        if ($user->isDepartmentManager() && ! $user->isCompanyAdmin() && ! $user->isHr()) {
            $managerEmployeeId = $user->employee?->getKey();

            if ($managerEmployeeId === null) {
                return response()->json([]);
            }

            $query->where('manager_employee_id', $managerEmployeeId);
        }

        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = Department::create($request->validated());

        return response()->json($department->fresh(['tenant', 'manager.user']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): JsonResponse
    {
        Gate::authorize('view', $department);

        return response()->json($department->load(['tenant', 'manager.user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department->update($request->validated());

        return response()->json($department->fresh(['tenant', 'manager.user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): JsonResponse
    {
        Gate::authorize('delete', $department);

        $department->delete();

        return response()->json(status: 204);
    }
}
