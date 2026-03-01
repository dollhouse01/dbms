<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage tag')) {
            $tags = Tag::where('parent_id', '=', \Auth::user()->id)->OrderBy('id', 'desc')->get();
            return view('tag.index', compact('tags'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        return view('tag.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create tag')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $tag = new Tag();
            $tag->title = $request->title;
            $tag->parent_id = \Auth::user()->id;
            $tag->save();

            return redirect()->back()->with('success', __('Tag successfully created!'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(Tag $tag)
    {
        //
    }


    public function edit($id)
    {
        if (\Auth::user()->can('edit tag')) {
            $id = decrypt($id);
            $tag = Tag::find($id);
            return view('tag.edit', compact('tag'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit tag')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $id = decrypt($id);
            $tag = Tag::find($id);
            $tag->title = $request->title;
            $tag->save();

            return redirect()->back()->with('success', __('Tag successfully updated!'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('delete tag')) {
            $id = decrypt($id);
            $tag = Tag::find($id);
            $tag->delete();
            return redirect()->back()->with('success', 'Tag successfully deleted!');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
