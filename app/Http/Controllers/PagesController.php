<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Page;

class PagesController extends Controller
{
    public function list()
    {
        return view('pages_console.list', [
            'pages' => Page::all()
        ]);
    }

    public function addForm()
    {
        return view('pages_console.add');
    }

    public function add()
    {
        $attributes = request()->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages|regex:/^[A-z0-9\-]+$/',
        ]);

        $page = new Page();
        $page->title = $attributes['title'];
        $page->slug = $attributes['slug'];
        $page->save();

        return redirect('/console/pages/list')->with('message', 'Page has been added!');
    }

    public function editForm(Page $page)
    {
        return view('pages_console.edit', [
            'page' => $page,
        ]);
    }

    public function edit(Page $page)
    {
        $attributes = request()->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages')->ignore($page->id),
                'regex:/^[A-z0-9\-]+$/',
            ],
        ]);

        $page->title = $attributes['title'];
        $page->slug = $attributes['slug'];
        $page->save();

        return redirect('/console/pages/list')->with('message', 'Page has been edited!');
    }

    public function delete(Page $page)
    {
        // delete related sections first
        $page->sections()->each(function($s){
            if($s->image) { \Illuminate\Support\Facades\Storage::delete($s->image); }
            $s->delete();
        });

        $page->delete();

        return redirect('/console/pages/list')->with('message', 'Page has been deleted!');
    }
}
