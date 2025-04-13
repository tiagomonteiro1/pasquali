<?php
namespace App\Http\Controllers;

use App\Models\TravelOrder;
use App\Notifications\TravelOrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TravelOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->travelOrders()->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('destination')) {
            $query->where('destination', 'like', '%'.$request->destination.'%');
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
        }

        return $query->paginate(15);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $order = $request->user()->travelOrders()->create([
            'destination' => $validated['destination'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'requested',
        ]);

        return response()->json($order, 201);
    }

    public function show(Request $request, TravelOrder $order)
    {
        $this->authorize('view', $order);
        return $order;
    }

    public function updateStatus(Request $request, TravelOrder $order)
    {
        $this->authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => 'required|in:approved,canceled',
            'reason' => 'nullable|string|required_if:status,canceled',
        ]);

        $order->update([
            'status' => $validated['status'],
            'reason' => $validated['reason'] ?? $order->reason,
        ]);

        // Notify user about status change
        Notification::send($order->user, new TravelOrderStatusChanged($order));

        return $order;
    }

    public function cancel(Request $request, TravelOrder $order)
    {
        $this->authorize('cancel', $order);

        if (!$order->canBeCancelled()) {
            return response()->json([
                'message' => 'This order cannot be canceled as it is either not approved or the start date is too close',
            ], 422);
        }

        $order->update([
            'status' => 'canceled',
            'reason' => $request->reason ?? 'Canceled by user',
        ]);

        // Notify user about cancellation
        Notification::send($order->user, new TravelOrderStatusChanged($order));

        return $order;
    }
}