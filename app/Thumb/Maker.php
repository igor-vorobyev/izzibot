<?php

namespace App\Thumb;

use Exception;
use Thumbnail;
use Intervention\Image\ImageManager;
use App\Thumb;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


class Maker
{
    const IMAGES = ['jpeg', 'jpg', 'png'];
    const VIDEOS = ['mp4'];

    const TYPE_IMAGE = 'IMAGE';
    const TYPE_VIDEO = 'VIDEO';

    const MAX_WIDTH  = 400;
    const MAX_HEIGHT = 400;

    const TIME_SCREENSHOT = 5;

    protected $link;



    public function __construct(string $link)
    {
        $this->link   = $link;
    }


    public function getLink()
    {
        return $this->link;
    }


    public function getType()
    {
        $extension = strtolower(pathinfo($this->link, PATHINFO_EXTENSION));

        if (in_array($extension, self::IMAGES)) {
            return self::TYPE_IMAGE;
        }

        if (in_array($extension, self::VIDEOS)) {
            return self::TYPE_VIDEO;
        }

        throw new Exception("Unknown format");
    }


    /**
     * Getting thumbnail.
     *
     * @return string
     */
    public function getThumb() : string
    {
        $thumb = Thumb::where('hash', '=', $this->getHashName())->first();

        if (!empty($thumb)) {
            $path = $thumb->path;
        } else {
            $path = $this->make();
        }
        return $path;
    }



    public function getHashName()
    {
        // Make filename.
        $fileext  = strtolower(pathinfo($this->link, PATHINFO_EXTENSION));

        if ($this->getType() == self::TYPE_VIDEO) {
            $fileext = 'png';
        }
        $filename = sha1($this->link) . '.' . $fileext;

        return $filename;
    }


    /**
     * Making thumbail.
     *
     * @return string
     */
    protected function make()
    {
        $filename = $this->getHashName();

        // Paths.
        $path_origin = Storage::path('public/origins/' . $filename);
        $path_thumb  = Storage::path('public/thumbs/' . $filename);


        // Save origin to storage.
        copy($this->link, $path_origin);


        // Make thumbnail.
        if ($this->getType() == self::TYPE_VIDEO) {

            $status = Thumbnail::getThumbnail(
                $path_origin,
                Storage::path('public/thumbs/'),
                $filename,
                self::TIME_SCREENSHOT
            );
            if (!$status) {
                throw new \Exception("Can't create thumbnail from video.");
            }

            $sizes   = self::getSizes($path_thumb);
            $manager = new ImageManager(['driver' => 'imagick']);
            $image   = $manager->make($path_origin)->resize($sizes['width'], $sizes['height']);


            // Save thumbnail to storage.
            $image->save($path_thumb);

        } else {
            $sizes   = self::getSizes($path_origin);
            $manager = new ImageManager(['driver' => 'imagick']);
            $image   = $manager->make($path_origin)->resize($sizes['width'], $sizes['height']);

            // Save thumbnail to storage.
            $image->save($path_thumb);
        }


        // Save to database.
        $thumb = new Thumb();
        $thumb->hash = $filename;
        $thumb->path = Storage::url('public/thumbs/' . $filename);
        $thumb->save();

        return Storage::url('public/thumbs/' . $filename);
   }


   protected static function getSizes($path)
   {
       list($width, $height) = getimagesize($path);

       if ($width > $height) {
            if ($width > self::MAX_WIDTH) {
                $height = (int) $height / $width * self::MAX_WIDTH;
                $width  = self::MAX_WIDTH;
            } else {
                $width  = (int) $width / $height * self::MAX_HEIGHT;
                $height = self::MAX_HEIGHT;
            }
       } else {
           if ($height > self::MAX_HEIGHT) {
               $width  = (int) $width / $height * self::MAX_HEIGHT;
               $height = self::MAX_HEIGHT;
           } else {
               $height = (int) $height / $width * self::MAX_WIDTH;
               $width  = self::MAX_WIDTH;
            }
       }

       return ['width' => $width, 'height' => $height];
   }
}