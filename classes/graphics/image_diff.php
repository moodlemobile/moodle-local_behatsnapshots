<?php

namespace local_behatsnapshots\graphics;

class image_diff extends image {

    protected const CHANGED_PIXELS_DISTANCE_THRESHOLD = 60;

    /**
     * @var int
     */
    protected $changedpixels;

    /**
     * @var int
     */
    protected $redpixel;

    public function __construct(image $original) {
        $width = imagesx($original->data);
        $height = imagesy($original->data);
        $data = imagecreate($width, $height);
        $mask = imagecreate($width, $height);
        $redpixel = imagecolorallocate($data, 0xFF, 0, 0);

        imagecolorallocate($mask, 0xFF, 0xFF, 0xFF);
        imagecopy($data, $original->data, 0, 0, 0, 0, $width, $height);
        imagecopymerge($data, $mask, 0, 0, 0, 0, $width, $height, 70);

        parent::__construct($data);

        $this->changedpixels = 0;
        $this->redpixel = $redpixel;
    }

    public function percentage(): float {
        return round((100 * $this->changedpixels) / (imagesx($this->data) * imagesy($this->data)), 2);
    }

    public function set_pixel_distance(int $x, int $y, float $distance) {
        if ($distance < static::CHANGED_PIXELS_DISTANCE_THRESHOLD) {
            return;
        }

        $this->changedpixels++;

        imagesetpixel($this->data, $x, $y, $this->redpixel);
    }

}
