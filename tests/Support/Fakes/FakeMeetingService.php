<?php

namespace Tests\Support\Fakes;

use App\Services\OnlineMeetingServiceInterface;

/**
 * Stands in for ZoomService / TeamsService: records every call and answers like
 * a successful meeting creation, without any network traffic.
 */
class FakeMeetingService implements OnlineMeetingServiceInterface
{
    /** @var array<int, array{method: string, args: array}> */
    public array $calls = [];

    public function createMeeting(array $data)
    {
        $this->calls[] = ['method' => 'createMeeting', 'args' => [$data]];
        $n = count($this->calls);

        return [
            'success' => true,
            'data' => [
                'id' => 'fake-meeting-' . $n,
                'join_url' => 'https://example.invalid/fake-meeting/' . $n,
                'start_url' => 'https://example.invalid/fake-meeting/' . $n . '/host',
                'passcode' => '000000',
                'password' => '000000',
            ],
        ];
    }

    public function getSessionReport(string $meetingId, ?string $userId = null)
    {
        return $this->record(__FUNCTION__, func_get_args());
    }

    public function getSessionRecord(string $meetingId, ?string $userId = null)
    {
        return $this->record(__FUNCTION__, func_get_args());
    }

    /** update(), delete() and anything else the controllers call. */
    public function __call($method, $args)
    {
        return $this->record($method, $args);
    }

    /** @return array<int, array> the $data of every createMeeting() call */
    public function created(): array
    {
        return array_values(array_map(
            fn ($call) => $call['args'][0],
            array_filter($this->calls, fn ($call) => $call['method'] === 'createMeeting')
        ));
    }

    private function record(string $method, array $args): array
    {
        $this->calls[] = compact('method', 'args');

        return ['success' => true, 'data' => []];
    }
}
