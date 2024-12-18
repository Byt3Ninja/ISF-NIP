<?php

namespace App\Services;

interface OnlineMeetingServiceInterface
{
    public function createMeeting(array $data);
    public function getSessionReport(string $meetingId);
    public function getSessionRecord(string $meetingId);
}
