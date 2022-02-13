<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResizeImageRequest;
use App\Http\Requests\UpdateImageManipulationRequest;
use App\Http\Resources\V1\ImageManipulationResource;
use App\Models\Album;
use App\Models\ImageManipulation;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\Response;

class ImageManipulationController extends Controller
{


    public function __construct()
    {
        $this->middleware('AlbumOwnership')->only(['resize', 'byAlbum']);
        $this->middleware('ImageManipulationOwnership')->only('show', 'destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ImageManipulationResource::collection($request->user()->images()->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ResizeImageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resize(ResizeImageRequest $request)
    {
        $all = $request->all();

        $image = $all['image'];
        unset($all['image']);

        $data = [
            'type' => imageManipulation::TYPE_RESIZE,
            'data' => json_encode($all),
            'user_id' => $request->user()->id,
            'album_id' => $request->album ?: null
        ];


        $dir = 'images/' . Str::random().'/';
        $absolutePath = public_path($dir);
        File::makeDirectory($absolutePath);

        if($image instanceof UploadedFile){
            $data['name'] = $image->getClientOriginalName();
            $filename = pathinfo($data['name'], PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $originalPath = $absolutePath.$data['name'];

            $image->move($absolutePath, $data['name']);
        }
        else{
            $data['name'] = pathinfo($image, PATHINFO_BASENAME);
            $filename = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $originalPath = $absolutePath.$data['name'];

            copy($image, $originalPath);
        }

        $data['path'] = $dir.$data['name'];

        $w = $all['w'];
        $h = $all['h'] ?? false;

        list($width, $height, $image) = $this->getImageWidthAndHeight($w, $h, $originalPath);

        $resizedFilename = $filename . '-resized.' . $extension;

        $image->resize($width, $height)->save($absolutePath.$resizedFilename);

        $data['output_path'] = $dir.$resizedFilename;

        $imageManipulation = ImageManipulation::create($data);

        return response(new ImageManipulationResource($imageManipulation), Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(ImageManipulation $image)
    {
        return new ImageManipulationResource($image);
    }



    public function byAlbum(Album $album)
    {
        return ImageManipulationResource::collection($album->images()->paginate(20));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageManipulation $image)
    {
        $directory = explode('/', $image->path)[1];

        File::deleteDirectory(public_path('images/'.$directory));

        $image->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }


    private function getImageWidthAndHeight($w, $h, string $originalPath)
    {
        $image = Image::make($originalPath);

        $originalWidth = $image->width();
        $originalHeight = $image->height();


        if(str_ends_with($w, '%')){
            $ratioW = (float)str_replace('%', '', $w);
            $ratioH = $h ? (float)str_replace('%', '', $h) : $ratioW;

            $newWidth = $originalWidth * $ratioW / 100;
            $newHeight = $originalHeight * $ratioH / 100;
        }
        else{
            $newWidth = (float)$w;
            $newHeight = $h ? (float)$h : $originalHeight * $newWidth/$originalWidth;
        }

        return [$newWidth, $newHeight, $image];
    }
}
