<?php

namespace App\Http\Middleware;

use App\Models\Album;
use Closure;
use Illuminate\Http\Request;

class AlbumOwnership
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

        $album = $request->album instanceof Album ? $request->album->id : $request->album; 

        if(!$request->user()->albums()->find($album)){
            abort(404, 'Album not found');
        }

        return $next($request);
    }
}
