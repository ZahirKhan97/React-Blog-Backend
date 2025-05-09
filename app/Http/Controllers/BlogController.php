<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\TempImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $blogs = Blog::orderBy('created_at', 'DESC');
        if(!empty($request->keyword))
        {
            $blogs = $blogs->where('title', 'like', '%'.$request->keyword.'%');
        }
        $blogs = $blogs->get();
        return response()->json([
            'status' => true,
            'data' => $blogs
        ]); 
    }

    public function show($id)
    {
        $blog = Blog::find($id);
        if($blog == null)
        {
            return response()->json([
                'status' => false,
                'message' => 'Blog not Found'
            ]);
        }
        $blog['date'] = Carbon::parse($blog->created_at)->format('d M, Y');
        return response()->json([
            'status' => true,
            'data' => $blog
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:10',
            'author' => 'required|min:3',
        ]);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Please fix the errors',
                'errors' => $validator->errors()
            ]);
        }

        $blog = new Blog();
        $blog->title = $request->title;
        $blog->author = $request->author;
        $blog->shortDesc = $request->shortDesc;
        $blog->description = $request->description;
        $blog->save();

        // save image here
        $tempImage = TempImage::find($request->image_id);
        if($tempImage != null)
        {
            $imageExtArray = explode('.', $tempImage->name);
            $ext = last($imageExtArray);
            $imageName = time().'-'.$blog->id.'.'.$ext;
            $blog->image = $imageName;
            $blog->save();

            $sourcePath = public_path('uploads/temp/'.$tempImage->name);
            $destinationPath = public_path('uploads/blogs/'.$imageName);
            File::copy($sourcePath,$destinationPath);
        }

        return response()->json([
            'status' => true,
            'message' => 'Blog added successfully',
            'data' => $blog
        ]); 
    }

    public function update($id, Request $request)
    {
        $blog = Blog::find($id);
        if($blog == null )
        {
            return response()->json([
                'status' => false,
                'message' => 'Blog not Found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:10',
            'author' => 'required|min:3',
        ]);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Please fix the errors',
                'errors' => $validator->errors()
            ]);
        }

        $blog->title = $request->title;
        $blog->author = $request->author;
        $blog->shortDesc = $request->shortDesc;
        $blog->description = $request->description;
        $blog->save();

        // save image here
        $tempImage = TempImage::find($request->image_id);
        if($tempImage != null)
        {
            File::delete(public_path('uploads/blogs/'.$blog->image));
            $imageExtArray = explode('.', $tempImage->name);
            $ext = last($imageExtArray);
            $imageName = time().'-'.$blog->id.'.'.$ext;
            $blog->image = $imageName;
            $blog->save();

            $sourcePath = public_path('uploads/temp/'.$tempImage->name);
            $destinationPath = public_path('uploads/blogs/'.$imageName);
            File::copy($sourcePath,$destinationPath);
        }

        return response()->json([
            'status' => true,
            'message' => 'Blog updated successfully',
            'data' => $blog
        ]); 
    }

    public function destroy($id)
    {
        $blog = Blog::find($id);
        if($blog == null)
        {
            return response()->json([
                'status' => false,
                'message' => 'Blog not Found'
            ]);
        }

        File::delete(public_path('uploads/blogs/'.$blog->image));
        $blog->delete();
        return response()->json([
            'status' => true,
            'message' => 'Blog deleted successfully'
        ]); 
    }
}
