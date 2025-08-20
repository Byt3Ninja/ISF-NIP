<?php

namespace App\Http\Controllers;

use App\NotificationTrigger;
use App\NotificationTemplate;
use App\Services\NotificationEventDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationTriggerController extends Controller
{
    protected $eventDispatcher;

    public function __construct(NotificationEventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Display a listing of notification triggers.
     */
    public function index(Request $request)
    {
        $query = NotificationTrigger::with(['template', 'creator']);

        // Apply filters
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $triggers = $query->orderBy('priority', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(15);

        if ($request->expectsJson()) {
            return response()->json($triggers);
        }

        $availableEvents = NotificationTrigger::getAvailableEvents();

        return view('admin.notification-triggers.index', compact('triggers', 'availableEvents'));
    }

    /**
     * Show the form for creating a new notification trigger.
     */
    public function create()
    {
        $templates = NotificationTemplate::where('is_active', true)->get();
        $availableEvents = NotificationTrigger::getAvailableEvents();
        $recipientRuleTypes = NotificationTrigger::getRecipientRuleTypes();

        return view('admin.notification-triggers.create', compact('templates', 'availableEvents', 'recipientRuleTypes'));
    }

    /**
     * Store a newly created notification trigger.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:notification_triggers',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event' => 'required|string|max:255',
            'template_id' => 'required|exists:notification_templates,id',
            'recipient_rules' => 'required|array|min:1',
            'recipient_rules.*.type' => 'required|string',
            'recipient_rules.*.value' => 'nullable|string',
            'conditions' => 'nullable|array',
            'priority' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $trigger = NotificationTrigger::create([
            'name' => $request->name,
            'title' => $request->title,
            'description' => $request->description,
            'event' => $request->event,
            'template_id' => $request->template_id,
            'recipient_rules' => $request->recipient_rules,
            'conditions' => $request->conditions ?? [],
            'priority' => $request->priority ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'settings' => $request->settings ?? [],
            'created_by' => Auth::id()
        ]);

        if ($request->expectsJson()) {
            return response()->json($trigger->load(['template', 'creator']), 201);
        }

        return redirect()->route('notification-triggers.show', $trigger)
            ->with('success', 'Notification trigger created successfully.');
    }

    /**
     * Display the specified notification trigger.
     */
    public function show(NotificationTrigger $notificationTrigger)
    {
        $notificationTrigger->load(['template', 'creator']);
        
        return view('admin.notification-triggers.show', compact('notificationTrigger'));
    }

    /**
     * Show the form for editing the specified notification trigger.
     */
    public function edit(NotificationTrigger $notificationTrigger)
    {
        $templates = NotificationTemplate::where('is_active', true)->get();
        $availableEvents = NotificationTrigger::getAvailableEvents();
        $recipientRuleTypes = NotificationTrigger::getRecipientRuleTypes();

        return view('admin.notification-triggers.edit', compact('notificationTrigger', 'templates', 'availableEvents', 'recipientRuleTypes'));
    }

    /**
     * Update the specified notification trigger.
     */
    public function update(Request $request, NotificationTrigger $notificationTrigger)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:notification_triggers,name,' . $notificationTrigger->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event' => 'required|string|max:255',
            'template_id' => 'required|exists:notification_templates,id',
            'recipient_rules' => 'required|array|min:1',
            'recipient_rules.*.type' => 'required|string',
            'recipient_rules.*.value' => 'nullable|string',
            'conditions' => 'nullable|array',
            'priority' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $notificationTrigger->update([
            'name' => $request->name,
            'title' => $request->title,
            'description' => $request->description,
            'event' => $request->event,
            'template_id' => $request->template_id,
            'recipient_rules' => $request->recipient_rules,
            'conditions' => $request->conditions ?? [],
            'priority' => $request->priority ?? 0,
            'is_active' => $request->boolean('is_active'),
            'settings' => $request->settings ?? []
        ]);

        if ($request->expectsJson()) {
            return response()->json($notificationTrigger->load(['template', 'creator']));
        }

        return redirect()->route('notification-triggers.show', $notificationTrigger)
            ->with('success', 'Notification trigger updated successfully.');
    }

    /**
     * Remove the specified notification trigger.
     */
    public function destroy(NotificationTrigger $notificationTrigger)
    {
        $notificationTrigger->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Notification trigger deleted successfully.']);
        }

        return redirect()->route('notification-triggers.index')
            ->with('success', 'Notification trigger deleted successfully.');
    }

    /**
     * Toggle the active status of a notification trigger.
     */
    public function toggleActive(NotificationTrigger $notificationTrigger)
    {
        $notificationTrigger->update([
            'is_active' => !$notificationTrigger->is_active
        ]);

        $status = $notificationTrigger->is_active ? 'activated' : 'deactivated';

        if (request()->expectsJson()) {
            return response()->json([
                'message' => "Notification trigger {$status} successfully.",
                'is_active' => $notificationTrigger->is_active
            ]);
        }

        return back()->with('success', "Notification trigger {$status} successfully.");
    }

    /**
     * Clone a notification trigger.
     */
    public function clone(Request $request, NotificationTrigger $notificationTrigger)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:notification_triggers',
            'title' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $updates = [
            'name' => $request->name,
            'created_by' => Auth::id()
        ];
        
        if ($request->has('title')) {
            $updates['title'] = $request->title;
        }

        $clonedTrigger = $notificationTrigger->cloneTrigger($updates);

        if ($request->expectsJson()) {
            return response()->json($clonedTrigger->load(['template', 'creator']), 201);
        }

        return redirect()->route('notification-triggers.edit', $clonedTrigger)
            ->with('success', 'Notification trigger cloned successfully. You can now modify it.');
    }

    /**
     * Test a notification trigger.
     */
    public function test(Request $request, NotificationTrigger $notificationTrigger)
    {
        $validator = Validator::make($request->all(), [
            'test_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator);
        }

        try {
            $testData = $request->test_data ?? [];
            $this->eventDispatcher->testTrigger($notificationTrigger, $testData);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Test notification sent successfully.']);
            }

            return back()->with('success', 'Test notification sent successfully. Check the logs for details.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to send test notification: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Failed to send test notification: ' . $e->getMessage());
        }
    }

    /**
     * Get sample conditions for an event.
     */
    public function getSampleConditions(Request $request)
    {
        $event = $request->get('event');
        $sampleConditions = NotificationTrigger::getSampleConditions($event);

        return response()->json($sampleConditions);
    }

    /**
     * Get available events.
     */
    public function getEvents()
    {
        $events = NotificationTrigger::getAvailableEvents();
        return response()->json($events);
    }

    /**
     * Get recipient rule types.
     */
    public function getRecipientRuleTypes()
    {
        $types = NotificationTrigger::getRecipientRuleTypes();
        return response()->json($types);
    }
}
