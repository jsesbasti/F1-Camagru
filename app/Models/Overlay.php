<?php

namespace App\Models;

class Overlay
{
    private const DIR = __DIR__ . '/../../public/overlays';
    private const MAX_PER_PHOTO = 10;

    public static function all(): array
    {
        $overlays = [];
        foreach (glob(self::DIR . '/*.png') ?: [] as $path) {
            $id = basename($path, '.png');
            if (self::validId($id)) {
                $overlays[] = ['id' => $id, 'url' => "/overlays/$id.png"];
            }
        }
        return $overlays;
    }

    public static function path(string $id): ?string
    {
        if (!self::validId($id)) {
            return null;
        }
        $path = self::DIR . "/$id.png";
        return is_file($path) ? $path : null;
    }

    public static function parse(?string $raw): ?array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        $items = str_starts_with($raw, '[')
            ? json_decode($raw, true)
            : array_map(fn ($id) => ['id' => $id], explode(',', $raw));

        if (!is_array($items) || !array_is_list($items) || count($items) > self::MAX_PER_PHOTO) {
            return null;
        }

        $specs = [];
        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['id'] ?? null) || !self::path($item['id'])) {
                return null;
            }
            $specs[] = [
                'id' => $item['id'],
                'x' => self::fraction($item['x'] ?? 0.5, -0.5, 1.5),
                'y' => self::fraction($item['y'] ?? 0.5, -0.5, 1.5),
                'size' => self::fraction($item['size'] ?? 1, 0.1, 3),
                'angle' => self::fraction($item['angle'] ?? 0, -180, 180),
            ];
        }
        return $specs;
    }

    private static function fraction($value, float $min, float $max): float
    {
        $value = is_numeric($value) ? (float) $value : ($min + $max) / 2;
        return round(max($min, min($max, $value)), 4);
    }

    private static function validId(string $id): bool
    {
        return (bool) preg_match('/^[a-z0-9_-]{1,40}$/i', $id);
    }
}
