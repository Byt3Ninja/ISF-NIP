<?php


namespace App\Services;

use App\User;
use stdClass;
use App\Company;
use Carbon\Carbon;
use App\Models\MentorshipRequest;
use Illuminate\Support\Facades\DB;

class NextSessionsService
{
    /**
     * جلب الجلسات القادمة
     *
     * @param int $days
     * @param string $l 'ar' أو 'en'
     * @param int|null $user_id
     * param int|null $cycle_id
     * @return \Illuminate\Support\Collection
     */
    public function getUpcomingSessions(int $days = 14, string $l = 'ar', int $user_id = null, int $cycle_id = null)
    {
        $days = (int) request()->query('days', 14);
        // GV-9566: zoom_date is platform wall-clock; the window is platform days.
        $platformNow = TimezoneService::platformNow();
        $start = $platformNow->copy()->startOfDay()->format('Y-m-d H:i:s');
        $end = $platformNow->copy()->addDays($days)->endOfDay()->format('Y-m-d H:i:s');

        $query = DB::table('mentorship_requests as mr')
            ->leftJoin('users as m', 'm.id', '=', 'mr.mentor_id')
            ->leftJoin('users as u', 'u.id', '=', 'mr.user_id')
            ->leftJoin('companies as c', 'c.id', '=', 'u.company_id')
            ->leftJoin('mentorship_requests as msr', 'msr.mentor_id', '=', 'm.id')
            ->select([
                'mr.id', 'mr.mentor_id', 'mr.user_id', 'mr.comment', 'mr.approved',
                'mr.rating', 'mr.join_url', 'mr.zoom_date', 'mr.period',
                'mr.rate_comment', 'mr.done', 'mr.cycle_id', 'mr.organization_id',
                'u.img_url as user_img_url',
                'u.first_name_en as user_first_name_en', 'u.first_name_ar as user_first_name_ar',
                'u.last_name_en as user_last_name_en', 'u.last_name_ar as user_last_name_ar',
                'u.title_en as user_title_en', 'u.title_ar as user_title_ar',
                'c.id as company_id', 'c.logo as company_logo',
                'c.name_en as company_name_en', 'c.name_ar as company_name_ar',
                'm.first_name_en as mentor_first_name_en', 'm.first_name_ar as mentor_first_name_ar',
                'm.last_name_en as mentor_last_name_en', 'm.last_name_ar as mentor_last_name_ar',
                'm.img_url as mentor_img_url',
                'msr.rating as mentor_rating',
            ])
            ->where(function ($q) {
                $q->where('mr.approved', 'Yes')
                  ->orWhereNull('mr.approved');
            })
            ->whereBetween('mr.zoom_date', [$start, $end]);

        if ($user_id !== null) {
            $query->where('mr.user_id', $user_id);
        }

        if ($cycle_id !== null) {
            $query->where('mr.cycle_id', $cycle_id);
        }

        $nextSessions = $query->orderBy('mr.zoom_date', 'asc')->get();
        $nextSessions = $nextSessions->groupBy('id')->map(function ($groupedSession) use ($l) {
            $_session = $groupedSession->first();
            $session = new stdClass;
            $session->id = $_session->id;
            $session->mentor_id = $_session->mentor_id;
            $session->user_id = $_session->user_id;
            $session->comment = $_session->comment;
            $session->approved = $_session->approved;
            $session->rating = $_session->rating;
            $session->join_url = $_session->join_url;
            $session->zoom_date = $_session->zoom_date;
            $session->period = $_session->period;
            $session->rate_comment = $_session->rate_comment;
            $session->done = $_session->done;
            $session->cycle_id = $_session->cycle_id;
            $session->organization_id = $_session->organization_id;

            $user = new stdClass;
            $user->id = $_session->user_id;
            $user->image_url = User::image_handler($_session->user_img_url);
            $user->first_name = $_session->{'user_first_name_' . $l};
            $user->last_name = $_session->{'user_last_name_' . $l};
            $user->name = trim($user->first_name . ' ' . $user->last_name);
            $user->title = $_session->{'user_title_' . $l};

            $company = new stdClass;
            $company->id = $_session->company_id;
            $company->logo_url = Company::logo_handler($_session->company_logo);
            $company->name = $_session->{'company_name_' . $l};
            $user->company = $company;
            $session->user = $user;

            $mentor = new stdClass;
            $mentor->id = $_session->mentor_id;
            $mentor->first_name = $_session->{'mentor_first_name_' . $l};
            $mentor->last_name = $_session->{'mentor_last_name_' . $l};
            $mentor->name = trim($mentor->first_name . ' ' . $mentor->last_name);
            $mentor->image_url = User::image_handler($_session->mentor_img_url);
            $mentor->ratings = $groupedSession->sum('mentor_rating');
            $session->mentor = $mentor;
            return $session;
        })->values();
        
        return $nextSessions;

         }
}