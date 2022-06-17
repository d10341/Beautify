<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index()
    {
        $banner = Banner::orderBy('id', 'DESC')->paginate(10);
        return view('backend.banner.index')->with('banners', $banner);
    }

    public function create()
    {
        return view('backend.banner.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'string|required|max:50',
            'description' => 'string|nullable',
            'photo' => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);
        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = Banner::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;
        $status = Banner::create($data);
        if ($status) {
            request()->session()->flash('success', 'Thêm Banner thành công');
        } else {
            request()->session()->flash('error', 'Lỗi khi đang tạo Banner');
        }
        return redirect()->route('banner.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('backend.banner.edit')->with('banner', $banner);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $this->validate($request, [
            'title' => 'string|required|max:50',
            'description' => 'string|nullable',
            'photo' => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);
        $data = $request->all();
        $status = $banner->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Sửa Banner thành công');
        } else {
            request()->session()->flash('error', 'Lỗi khi đang sửa Banner');
        }
        return redirect()->route('banner.index');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $status = $banner->delete();
        if ($status) {
            request()->session()->flash('success', 'Xóa Banner thành công');
        } else {
            request()->session()->flash('error', 'Lỗi khi đang xóa Banner');
        }
        return redirect()->route('banner.index');
    }
}
