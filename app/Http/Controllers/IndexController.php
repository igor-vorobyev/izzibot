<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Thumb\Maker;


class IndexController extends Controller
{
    const WIDTH  = 300;
    const HEIGHT = 300;


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
        $maker = new Maker($link, self::WIDTH, self::HEIGHT);


        try {
            $path = $maker->getThumb();
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
