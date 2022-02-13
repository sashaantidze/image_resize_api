<?php

namespace App\Http\Middleware;

use App\Models\ImageManipulation;
use Closure;
use Illuminate\Http\Request;

class ImageManipulationOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $image = $request->image instanceof ImageManipulation ? $request->image->id : $request->image; 

        if(!$request->user()->images()->find($image)){
            abort(404, 'Image not found');
        }

        return $next($request);
    }
}
