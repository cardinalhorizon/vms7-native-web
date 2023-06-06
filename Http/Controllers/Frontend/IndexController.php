<?php

namespace Modules\SmartCARSNative\Http\Controllers\Frontend;

use App\Contracts\Controller;
use Illuminate\Http\Request;

/**
 * Class $CLASS$
 * @package 
 */
class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        return view('smartcarsnative::index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function create(Request $request)
    {
        return view('smartcarsnative::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function show(Request $request)
    {
        return view('smartcarsnative::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function edit(Request $request)
    {
        return view('smartcarsnative::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     */
    public function destroy(Request $request)
    {
    }
}
