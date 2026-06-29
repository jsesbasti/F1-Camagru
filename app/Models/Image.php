<?php

namespace App\Models;

use App\Core\Model;

class Image extends Model
{
    public static function feedPage(int $limit = 4, int $offset = 0): array
    {
        $limit = max(1, min($limit, 20));
        $offset = max(0, $offset);

        $stmt = self::db()->prepare(
            "SELECT
                i.id,
                i.user_id,
                i.filename,
                i.description,
                i.created_at,
                u.username,
                COALESCE(l.likes_count, 0) AS likes_count,
                COALESCE(c.comments_count, 0) AS comments_count
            FROM images i
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN (
                SELECT image_id, COUNT(*) AS likes_count
                FROM likes
                GROUP BY image_id
            ) l ON l.image_id = i.id
            LEFT JOIN (
                SELECT image_id, COUNT(*) AS comments_count
                FROM comments
                GROUP BY image_id
            ) c ON c.image_id = i.id
            ORDER BY i.created_at DESC, i.id DESC
            LIMIT :limit_plus_one OFFSET :offset"
        );

        $stmt->bindValue(':limit_plus_one', $limit + 1, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $hasMore = count($rows) > $limit;

        if ($hasMore) {
            array_pop($rows);
        }

        $commentsByImage = self::commentsForImages(array_column($rows, 'id'));

        $posts = array_map(static function (array $row) use ($commentsByImage) {
            $imageId = (int) $row['id'];

            return [
                'id' => $imageId,
                'username' => $row['username'],
                'avatar_initials' => self::initials($row['username']),
                'image_url' => self::imageUrl($row['filename'] ?? ''),
                'caption' => trim((string) ($row['description'] ?? '')),
                'likes_count' => (int) $row['likes_count'],
                'comments_count' => (int) $row['comments_count'],
                'created_at' => $row['created_at'],
                'badge' => 'Post',
                'comments' => $commentsByImage[$imageId] ?? [],
            ];
        }, $rows);

        return [
            'posts' => $posts,
            'hasMore' => $hasMore,
        ];
    }

    private static function commentsForImages(array $imageIds): array
    {
        if (empty($imageIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = self::db()->prepare(
            "SELECT
                c.image_id,
                c.comment,
                c.created_at,
                u.username
            FROM comments c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.image_id IN ($placeholders)
            ORDER BY c.created_at ASC, c.id ASC"
        );
        $stmt->execute(array_values($imageIds));

        $commentsByImage = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $imageId = (int) $row['image_id'];

            if (!isset($commentsByImage[$imageId])) {
                $commentsByImage[$imageId] = [];
            }

            if (count($commentsByImage[$imageId]) >= 3) {
                continue;
            }

            $commentsByImage[$imageId][] = [
                'username' => $row['username'],
                'comment' => $row['comment'],
                'created_at' => $row['created_at'],
            ];
        }

        return $commentsByImage;
    }

    private static function initials(string $username): string
    {
        $cleanName = trim($username);

        if ($cleanName === '') {
            return '??';
        }

        $parts = preg_split('/[^a-zA-Z0-9]+/', $cleanName) ?: [];
        $letters = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $letters .= strtoupper(substr($part, 0, 1));
            if (strlen($letters) >= 2) {
                break;
            }
        }

        if ($letters === '') {
            $letters = strtoupper(substr($cleanName, 0, 2));
        }

        return str_pad(substr($letters, 0, 2), 2, '?');
    }

    private static function imageUrl(string $filename): string
    {
        $cleanFilename = trim($filename);

        if ($cleanFilename === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#i', $cleanFilename) || str_starts_with($cleanFilename, '/')) {
            return $cleanFilename;
        }

        if (str_starts_with($cleanFilename, 'assets/') || str_starts_with($cleanFilename, 'img/')) {
            return $cleanFilename;
        }

        return 'assets/img/' . ltrim($cleanFilename, '/');
    }
}
