<?php


class image
{

    /**
     * image constructor.
     */
    public function __construct($images,$name_pdf)
    {
        //$images = array("file/test.jpg");
        $pdf = new Imagick($images);
        $pdf->setImageFormat('pdf');
        $pdf->writeImages($name_pdf, true);
    }
}