<?php

namespace App\Util\Media;

use App\Util\Blurhash\Blurhash as BlurhashEngine;
use App\Media;
use Image as Intervention;

class Blurhash {

	const DEFAULT_HASH = 'U4Rfzst8?bt7ogayj[j[~pfQ9Goe%Mj[WBay';

	public static function generate(Media $media)
	{
		if(!in_array($media->mime, ['image/png', 'image/jpeg', 'video/mp4'])) {
			return self::DEFAULT_HASH;
		}

		if($media->thumbnail_path == null) {
			return self::DEFAULT_HASH;
		}

		$file  = storage_path('app/' . $media->thumbnail_path);

		if(!is_file($file)) {
			return self::DEFAULT_HASH;
		}

		$image = Intervention::make($file);

		if(!$image) {
			return self::DEFAULT_HASH;
		}
		$width = $image->width();
		$height = $image->height();

		$pixels = [];
		for ($y = 0; $y < $height; ++$y) {
			$row = [];
			for ($x = 0; $x < $width; ++$x) {
				$pixel = $image->getImagePixelColor($x, $y);
				$colors =  $pixel->getColor(); 

				$row[] = [$colors['r'], $colors['g'], $colors['b']];
			}
			$pixels[] = $row;
		}

		$components_x = 4;
		$components_y = 4;
		$blurhash = BlurhashEngine::encode($pixels, $components_x, $components_y);
		if(strlen($blurhash) > 191) {
			return self::DEFAULT_HASH;
		}
		return $blurhash;
	}

}