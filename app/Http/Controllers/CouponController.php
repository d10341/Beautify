<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\Cart;

class CouponController extends Controller
{
    public function index()
    {
        $coupon = Coupon::orderBy('id', 'DESC')->paginate('10');
        return view('backend.coupon.index')->with('coupons', $coupon);
    }

    public function create()
    {
        return view('backend.coupon.create');
    }

    public function store(Request $request)
    {
        // return $request->all();
        $this->validate($request, [
            'code' => 'string|required',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric',
            'status' => 'required|in:active,inactive'
        ]);
        $data = $request->all();
        $status = Coupon::create($data);
        if ($status) {
            request()->session()->flash('success', 'Thêm Coupon thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('coupon.index');
    }

    public function show($id)
    {
    }

    public function edit($id)
    {
        $coupon = Coupon::find($id);
        if ($coupon) {
            return view('backend.coupon.edit')->with('coupon', $coupon);
        } else {
            return view('backend.coupon.index')->with('error', 'Coupon không tìm thấy');
        }
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);
        $this->validate($request, [
            'code' => 'string|required',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric',
            'status' => 'required|in:active,inactive'
        ]);
        $data = $request->all();

        $status = $coupon->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Sửa Coupon thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('coupon.index');
    }

    public function destroy($id)
    {
        $coupon = Coupon::find($id);
        if ($coupon) {
            $status = $coupon->delete();
            if ($status) {
                request()->session()->flash('success', 'Xóa Coupon thành công');
            } else {
                request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
            }
            return redirect()->route('coupon.index');
        } else {
            request()->session()->flash('error', 'Coupon không tìm thấy');
            return redirect()->back();
        }
    }

    public function couponStore(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->first();
        if (!$coupon) {
            request()->session()->flash('error', 'Coupon không hợp lệ');
            return back();
        }
        if ($coupon) {
            $total_price = Cart::where('user_id', auth()->user()->id)->where('order_id', null)->sum('price');
            session()->put('coupon', [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'value' => $coupon->discount($total_price)
            ]);
            request()->session()->flash('success', 'Áp dụng Coupon thành công');
            return redirect()->back();
        }
    }
}
