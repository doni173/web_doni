<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Service;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Menambahkan item ke cart
    public function addToCart(Request $request)
    {
        // Pastikan user memiliki cart aktif, jika belum, buatkan cart baru
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        // Jika item adalah produk
        if ($request->has('id_produk')) {
            CartItem::create([
                'cart_id' => $cart->id,
                'id_produk' => $request->id_produk,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'price_after_discount' => $request->price_after_discount,
            ]);
        }

        // Jika item adalah service
        if ($request->has('id_service')) {
            CartItem::create([
                'cart_id' => $cart->id,
                'id_service' => $request->id_service,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'price_after_discount' => $request->price_after_discount,
            ]);
        }

        return response()->json(['success' => true]);
    }

    // Menghapus item dari cart
    public function removeFromCart($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return response()->json(['success' => true]);
    }
}
