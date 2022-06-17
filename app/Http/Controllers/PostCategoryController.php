<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostCategory;
use Illuminate\Support\Str;

class PostCategoryController extends Controller
{
    public function index()
    {
        $postCategory = PostCategory::orderBy('id', 'DESC')->paginate(10);
        return view('backend.postcategory.index')->with('postCategories', $postCategory);
    }

    public function create()
    {
        return view('backend.postcategory.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'string|required',
            'status' => 'required|in:active,inactive'
        ]);
        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = PostCategory::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;
        $status = PostCategory::create($data);
        if ($status) {
            request()->session()->flash('success', 'Thêm Danh mục Bài viết thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('post-category.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $postCategory = PostCategory::findOrFail($id);
        return view('backend.postcategory.edit')->with('postCategory', $postCategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $postCategory = PostCategory::findOrFail($id);
        // return $request->all();
        $this->validate($request, [
            'title' => 'string|required',
            'status' => 'required|in:active,inactive'
        ]);
        $data = $request->all();
        $status = $postCategory->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Cập nhật Danh mục Bài việt thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('post-category.index');
    }

    public function destroy($id)
    {
        $postCategory = PostCategory::findOrFail($id);

        $status = $postCategory->delete();

        if ($status) {
            request()->session()->flash('success', 'Xóa Danh mục Bài viết thành công');
        } else {
            request()->session()->flash('error', 'Lỗi, vui lòng thử lại');
        }
        return redirect()->route('post-category.index');
    }
}
