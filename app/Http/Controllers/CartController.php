<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Cart;
use Illuminate\Support\Str;
use Helper;

class CartController extends Controller
{
    protected $product = null;
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function addToCart(Request $request)
    {
        if (empty($request->slug)) {
            request()->session()->flash('error', '
            Sản phẩm không hợp lệ');
            return back();
        }
        $product = Product::where('slug', $request->slug)->first();
        if (empty($product)) {
            request()->session()->flash('error', '
            Sản phẩm không hợp lệ');
            return back();
        }

        $already_cart = Cart::where('user_id', auth()->user()->id)->where('order_id', null)->where('product_id', $product->id)->first();
        if ($already_cart) {
            $already_cart->quantity = $already_cart->quantity + 1;
            $already_cart->amount = $product->price + $already_cart->amount;
            if ($already_cart->product->stock < $already_cart->quantity || $already_cart->product->stock <= 0) return back()->with('error', 'Không đủ sản phảm.');
            $already_cart->save();
        } else {

            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = ($product->price - ($product->price * $product->discount) / 100);
            $cart->quantity = 1;
            $cart->amount = $cart->price * $cart->quantity;
            if ($cart->product->stock < $cart->quantity || $cart->product->stock <= 0) return back()->with('error', 'Không đủ sản phảm.');
            $cart->save();
            $wishlist = Wishlist::where('user_id', auth()->user()->id)->where('cart_id', null)->update(['cart_id' => $cart->id]);
        }
        request()->session()->flash('success', 'Thêm sản phẩm vào giỏ hàng thành công');
        return back();
    }

    public function singleAddToCart(Request $request)
    {
        $request->validate([
            'slug'      =>  'required',
            'quant'      =>  'required',
        ]);


        $product = Product::where('slug', $request->slug)->first();
        if ($product->stock < $request->quant[1]) {
            return back()->with('error', 'Hết hàng');
        }
        if (($request->quant[1] < 1) || empty($product)) {
            request()->session()->flash('error', 'Sản phẩm không hợp lệ');
            return back();
        }

        $already_cart = Cart::where('user_id', auth()->user()->id)->where('order_id', null)->where('product_id', $product->id)->first();


        if ($already_cart) {
            $already_cart->quantity = $already_cart->quantity + $request->quant[1];
            $already_cart->amount = ($product->price * $request->quant[1]) + $already_cart->amount;

            if ($already_cart->product->stock < $already_cart->quantity || $already_cart->product->stock <= 0) return back()->with('error', 'Hết hàng.');

            $already_cart->save();
        } else {

            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = ($product->price - ($product->price * $product->discount) / 100);
            $cart->quantity = $request->quant[1];
            $cart->amount = ($product->price * $request->quant[1]);
            if ($cart->product->stock < $cart->quantity || $cart->product->stock <= 0) return back()->with('error', 'Hết hàng.');
            $cart->save();
        }
        request()->session()->flash('success', 'Thêm sản phẩm vào giỏ hàng thành công.');
        return back();
    }

    public function cartDelete(Request $request)
    {
        $cart = Cart::find($request->id);
        if ($cart) {
            $cart->delete();
            request()->session()->flash('success', 'Xóa sản phẩm khỏi giỏ hàng thành công');
            return back();
        }
        request()->session()->flash('error', 'Lỗi, thử lại sau');
        return back();
    }

    public function cartUpdate(Request $request)
    {

        if ($request->quant) {
            $error = array();
            $success = '';
            foreach ($request->quant as $k => $quant) {

                $id = $request->qty_id[$k];

                $cart = Cart::find($id);

                if ($quant > 0 && $cart) {


                    if ($cart->product->stock < $quant) {
                        request()->session()->flash('error', 'Hết hàng.');
                        return back();
                    }
                    $cart->quantity = ($cart->product->stock > $quant) ? $quant  : $cart->product->stock;


                    if ($cart->product->stock <= 0) continue;
                    $after_price = ($cart->product->price - ($cart->product->price * $cart->product->discount) / 100);
                    $cart->amount = $after_price * $quant;

                    $cart->save();
                    $success = 'Cập nhật giỏ hàng thành công.';
                } else {
                    $error[] = 'Giỏ hàng không hợp lệ.';
                }
            }
            return back()->with($error)->with('success', $success);
        } else {
            return back()->with('Giỏ hàng không hợp lệ.');
        }
    }


    public function checkout(Request $request)
    {
        return view('frontend.pages.checkout');
    }
}
