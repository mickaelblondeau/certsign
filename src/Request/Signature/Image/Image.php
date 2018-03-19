<?php

namespace YllyCertSign\Request\Signature\Image;

class Image
{
    /** @var resource */
    private $image;

    /** @var TextStyle */
    private $style;

    /** @var int */
    private $textIndex = 0;

    /**
     * @param int $w
     * @param int $h
     * @param Color $color
     */
    public function __construct($w, $h, Color $color)
    {
        $this->setStyle(new TextStyle(0, 10, 7, 15, new Color(0, 0, 0)));

        $this->image = imagecreate($w, $h);
        imagecolorallocate($this->image, $color->red, $color->green, $color->blue);
    }

    /**
     * @param TextStyle $style
     */
    public function setStyle(TextStyle $style)
    {
        $this->style = $style;
        $this->textIndex = 0;
    }

    /**
     * @param string $text
     */
    public function addText($text)
    {
        $font = __DIR__ . '/../../../../public/fonts/arial.ttf';

        $x = $this->style->getX();
        $y = $this->style->getY() + $this->style->getSpacing() * $this->textIndex;
        ++$this->textIndex;
        $color = $this->style->getColor($this->image);
        $fontSize = $this->style->getFontSize();

        if (function_exists('imagettftext')) {
            imagettftext($this->image, $fontSize, 0, $x, $y, $color, $font, $text);
        } else {
            imagestring($this->image, $fontSize, $x, $y, $text, $color);
        }
    }

    /**
     * @return string
     */
    public function toBase64()
    {
        ob_start();
        imagepng($this->image);
        $data = ob_get_contents();
        ob_end_clean();
        imagedestroy($this->image);

        return base64_encode($data);
    }
}
