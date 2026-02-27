<?php

namespace App\Libraries;

/**
 * Generador de Captcha i Imatges amb Text
 */
class Text2Image
{
    private string $font;
    private string $text;
    private string $letters = '23456789bcdfghjkmnpqrstvwxyz'; // Lletres sense confusió (sense o/0, l/1, etc.)
    private int $length;
    private string $textColor;
    private string $backColor;
    private string $noiceColor;
    private int $imgWidth;
    private int $imgHeight;
    private int $noiceLines;
    private int $noiceDots;
    private int $expiration; // Caducitat de l'arxiu en segons
    private string $blob;

    public function __construct(?array $config = null)
    {
        // Imatge per defecte (quadrat negre 10x10 en base64 transparent)
        $this->blob = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $this->setConfig($config ?? []);
    }

    public function setConfig(array $config): self
    {
        $this->setLength($config['length'] ?? $this->length ?? 6);
        $this->setText($config['text'] ?? $this->text ?? $this->random());

        // Validem que la font existeixi, si no, usem el path per defecte
        $defaultFont = FCPATH . 'fonts/monofont.ttf'; // public/fonts/monofont.ttf
        $this->font = $config['font'] ?? $this->font ?? $defaultFont;
        
        $this->textColor  = $config['textColor'] ?? $this->textColor ?? '162453';
        $this->backColor  = $config['backColor'] ?? $this->backColor ?? '#395786';
        $this->noiceColor = $config['noiceColor'] ?? $this->noiceColor ?? '162453';
        $this->imgWidth   = $config['imgWidth'] ?? $this->imgWidth ?? 180;
        $this->imgHeight  = $config['imgHeight'] ?? $this->imgHeight ?? 40;
        $this->noiceLines = $config['noiceLines'] ?? $this->noiceLines ?? 25;
        $this->noiceDots  = $config['noiceDots'] ?? $this->noiceDots ?? 25;
        
        // Use constants like MINUTE from CI4, fallback to 60 if not defined
        $this->expiration = $config['expiration'] ?? $this->expiration ?? (defined('MINUTE') ? 15 * MINUTE : 900);

        return $this;
    }

    public function toJSON(): string
    {
        return json_encode(['text' => $this->text, 'image_base64' => $this->blob]);
    }

    public function toImg64(): string
    {
        return $this->blob;
    }

    public function html(): string
    {
        return '<img src="data:image/png;base64,' . $this->blob . '" alt="Captcha" />';
    }

    public function saveToFile(string $publicImageFolder, ?string $name = null, bool $clearOld = false): string
    {
        $relativePath = FCPATH . trim($publicImageFolder, '/') . '/';
        $now = time();

        if ($clearOld && is_dir($relativePath)) {
            $files = glob($relativePath . '*.png');
            foreach ($files as $file) {
                if (filemtime($file) + $this->expiration < $now) {
                    unlink($file);
                }
            }
        }

        $filename = $name ?? $now . ".png";
        $filepath = $relativePath . $filename;

        $img = imagecreatefromstring(base64_decode($this->blob));

        // Desem en PNG (molt millor per a text)
        if ($img !== false && imagepng($img, $filepath)) {
            imagedestroy($img);
            return json_encode(['text' => $this->text, 'path' => $publicImageFolder, 'imagename' => $filename]);
        }

        return json_encode(['text' => $this->text, 'path' => $publicImageFolder, 'imagename' => 'ERROR']);
    }

    public function textToImage(?string $textToShow = null): self
    {
        $this->text = $textToShow ?? $this->text;
        $fontSize = (int)($this->imgHeight * 0.75);

        $im = imagecreatetruecolor($this->imgWidth, $this->imgHeight);
        
        $colorTxt = $this->hexToRGB($this->textColor);
        $textColor = imagecolorallocate($im, $colorTxt['r'], $colorTxt['g'], $colorTxt['b']);

        $colorBg = $this->hexToRGB($this->backColor);
        $backgroundColor = imagecolorallocate($im, $colorBg['r'], $colorBg['g'], $colorBg['b']);

        imagefill($im, 0, 0, $backgroundColor);
        
        list($x, $y) = $this->ImageTTFCenter($im, $this->text, $this->font, $fontSize);
        imagettftext($im, $fontSize, 0, 10, $y, $textColor, $this->font, $this->text);

        $this->saveToBlob($im);
        return $this;
    }

    public function captcha(): self
    {
        $im = imagecreatetruecolor($this->imgWidth, $this->imgHeight);
        $fontSize = (int)($this->imgHeight * 0.75);

        $colorBg = $this->hexToRGB($this->backColor);
        $backgroundColor = imagecolorallocate($im, $colorBg['r'], $colorBg['g'], $colorBg['b']);

        $colorTxt = $this->hexToRGB($this->textColor);
        $textColor = imagecolorallocate($im, $colorTxt['r'], $colorTxt['g'], $colorTxt['b']);

        imagefill($im, 0, 0, $backgroundColor);
        $im = $this->addNoise($im);

        list($x, $y) = $this->ImageTTFCenter($im, $this->text, $this->font, $fontSize);

        for ($i = 0; $i < strlen($this->text); $i++) {
            $pos = random_int(-30, 30);
            imagettftext($im, $fontSize, $pos, 10 + ($i * $fontSize), $y, $textColor, $this->font, $this->text[$i]);
        }

        $this->saveToBlob($im);
        return $this;
    }

    // --- Mètodes interns de suport ---

    private function saveToBlob(\GdImage $im): void
    {
        ob_start();
        imagepng($im); // PNG és millor que JPEG per a text nítid
        $this->blob = base64_encode(ob_get_contents());
        ob_end_clean();
        imagedestroy($im);
    }

    protected function random(): string
    {
        $str = '';
        $max = strlen($this->letters) - 1;
        for ($i = 0; $i < $this->length; $i++) {
            $str .= $this->letters[random_int(0, $max)];
        }
        return $str;
    }

    private function addNoise(\GdImage $img): \GdImage
    {
        $colorNoice = $this->hexToRGB($this->noiceColor);
        $noiceColor = imagecolorallocate($img, $colorNoice['r'], $colorNoice['g'], $colorNoice['b']);

        if ($this->noiceLines > 0) {
            for ($i = 0; $i < $this->noiceLines; $i++) {
                imageline($img, random_int(0, $this->imgWidth), random_int(0, $this->imgHeight), random_int(0, $this->imgWidth), random_int(0, $this->imgHeight), $noiceColor);
            }
        }

        if ($this->noiceDots > 0) {
            for ($i = 0; $i < $this->noiceDots; $i++) {
                imagefilledellipse($img, random_int(0, $this->imgWidth), random_int(0, $this->imgHeight), random_int(2, 6), random_int(2, 6), $noiceColor);
            }
        }
        return $img;
    }

    protected function hexToRGB(string $colour): array
    {
        $colour = ltrim($colour, '#');
        if (strlen($colour) == 6) {
            list($r, $g, $b) = [$colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]];
        } elseif (strlen($colour) == 3) {
            list($r, $g, $b) = [$colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]];
        } else {
            return ['r' => 0, 'g' => 0, 'b' => 0]; // Fallback negre
        }
        return ['r' => hexdec($r), 'g' => hexdec($g), 'b' => hexdec($b)];
    }

    protected function ImageTTFCenter(\GdImage $image, string $text, string $font, int $size, int $angle = 0): array
    {
        $xi = imagesx($image);
        $yi = imagesy($image);
        $box = imagettfbbox($size, $angle, $font, $text);
        if ($box === false) return [0, 0];

        $xr = abs(max($box[2], $box[4]));
        $yr = abs(max($box[5], $box[7]));
        $x = intval(($xi - $xr) / 2);
        $y = intval(($yi + $yr) / 2);
        return [$x, $y];
    }

    // Getters i Setters màgics
    public function __get(string $property)
    {
        $method = "get" . ucfirst($property);
        if (method_exists($this, $method)) return $this->$method();
        return $this->$property ?? null;
    }

    protected function getLength(): int { return $this->length; }
    protected function setLength(int $value): void { $this->length = $value; $this->text = $this->random(); }
    protected function getText(): string { return $this->text; }
    protected function setText(string $value): void { $this->text = $value; $this->length = strlen($value); }
}