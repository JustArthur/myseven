<?php
    function optimizeImage($tmpPath, $extension) {
        list($width, $height) = getimagesize($tmpPath);
        $maxWidth = 1000;
        $newWidth = $width > $maxWidth ? $maxWidth : $width;
        $newHeight = ($height / $width) * $newWidth;

        $image = null;
        ob_start();

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($tmpPath);
                $resized = imagescale($image, $newWidth, $newHeight);
                imagejpeg($resized, null, 75); // compression à 75%
                break;
            case 'png':
                $image = imagecreatefrompng($tmpPath);
                $resized = imagescale($image, $newWidth, $newHeight);
                imagepng($resized, null, 6); // compression PNG (0-9)
                break;
        }

        $optimizedContent = ob_get_clean();
        return $optimizedContent;
    }

    use setasign\Fpdi\Fpdi;

    function compressPDF($tmpFile) {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($tmpFile);

        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage();
            $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
        }

        ob_start();
        $pdf->Output('S'); // 'S' retourne le contenu PDF
        return ob_get_clean();
    }

?>