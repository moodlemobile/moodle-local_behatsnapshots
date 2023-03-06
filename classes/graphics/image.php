<?php

namespace local_behatsnapshots\graphics;

use GdImage;

class image {

    public static function from_file(string $filename): image {
        return new image(imagecreatefrompng($filename));
    }

    public static function from_blob(string $data): image {
        return new image(imagecreatefromstring($data));
    }

    /**
     * @var GdImage
     */
    protected $data;

    public function __construct(GdImage $data) {
        $this->data = $data;
    }

    public function compare(self $other): image_diff {
        $width = imagesx($this->data);
        $height = imagesy($this->data);
        $diff = new image_diff($this);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $originalcolor = imagecolorat($this->data, $x, $y);
                $originalred = ($originalcolor >> 16) & 0xFF;
                $originalgreen = ($originalcolor >> 8) & 0xFF;
                $originalblue = $originalcolor & 0xFF;

                $changedcolor = imagecolorat($other->data, $x, $y);
                $changedred = ($changedcolor >> 16) & 0xFF;
                $changedgreen = ($changedcolor >> 8) & 0xFF;
                $changedblue = $changedcolor & 0xFF;

                $distance = abs($originalred - $changedred);
                $distance += abs($originalgreen - $changedgreen);
                $distance += abs($originalblue - $changedblue);

                $diff->set_pixel_distance($x, $y, $distance);
            }
        }

        return $diff;
    }

    public function save(string $filepath) {
        imagepng($this->data, $filepath);
    }

}
