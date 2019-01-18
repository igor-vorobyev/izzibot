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

    const TIME_SCREENSHOT = 5;

    protected $link;
    protected $width;
    protected $height;



    public function __construct(string $link, int $width, int $height)
    {
        $this->link   = $link;
        $this->width  = $width;
        $this->height = $height;
    }


    public function getLink()
    {
        return $this->link;
    }


    public function getWidth()
    {
        return $this->width;
    }


    public function getHeight()
    {
        return $this->height;
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

            $manager = new ImageManager(['driver' => 'imagick']);
            $image   = $manager->make($path_thumb)->resize($this->getWidth(), $this->getheight());

            // Save thumbnail to storage.
            $image->save($path_thumb);

        } else {

            $manager = new ImageManager(['driver' => 'imagick']);
            $image   = $manager->make($path_origin)->resize($this->getWidth(), $this->getheight());

            // Save thumbnail to storage.
            $image->save($path_thumb);
        }


        // Save to database.
        $thumb = new Thumb();
        $thumb->hash = $filename;
        $thumb->path = $path_thumb;
        $thumb->save();

        return Storage::url('public/thumbs/' . $filename);
   }
}