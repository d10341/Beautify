<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostTag;
use Illuminate\Support\Str;

class PostTagController extends Controller
{
    public function index()
    {
        $postTag = PostTag::orderBy('id', 'DESC')->paginate(10);
        return view('backend.posttag.index')->with('postTags', $postTag);
    }

    public function create()
    {
        return view('backend.posttag.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'string|required',
            'status' => 'required|in:active,inactive'
        ]);
        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = PostTag::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;
        $status = PostTag::create($data);
        if ($status) {
            request()->session()->flash('success', 'Thêm Tag thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('post-tag.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $postTag = PostTag::findOrFail($id);
        return view('backend.posttag.edit')->with('postTag', $postTag);
    }

    public function update(Request $request, $id)
    {
        $postTag = PostTag::findOrFail($id);
        $this->validate($request, [
            'title' => 'string|required',
            'status' => 'required|in:active,inactive'
        ]);
        $data = $request->all();
        $status = $postTag->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Cập nhật Tag thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('post-tag.index');
    }

    public function destroy($id)
    {
        $postTag = PostTag::findOrFail($id);

        $status = $postTag->delete();

        if ($status) {
            request()->session()->flash('success', 'Xóa Tag thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('post-tag.index');
    }
}
