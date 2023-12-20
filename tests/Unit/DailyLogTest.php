<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Livewire\DailyLog;

class DailyLogTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function it_can_upload_to_s3(): void
    {
        // 假設這是你的 Livewire 組件
        $dailyLog = new DailyLog();

        // 創建一個虛擬的上傳檔案
        $image = UploadedFile::fake()->image('test-image.jpg');

        // 模擬用戶和任務報告的 ID
        $authId       = 999;
        $taskReportId = 99999;

        // 確認圖片是否成功上傳至 S3
        $s3FolderPath = $dailyLog->getS3ImagePath($taskReportId, $authId);

        // 使用虛擬的 S3 存儲系統
        Storage::fake('s3');

        // 執行上傳到 S3 的方法
        $dailyLog->uploadToS3($image, $taskReportId);

        // 斷言檔案是否存在於 S3 中
        Storage::disk('s3')->assertExists($s3FolderPath . $image->getClientOriginalName());

        $this->assertEquals($image->getSize(), Storage::disk('s3')->size($s3FolderPath . $image->getClientOriginalName()));
        $this->assertEquals('image/jpeg', Storage::disk('s3')->mimeType($s3FolderPath . $image->getClientOriginalName()));
    }
}
