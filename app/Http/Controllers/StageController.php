<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->can('manage Stage')) {
            $Stages = Stage::OrderBy('id', 'desc')->get();
            return view('stage.index', compact('Stages'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->can('create Stage')) {
            return view('stage.create');
        } else {
            $return['status'] = 'error';
            $return['messages'] = __('Permission denied.');
            return response()->json($return);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create Stage')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'color' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $Stage = new Stage();
            $Stage->color = $request->color;
            $Stage->title = $request->title;
            $Stage->parent_id = \Auth::user()->id;
            $Stage->save();

            return redirect()->back()->with('success', __('Stage successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stage  $Stage
     * @return \Illuminate\Http\Response
     */
    public function show(Stage $Stage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stage  $Stage
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (\Auth::user()->can('edit Stage')) {
            $id = decrypt($id);
            $Stage = Stage::find($id);
            if ($Stage) {
                return view('stage.edit', compact('Stage'));
            } else {
                $return['status'] = 'error';
                $return['messages'] = __('Stage not found.');
                return response()->json($return);
            }
        } else {
            $return['status'] = 'error';
            $return['messages'] = __('Permission denied.');
            return response()->json($return);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stage  $Stage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit Stage')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'color' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $id = decrypt($id);
            $Stage = Stage::find($id);
            $Stage->color = $request->color;
            $Stage->title = $request->title;
            $Stage->save();

            return redirect()->back()->with('success', __('Stage successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stage  $Stage
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (\Auth::user()->can('delete Stage')) {
            $id = decrypt($id);
            $Stage = Stage::find($id);
            if ($Stage) {
                $Stage->delete();
                return redirect()->back()->with('success', 'Stage successfully deleted.');
            } else {
                return redirect()->back()->with('error', 'Stage not found.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
