<?php

namespace App\Http\Controllers;

use App\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of the notification templates.
     */
    public function index(Request $request)
    {
        $query = NotificationTemplate::with('creator');
        
        // Filter by channel if provided
        if ($request->has('channel')) {
            $query->byChannel($request->channel);
        }
        
        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
        
        // Search by name or title
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        
        $templates = $query->orderBy('created_at', 'desc')->paginate(15);
        
        if ($request->expectsJson()) {
            return response()->json($templates);
        }
        
        return view('admin.notification-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new notification template.
     */
    public function create()
    {
        $channels = ['email', 'whatsapp', 'pusher'];
        $sampleData = NotificationTemplate::getSampleData();
        
        return view('admin.notification-templates.create', compact('channels', 'sampleData'));
    }

    /**
     * Store a newly created notification template.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:notification_templates',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'channel' => 'required|in:email,whatsapp,pusher',
            'subject' => 'required_if:channel,email|nullable|string|max:255',
            'content' => 'required|string',
            'sender' => 'nullable|string|max:255',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $template = new NotificationTemplate($request->all());
        $template->created_by = Auth::id();
        $template->variables = $template->extractVariables();
        $template->save();

        if ($request->expectsJson()) {
            return response()->json($template->load('creator'), 201);
        }

        return redirect()->route('admin.notification-templates.index')
            ->with('success', 'Notification template created successfully.');
    }

    /**
     * Display the specified notification template.
     */
    public function show(NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->load('creator', 'parentTemplate', 'childTemplates');
        
        if (request()->expectsJson()) {
            return response()->json($notificationTemplate);
        }
        
        return view('admin.notification-templates.show', compact('notificationTemplate'));
    }

    /**
     * Show the form for editing the specified notification template.
     */
    public function edit(NotificationTemplate $notificationTemplate)
    {
        $channels = ['email', 'whatsapp', 'pusher'];
        $sampleData = NotificationTemplate::getSampleData();
        
        return view('admin.notification-templates.edit', compact('notificationTemplate', 'channels', 'sampleData'));
    }

    /**
     * Update the specified notification template.
     */
    public function update(Request $request, NotificationTemplate $notificationTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:notification_templates,name,' . $notificationTemplate->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'channel' => 'required|in:email,whatsapp,pusher',
            'subject' => 'required_if:channel,email|nullable|string|max:255',
            'content' => 'required|string',
            'sender' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $notificationTemplate->fill($request->all());
        $notificationTemplate->variables = $notificationTemplate->extractVariables();
        $notificationTemplate->save();

        if ($request->expectsJson()) {
            return response()->json($notificationTemplate->load('creator'));
        }

        return redirect()->route('admin.notification-templates.index')
            ->with('success', 'Notification template updated successfully.');
    }

    /**
     * Remove the specified notification template.
     */
    public function destroy(NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Template deleted successfully.']);
        }

        return redirect()->route('admin.notification-templates.index')
            ->with('success', 'Notification template deleted successfully.');
    }

    /**
     * Preview the notification template with sample data.
     */
    public function preview(Request $request, NotificationTemplate $notificationTemplate)
    {
        $sampleData = NotificationTemplate::getSampleData();
        
        // Allow custom preview data
        if ($request->has('preview_data')) {
            $customData = $request->preview_data;
            $sampleData = array_merge($sampleData, $customData);
        }
        
        $renderedContent = $notificationTemplate->render($sampleData);
        
        return response()->json([
            'rendered_content' => $renderedContent,
            'sample_data' => $sampleData,
            'variables' => $notificationTemplate->variables
        ]);
    }

    /**
     * Clone/duplicate an existing template.
     */
    public function clone(Request $request, NotificationTemplate $notificationTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:notification_templates',
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

        $clonedTemplate = $notificationTemplate->cloneTemplate($updates);

        if ($request->expectsJson()) {
            return response()->json($clonedTemplate->load('creator'), 201);
        }

        return redirect()->route('admin.notification-templates.edit', $clonedTemplate)
            ->with('success', 'Template cloned successfully. You can now modify it.');
    }

    /**
     * Test send a notification using the template.
     */
    public function testSend(Request $request, NotificationTemplate $notificationTemplate)
    {
        $validator = Validator::make($request->all(), [
            'test_email' => 'required_if:channel,email|email',
            'test_phone' => 'required_if:channel,whatsapp|string',
            'test_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $testData = $request->test_data ?? NotificationTemplate::getSampleData();
        
        try {
            // Here you would implement the actual sending logic based on channel
            switch ($notificationTemplate->channel) {
                case 'email':
                    // Send test email
                    $this->sendTestEmail($notificationTemplate, $request->test_email, $testData);
                    break;
                case 'whatsapp':
                    // Send test WhatsApp message
                    $this->sendTestWhatsApp($notificationTemplate, $request->test_phone, $testData);
                    break;
                case 'pusher':
                    // Send test push notification
                    $this->sendTestPush($notificationTemplate, $testData);
                    break;
            }
            
            return response()->json(['message' => 'Test notification sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send test notification: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get available variables for templates.
     */
    public function getVariables()
    {
        $variables = [
            'user' => [
                'name' => 'User full name',
                'email' => 'User email address',
                'first_name' => 'User first name',
                'last_name' => 'User last name'
            ],
            'company' => [
                'name' => 'Company name',
                'website' => 'Company website'
            ],
            'order' => [
                'id' => 'Order ID',
                'total' => 'Order total amount',
                'date' => 'Order date'
            ],
            'system' => [
                'app_name' => 'Application name',
                'support_email' => 'Support email address'
            ]
        ];
        
        return response()->json($variables);
    }

    // Private helper methods for test sending
    private function sendTestEmail($template, $email, $data)
    {
        // Implementation for sending test email
        // This would integrate with your existing email system
    }

    private function sendTestWhatsApp($template, $phone, $data)
    {
        // Implementation for sending test WhatsApp message
        // This would integrate with your WhatsApp API
    }

    private function sendTestPush($template, $data)
    {
        // Implementation for sending test push notification
        // This would integrate with your Pusher setup
    }
}
