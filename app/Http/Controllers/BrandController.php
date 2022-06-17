<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Str;

class BrandController extends Controller
{

    public function index()
    {
        $brand = Brand::orderBy('id', 'DESC')->paginate();
        return view('backend.brand.index')->with('brands', $brand);
    }

    public function create()
    {
        return view('backend.brand.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'string|required',
        ]);
        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = Brand::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;
        $status = Brand::create($data);
        if ($status) {
            request()->session()->flash('success', 'Tạo Thương hiệu thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('brand.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            request()->session()->flash('error', 'Không tìm thấy Thương hiệu');
        }
        return view('backend.brand.edit')->with('brand', $brand);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);
        $this->validate($request, [
            'title' => 'string|required',
        ]);
        $data = $request->all();

        $status = $brand->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Sửa Thương hiệu thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('brand.index');
    }

    public function destroy($id)
    {
        $brand = Brand::find($id);
        if ($brand) {
            $status = $brand->delete();
            if ($status) {
                request()->session()->flash('success', 'Xóa Thương hiệu thành công');
            } else {
                request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
            }
            return redirect()->route('brand.index');
        } else {
            request()->session()->flash('error', 'Không tìm thấy Thương hiệu');
            return redirect()->back();
        }
    }
}
