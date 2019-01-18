<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Thumb\Maker;
use App;


class IndexController extends Controller
{

    public function form()
    {
        return view('form');
    }



    /**
     * @param Request $request
     */
    public function thumb(Request $request)
    {
        // Link to file.
        $link = $request->get('link');


        if (empty($link)) {
            return response()->json([
                'status' => false,
                'error'  => 'File is not specified'
            ]);
        }

        // Preview making.
        $maker = new Maker($link);


        try {
            $path = App::make('url')->to('/') . $maker->getThumb();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'link'   => $path
        ]);
    }
}
