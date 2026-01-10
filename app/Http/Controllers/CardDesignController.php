<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Option;
use App\Models\Order;
use App\Services\PricingCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardDesignController extends Controller
{
    protected $pricingCalculator;

    public function __construct(PricingCalculator $pricingCalculator)
    {
        $this->pricingCalculator = $pricingCalculator;
    }

    public function design($slug = null)
    {
        $category = Category::where('slug', $slug ?? 'business-cards')
            ->with(['subcategories.options', 'pricings'])
            ->firstOrFail();

        $defaults = [
            'size' => $category->defaultSize,
            'finish' => $category->defaultFinish,
            'corners' => $category->defaultCorners,
        ];

        return view('cards.design', compact('category', 'defaults'));
    }

    public function calculatePrice(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'size' => 'required|exists:options,id',
            'finish' => 'required|exists:options,id',
            'corners' => 'required|exists:options,id',
            'quantity' => 'required|integer|min:50',
        ]);

        $result = $this->pricingCalculator->calculate(
            $request->category_id,
            $request->size,
            $request->finish,
            $request->corners,
            $request->quantity
        );

        return response()->json($result);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'size' => 'required|exists:options,id',
            'finish' => 'required|exists:options,id',
            'corners' => 'required|exists:options,id',
            'quantity' => 'required|integer|min:50',
        ]);

        $category = Category::with(['subcategories.options'])->findOrFail($request->category_id);
        
        $selectedOptions = [
            'size' => Option::find($request->size),
            'finish' => Option::find($request->finish),
            'corners' => Option::find($request->corners),
        ];

        $price = $this->pricingCalculator->calculate(
            $request->category_id,
            $request->size,
            $request->finish,
            $request->corners,
            $request->quantity
        );

        return view('cards.preview', compact('category', 'selectedOptions', 'price', 'request'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'size' => 'required|exists:options,id',
            'finish' => 'required|exists:options,id',
            'corners' => 'required|exists:options,id',
            'quantity' => 'required|integer|min:50',
        ]);

        $category = Category::findOrFail($request->category_id);
        
        $price = $this->pricingCalculator->calculate(
            $request->category_id,
            $request->size,
            $request->finish,
            $request->corners,
            $request->quantity
        );

        // Store in session for checkout
        session([
            'card_design' => [
                'category_id' => $request->category_id,
                'size' => $request->size,
                'finish' => $request->finish,
                'corners' => $request->corners,
                'quantity' => $request->quantity,
                'price_per_card' => $price['price_per_card'],
                'total_price' => $price['total_price'],
            ]
        ]);

        return view('cards.checkout', compact('category', 'price', 'request'));
    }

    public function confirmCheckout(Order $order)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        return view('cards.checkout-confirm', compact('order'));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
            'country' => 'required|string',
            'payment_method' => 'required|in:card,paypal,bank_transfer',
        ]);

        $design = session('card_design');
        
        if (!$design) {
            return redirect()->route('cards.design')->with('error', 'Session expired. Please design your card again.');
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'category_id' => $design['category_id'],
                'selected_options' => [
                    'size' => $design['size'],
                    'finish' => $design['finish'],
                    'corners' => $design['corners'],
                ],
                'quantity' => $design['quantity'],
                'price_per_card' => $design['price_per_card'],
                'total_price' => $design['total_price'],
                'customer_name' => $request->name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'customer_address' => $request->address,
                'customer_city' => $request->city,
                'customer_state' => $request->state,
                'customer_zip_code' => $request->zip_code,
                'customer_country' => $request->country,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            session()->forget('card_design');
            DB::commit();

            // Redirect to payment gateway or success page
            return redirect()->route('cards.success', $order);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to place order. Please try again.');
        }
    }

    public function success(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('cards.success', compact('order'));
    }
}