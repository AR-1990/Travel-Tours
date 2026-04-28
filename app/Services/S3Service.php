<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class S3Service
{
    /**
     * Upload media file to S3 with folder separation
     * Images go to 'media/images' folder, videos go to 'media/videos' folder
     * Category parameter allows subfolder organization (profile, product, service, blog, event, posts, company)
     *
     * @param UploadedFile $file
     * @param string|null $category Optional category folder (profile, product, service, blog, event, posts, company)
     * @return array Returns array with 'path', 'url', 'type', and 'folder'
     * @throws \Exception
     */
    public function uploadMedia(UploadedFile $file, ?string $category = null): array
    {
        // Determine if file is image or video
        $mimeType = $file->getMimeType();
        $isImage = str_starts_with($mimeType, 'image/');
        $isVideo = str_starts_with($mimeType, 'video/');

        if (!$isImage && !$isVideo) {
            throw new \Exception('File must be an image or video. Received: ' . $mimeType);
        }

        // Determine base folder based on file type
        $baseFolder = $isImage ? 'media/images' : 'media/videos';

        // Build the path with category if provided
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $category ? "{$baseFolder}/{$category}/{$fileName}" : "{$baseFolder}/{$fileName}";

        // Use AWS SDK directly to avoid PortableVisibilityConverter issues
        $storedPath = $this->uploadWithAwsSdk($file, $path, $mimeType);
        $path = $storedPath;
        
        // Build the URL using custom URL if set, otherwise construct from bucket/region
        $url = $this->getUrl($path);

        $result = [
            'path' => $path,
            'url' => $url,
            'type' => $isImage ? 'image' : 'video',
            'folder' => $baseFolder, // This will be 'media/images' or 'media/videos'
            'category' => $category,
            'mime_type' => $mimeType,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ];

        // Generate thumbnail for videos
        if ($isVideo) {
            try {
                $thumbnailResult = $this->generateVideoThumbnail($file, $category);
                if ($thumbnailResult) {
                    $result['thumbnail_path'] = $thumbnailResult['path'];
                    $result['thumbnail_url'] = $thumbnailResult['url'];
                    $result['duration'] = $thumbnailResult['duration'] ?? null;
                }
            } catch (\Exception $e) {
                // Log error but don't fail the upload
                Log::warning('Failed to generate video thumbnail: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Delete a file from S3
     *
     * @param string $path The path to delete
     * @return bool
     */
    public function deleteMedia(string $path): bool
    {
        try {
            $s3Client = $this->getS3Client();
            $s3Client->deleteObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $path,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if a file exists in S3
     *
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        try {
            $s3Client = $this->getS3Client();
            return $s3Client->doesObjectExist(
                config('filesystems.disks.s3.bucket'),
                $path
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get URL for a file in S3
     *
     * @param string $path
     * @return string
     */
    public function getUrl(string $path): string
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $region = config('filesystems.disks.s3.region');
        $customUrl = config('filesystems.disks.s3.url');
        
        if ($customUrl) {
            return rtrim($customUrl, '/') . '/' . $path;
        }
        
        return "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
    }

    /**
     * Extract S3 path from a full S3 URL
     *
     * @param string $url
     * @return string|null
     */
    public function extractPathFromUrl(string $url): ?string
    {
        // If it's not a URL, return as is (might already be a path)
        if (!str_starts_with($url, 'http')) {
            return $url;
        }

        $bucket = config('filesystems.disks.s3.bucket');
        $region = config('filesystems.disks.s3.region');
        $customUrl = config('filesystems.disks.s3.url');

        // Try custom URL pattern
        if ($customUrl) {
            $customUrlBase = rtrim($customUrl, '/');
            if (str_starts_with($url, $customUrlBase)) {
                return str_replace($customUrlBase . '/', '', $url);
            }
        }

        // Try standard S3 URL pattern
        $s3UrlPattern = "https://{$bucket}.s3.{$region}.amazonaws.com/";
        if (str_starts_with($url, $s3UrlPattern)) {
            return str_replace($s3UrlPattern, '', $url);
        }

        // Try alternative S3 URL pattern (without region)
        $s3UrlPatternAlt = "https://{$bucket}.s3.amazonaws.com/";
        if (str_starts_with($url, $s3UrlPatternAlt)) {
            return str_replace($s3UrlPatternAlt, '', $url);
        }

        return null;
    }

    /**
     * Get S3 Client instance
     *
     * @return S3Client
     * @throws \Exception
     */
    private function getS3Client(): S3Client
    {
        // Ensure AWS SDK classes are loaded
        if (!class_exists('\Aws\S3\S3Client')) {
            // Check if vendor directory exists
            if (!file_exists(base_path('vendor/aws/aws-sdk-php'))) {
                throw new \Exception('AWS SDK is not installed. Please run: composer install');
            }
            
            // The class should be autoloaded, if not, web server needs restart
            throw new \Exception('AWS SDK classes not found. Please restart your Apache server in XAMPP Control Panel, then try again.');
        }

        $key = config('filesystems.disks.s3.key');
        $secret = config('filesystems.disks.s3.secret');
        $region = config('filesystems.disks.s3.region');

        if (empty($key) || empty($secret) || empty($region)) {
            throw new \Exception('AWS credentials not configured. Please check your .env file.');
        }

        return new S3Client([
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ]);
    }

    /**
     * Upload file directly using AWS SDK
     *
     * @param UploadedFile $file
     * @param string $path
     * @param string $mimeType
     * @return string
     * @throws \Exception
     */
    private function uploadWithAwsSdk(UploadedFile $file, string $path, string $mimeType): string
    {
        $s3Client = $this->getS3Client();

        try {
            // Remove ACL if bucket doesn't support it (newer S3 buckets)
            $putObjectParams = [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $path,
                'Body' => file_get_contents($file->getRealPath()),
                'ContentType' => $mimeType,
            ];
            
            // Only add ACL if bucket supports it (older buckets)
            // Newer buckets use bucket policies for public access instead
            // Uncomment the line below if your bucket supports ACLs
            // $putObjectParams['ACL'] = 'public-read';
            
            $s3Client->putObject($putObjectParams);

            return $path;
        } catch (AwsException $e) {
            throw new \Exception('AWS S3 upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate thumbnail from video file
     *
     * @param UploadedFile $videoFile
     * @param string|null $category
     * @return array|null Returns array with 'path', 'url', and 'duration' or null on failure
     */
    private function generateVideoThumbnail(UploadedFile $videoFile, ?string $category = null): ?array
    {
        $tempVideoPath = $videoFile->getRealPath();
        $tempThumbnailPath = sys_get_temp_dir() . '/' . 'thumb_' . time() . '_' . Str::random(10) . '.jpg';

        try {
            // Try to extract frame using ffmpeg
            $ffmpegPath = $this->getFfmpegPath();
            
            if ($ffmpegPath) {
                // Extract frame at 1 second (or 10% of video if duration is known)
                $command = sprintf(
                    '%s -i %s -ss 00:00:01 -vframes 1 -q:v 2 -y %s 2>&1',
                    escapeshellarg($ffmpegPath),
                    escapeshellarg($tempVideoPath),
                    escapeshellarg($tempThumbnailPath)
                );

                exec($command, $output, $returnCode);

                if ($returnCode !== 0 || !file_exists($tempThumbnailPath)) {
                    // Try extracting at 0.5 seconds as fallback
                    $command = sprintf(
                        '%s -i %s -ss 00:00:00.5 -vframes 1 -q:v 2 -y %s 2>&1',
                        escapeshellarg($ffmpegPath),
                        escapeshellarg($tempVideoPath),
                        escapeshellarg($tempThumbnailPath)
                    );
                    exec($command, $output, $returnCode);
                }

                if ($returnCode === 0 && file_exists($tempThumbnailPath) && filesize($tempThumbnailPath) > 0) {
                    // Get video duration
                    $duration = $this->getVideoDuration($ffmpegPath, $tempVideoPath);

                    // Upload thumbnail to S3
                    $thumbnailFileName = 'thumb_' . time() . '_' . Str::random(10) . '.jpg';
                    $thumbnailPath = $category 
                        ? "media/images/{$category}/thumbnails/{$thumbnailFileName}"
                        : "media/images/thumbnails/{$thumbnailFileName}";

                    // Read thumbnail file and upload
                    $thumbnailContent = file_get_contents($tempThumbnailPath);
                    $s3Client = $this->getS3Client();
                    $s3Client->putObject([
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Key' => $thumbnailPath,
                        'Body' => $thumbnailContent,
                        'ContentType' => 'image/jpeg',
                    ]);

                    // Clean up temp file
                    @unlink($tempThumbnailPath);

                    return [
                        'path' => $thumbnailPath,
                        'url' => $this->getUrl($thumbnailPath),
                        'duration' => $duration,
                    ];
                }
            }

            // Fallback: If ffmpeg is not available, return null
            // The upload will still succeed, just without thumbnail
            Log::warning('FFmpeg not available or failed to generate thumbnail');
            return null;

        } catch (\Exception $e) {
            Log::error('Error generating video thumbnail: ' . $e->getMessage());
            @unlink($tempThumbnailPath);
            return null;
        }
    }

    /**
     * Get FFmpeg executable path
     *
     * @return string|null
     */
    private function getFfmpegPath(): ?string
    {
        // First, check if custom path is set in environment variable
        $customPath = env('FFMPEG_PATH');
        if ($customPath && $this->commandExists($customPath)) {
            return $customPath;
        }

        // Check common ffmpeg locations
        $possiblePaths = [
            'ffmpeg', // In PATH (most common)
            '/usr/bin/ffmpeg', // Linux
            '/usr/local/bin/ffmpeg', // macOS/Linux
            'C:\\ffmpeg\\bin\\ffmpeg.exe', // Windows common location
            'C:\\xampp\\ffmpeg\\bin\\ffmpeg.exe', // XAMPP Windows
            base_path('ffmpeg/bin/ffmpeg.exe'), // Project directory
        ];

        foreach ($possiblePaths as $path) {
            if ($this->commandExists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Check if a command exists and is executable
     *
     * @param string $command
     * @return bool
     */
    private function commandExists(string $command): bool
    {
        $whereIsCommand = (PHP_OS === 'WINNT') ? 'where' : 'which';
        
        $process = proc_open(
            "$whereIsCommand $command",
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $returnCode = proc_close($process);
            return $returnCode === 0 && !empty(trim($stdout));
        }

        return false;
    }

    /**
     * Get video duration in seconds
     *
     * @param string $ffmpegPath
     * @param string $videoPath
     * @return int|null
     */
    private function getVideoDuration(string $ffmpegPath, string $videoPath): ?int
    {
        try {
            $command = sprintf(
                '%s -i %s 2>&1 | grep "Duration" | cut -d \' \' -f 4 | sed s/,//',
                escapeshellarg($ffmpegPath),
                escapeshellarg($videoPath)
            );

            $output = shell_exec($command);
            
            if ($output && preg_match('/(\d+):(\d+):(\d+)/', trim($output), $matches)) {
                $hours = (int)$matches[1];
                $minutes = (int)$matches[2];
                $seconds = (int)$matches[3];
                return ($hours * 3600) + ($minutes * 60) + $seconds;
            }

            // Alternative method using ffprobe if available
            $ffprobePath = str_replace('ffmpeg', 'ffprobe', $ffmpegPath);
            if ($this->commandExists($ffprobePath)) {
                $command = sprintf(
                    '%s -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
                    escapeshellarg($ffprobePath),
                    escapeshellarg($videoPath)
                );
                $duration = shell_exec($command);
                if ($duration && is_numeric(trim($duration))) {
                    return (int)round((float)trim($duration));
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get video duration: ' . $e->getMessage());
        }

        return null;
    }
}

