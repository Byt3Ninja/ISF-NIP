<?php

namespace App\Services;

interface OnlineMeetingServiceInterface
{
    public function createMeeting(array $data);

    public function getAllMeetings();

    public function getSessionReport(string $meetingId);

    public function deleteMeeting(string $id);

    public function getRecordedSessions(string $meetingId);
}
