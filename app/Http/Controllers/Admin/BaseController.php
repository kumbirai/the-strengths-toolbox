<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

abstract class BaseController extends Controller
{
    /**
     * Return view with admin layout
     */
    protected function adminView(string $view, array $data = [])
    {
        return view($view, $data);
    }

    /**
     * Return success redirect with message
     */
    protected function successRedirect(string $route, string $message)
    {
        return redirect()->route($route)->with('success', $message);
    }

    /**
     * Return error redirect with message
     */
    protected function errorRedirect(string $route, string $message)
    {
        return redirect()->route($route)->with('error', $message);
    }

    /**
     * Return back with success message
     */
    protected function backWithSuccess(string $message)
    {
        return back()->with('success', $message);
    }

    /**
     * Return back with error message
     */
    protected function backWithError(string $message)
    {
        return back()->with('error', $message);
    }
}
