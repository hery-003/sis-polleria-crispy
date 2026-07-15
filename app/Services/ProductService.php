<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductService
{
    public function handleImageUpload(?UploadedFile $image, ?string $currentImage = null): ?string
    {
        if (! $image) {
            return $currentImage;
        }

        if ($currentImage) {
            $this->deleteProductImage($currentImage);
        }

        return $image->store('products', 'public');
    }

    public function generateThumbnail(?string $imagePath): ?string
    {
        if (! $imagePath || ! Storage::disk('public')->exists($imagePath)) {
            return null;
        }

        try {
            $manager = new ImageManager(driver: new Driver());
            $fullPath = Storage::disk('public')->path($imagePath);
            $thumbPath = 'thumbs/' . basename($imagePath);

            $img = $manager->decodePath($fullPath);
            $img->scale(width: 150);
            $img->save(Storage::disk('public')->path($thumbPath));

            return $thumbPath;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function handleImageRemoval(bool $removeImage, ?string $currentImage): ?string
    {
        if ($removeImage && $currentImage) {
            $this->deleteProductImage($currentImage);
            return null;
        }

        return $currentImage;
    }

    public function syncVariants(Product $product, array $variants, ?Request $request = null): void
    {
        $existingIds = collect($variants)->pluck('id')->filter();
        $product->variants()->whereNotIn('id', $existingIds)->delete();

        foreach ($variants as $i => $variant) {
            if ($request) {
                $variantImage = $request->file("variant_images.{$i}");
                if ($variantImage) {
                    if (isset($variant['id'])) {
                        $existing = $product->variants()->where('id', $variant['id'])->first();
                        if ($existing && $existing->image) {
                            $this->deleteProductImage($existing->image);
                            $this->deleteProductImage($existing->image_thumbnail);
                        }
                    }
                    $variant['image'] = $variantImage->store('variants', 'public');
                    $variant['image_thumbnail'] = $this->generateThumbnailFromDisk($variant['image']);
                }

                $removeImageKey = "remove_variant_image.{$i}";
                $removeFlag = $request->boolean($removeImageKey);
                if ($removeFlag) {
                    if (isset($variant['id'])) {
                        $existing = $product->variants()->where('id', $variant['id'])->first();
                        if ($existing && $existing->image) {
                            $this->deleteProductImage($existing->image);
                            $this->deleteProductImage($existing->image_thumbnail);
                        }
                    }
                    $variant['image'] = null;
                    $variant['image_thumbnail'] = null;
                }
            }

            if (isset($variant['id'])) {
                $product->variants()->where('id', $variant['id'])->update(
                    collect($variant)->except('id')->toArray()
                );
            } else {
                $product->variants()->create($variant);
            }
        }

        Cache::forget('pos_categories');
    }

    public function deleteProductImage(?string $image): void
    {
        if ($image) {
            Storage::disk('public')->delete($image);
        }
    }

    public function invalidateCache(): void
    {
        Cache::forget('pos_categories');
    }

    private function generateThumbnailFromDisk(string $imagePath): ?string
    {
        if (! Storage::disk('public')->exists($imagePath)) {
            return null;
        }

        try {
            $manager = new ImageManager(driver: new Driver());
            $fullPath = Storage::disk('public')->path($imagePath);
            $dir = pathinfo($imagePath, PATHINFO_DIRNAME);
            $thumbPath = $dir . '/thumbs/' . basename($imagePath);

            $img = $manager->decodePath($fullPath);
            $img->scale(width: 150);
            $img->save(Storage::disk('public')->path($thumbPath));

            return $thumbPath;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }
}
