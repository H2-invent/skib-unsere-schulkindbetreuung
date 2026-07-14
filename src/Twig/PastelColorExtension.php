<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PastelColorExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('pastel_color', $this->stringToPastelColor(...)),
        ];
    }

    public function stringToPastelColor(string $input): string
    {
        $hash = md5(mb_strtolower(trim($input)));

        $hue = hexdec(substr($hash, 0, 4)) % 360;
        $saturation = 45;
        $lightness = 82;

        [$r, $g, $b] = $this->hslToRgb($hue, $saturation, $lightness);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    private function hslToRgb(float $h, float $s, float $l): array
    {
        $h /= 360;
        $s /= 100;
        $l /= 100;

        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = $this->hueToRgb($p, $q, $h + 1 / 3);
            $g = $this->hueToRgb($p, $q, $h);
            $b = $this->hueToRgb($p, $q, $h - 1 / 3);
        }

        return [
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255),
        ];
    }

    private function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }

        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }

        if ($t < 1 / 2) {
            return $q;
        }

        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }

        return $p;
    }
}