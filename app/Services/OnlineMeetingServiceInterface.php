<?php

namespace App\Services;

interface OnlineMeetingServiceInterface
{
    public function createMeeting(array $data);
    public function getSessionReport(string $userId, ?string $meetingId = null);
    public function getSessionRecord(string $meetingId, ?string $userId = null);
    public function getUserIdFromMeeting(string $meetingId);
}
