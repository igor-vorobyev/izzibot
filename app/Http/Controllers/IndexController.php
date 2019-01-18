<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Thumb\Maker;


class IndexController extends Controller
{

    /**
     * @param Request $request
     */
    public function thumb(Request $request)
    {
        $link = $request->get('link');


        // Preview making.
        $maker = new Maker($link, 300, 300);

        try {
            $path = $maker->getThumb();
        } catch (\Exception $e) {

        }

        return view('thumb', ['data' => ['link' => $link, 'path' => $path]]);
    }
}
