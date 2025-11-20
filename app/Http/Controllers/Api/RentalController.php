<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class RentalController extends Controller
{
    use AuthorizesRequests;
    protected RentalService $service;

    public function __construct(RentalService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        if ($request->user()->is_admin) {
            // همه سفارش‌ها
            $rentals = Rental::with(['user', 'items.book'])->latest()->get();
            return response()->json($rentals);
        }
        // فقط سفارش‌های خود کاربر
        $rentals = $request->user()->rentals()->with('items.book')->paginate(10);
        return response()->json($rentals);
    }

    public function store(Request $request)
    {
        try {
            $payload = $request->validate([
                'start_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:start_date',
                'items' => 'required|array|min:1',
                'items.*.book_id' => 'required|integer|exists:books,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);
            $payload['user_id'] = $request->user()->id;

            $rental = $this->service->createRental($payload);
            return response()->json(['success' => true, 'data' => $rental], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function show(Rental $rental)
    {
        $this->authorize('view', $rental); // policy: ensure only owner/admin can view
        return response()->json($rental->load('items.book'));
    }

    public function return(Request $request, Rental $rental)
    {
        try {
            $this->authorize('update', $rental);
            $returned = $this->service->returnRental($rental, $request->input('returned_at'));
            return response()->json(['success' => true, 'data' => $returned]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
        }
    }
}
