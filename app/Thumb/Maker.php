<?php

namespace App\Thumb;

use Thumbnail;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


class Maker
{
    const IMAGES = ['jpeg', 'jpg', 'png'];
    const VIDEOS = ['mp4'];

    const TYPE_IMAGE = 'IMAGE';
    const TYPE_VIDEO = 'VIDEO';

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

        return self::TYPE_IMAGE;
    }


    /**
     * Making thumbnail.
     *
     * @return string
     */
    public function getThumb() : string
    {
        if (0) {

        }
        $path = $this->make();

        return $path;
    }


    protected function make()
    {
        // Make filename.
        $fileext  = strtolower(pathinfo($this->link, PATHINFO_EXTENSION));
        $filename = sha1($this->link) . '.' . $fileext;

        // Get content.
        $content = file_get_contents($this->link);


        // Make thumbnail.


        // Save thumbnail to storage.
        Storage::put('thumbs/' . $filename, $content);

        
        // Save to database.


        return $filename;
   }
}