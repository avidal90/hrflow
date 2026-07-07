<?php

namespace App\Http\Controllers\Portal;

use App\Enums\LeaveRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Festivo;
use App\Models\LeaveRequest;
use App\Models\TurnoAssignment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalCalendarEventsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $validated = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
        ]);

        $start = CarbonImmutable::parse($validated['start'] ?? now()->startOfMonth());
        $end = CarbonImmutable::parse($validated['end'] ?? now()->endOfMonth());

        $events = collect();

        // Festivos del tenant en el rango visible
        Festivo::query()
            ->where('tenant_id', $user->tenant_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->each(function (Festivo $festivo) use ($events): void {
                $events->push([
                    'id' => 'festivo-'.$festivo->id,
                    'title' => 'Festivo',
                    'start' => $festivo->date->toDateString(),
                    'allDay' => true,
                    'backgroundColor' => '#fef3c7',
                    'borderColor' => '#f59e0b',
                    'textColor' => '#92400e',
                ]);
            });

        // Solicitudes de ausencia aprobadas del empleado
        LeaveRequest::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('status', LeaveRequestStatus::Approved->value)
            ->where(function ($q) use ($start, $end): void {
                $q->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('end_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q) use ($start, $end): void {
                        $q->where('start_date', '<=', $start->toDateString())
                            ->where('end_date', '>=', $end->toDateString());
                    });
            })
            ->get()
            ->each(function (LeaveRequest $leave) use ($events): void {
                $events->push([
                    'id' => 'leave-'.$leave->id,
                    'title' => $leave->request_type->label(),
                    'start' => $leave->start_date->toDateString(),
                    'end' => $leave->end_date->addDay()->toDateString(),
                    'allDay' => true,
                    'backgroundColor' => '#d1fae5',
                    'borderColor' => '#10b981',
                    'textColor' => '#065f46',
                ]);
            });

        // Turnos asignados al empleado con solapamiento en el rango visible
        TurnoAssignment::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->with('turno')
            ->where(function ($q) use ($start, $end): void {
                $q->where(function ($q) use ($end): void {
                    $q->whereNull('valid_from')
                        ->orWhereDate('valid_from', '<=', $end->toDateString());
                })->where(function ($q) use ($start): void {
                    $q->whereNull('valid_until')
                        ->orWhereDate('valid_until', '>=', $start->toDateString());
                });
            })
            ->get()
            ->each(function (TurnoAssignment $assignment) use ($start, $end, $events): void {
                $turno = $assignment->turno;

                $validFrom = $assignment->valid_from ? CarbonImmutable::parse((string) $assignment->valid_from) : null;
                $validUntil = $assignment->valid_until ? CarbonImmutable::parse((string) $assignment->valid_until) : null;

                $eventStart = ($validFrom && $validFrom->gt($start))
                    ? $validFrom->toDateString()
                    : $start->toDateString();

                $eventEnd = $validUntil
                    ? $validUntil->addDay()->toDateString()
                    : $end->addDay()->toDateString();

                $title = $turno
                    ? $turno->name.' · '.substr((string) $turno->start_time, 0, 5).'-'.substr((string) $turno->end_time, 0, 5)
                    : 'Turno asignado';

                $baseEvent = [
                    'id' => 'turno-'.$assignment->id,
                    'title' => $title,
                    'allDay' => true,
                    'backgroundColor' => '#dbeafe',
                    'borderColor' => '#93c5fd',
                    'textColor' => '#1e40af',
                ];

                if ($turno && ! $turno->includes_weekends) {
                    $events->push(array_merge($baseEvent, [
                        'startRecur' => $eventStart,
                        'endRecur' => $eventEnd,
                        'daysOfWeek' => [1, 2, 3, 4, 5],
                    ]));
                } else {
                    $events->push(array_merge($baseEvent, [
                        'start' => $eventStart,
                        'end' => $eventEnd,
                    ]));
                }
            });

        return response()->json($events->values());
    }
}
