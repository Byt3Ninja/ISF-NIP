# Quick Testing Guide - Task Per Program Feature

## Access the Feature
URL: `http://localhost:8000/dashboard/tasks`

## Test Cases

### 1. Create New Task (Happy Path)
**Steps:**
1. Click "Add New Task" button (top right or sidebar)
2. Fill in the form:
   - **Task Name**: "Test Task for Program X" (required)
   - **Description**: "This is a test task description" (optional)
   - **Status**: Select "Pending" (required)
   - **Priority**: Select "High" (required)
   - **Due Date**: Select a future date using date picker (optional)
   - **Program**: Select a program from dropdown (required)
   - **Assignees**: Auto-selected, can modify (required)
   - **Tags**: Select one or more tags from dropdown (optional)
   - **Attachments**: Upload files if needed (optional)
3. Click "Submit"

**Expected Result:**
- Success message: "Task created successfully" / "تم إنشاء المهمة بنجاح"
- Task appears in the table
- Modal closes

### 2. Test Required Field Validation
**Steps:**
1. Click "Add New Task"
2. Leave required fields empty
3. Click "Submit"

**Expected Result:**
- Validation errors appear for:
  - Task Name: "Please fill in all required fields"
  - Program: "Please select a program"
  - Priority: "Please select priority"
  - Status: "Please select status"
  - Assignees: "Please select at least one assignee"

### 3. Test File Upload - Valid Files
**Steps:**
1. Click "Add New Task"
2. Fill required fields
3. Upload files with these extensions: .pdf, .docx, .xlsx, .png, .jpg, .jpeg
4. Each file should be under 10MB
5. Click "Submit"

**Expected Result:**
- Files upload successfully
- Task created with attachments

### 4. Test File Upload - Invalid File Type
**Steps:**
1. Click "Add New Task"
2. Try to upload: .doc, .ppt, .xls, .txt, .zip
3. Click "Submit"

**Expected Result:**
- Error message: "Unsupported file type. Please upload files in PDF, DOCX, XLSX, PNG, JPG, or JPEG format."
- Arabic: "نوع الملف غير مدعوم. يرجى تحميل ملفات بصيغة PDF أو DOCX أو XLSX أو PNG أو JPG أو JPEG."

### 5. Test File Upload - Size Limit
**Steps:**
1. Click "Add New Task"
2. Try to upload a file larger than 10MB
3. Click "Submit"

**Expected Result:**
- Error message: "Attachment size exceeds the allowed limit (10 MB)."
- Arabic: "يتجاوز حجم المرفق الحد المسموح به (10 ميجابايت)"

### 6. Test Status Options
**Steps:**
1. Click "Add New Task"
2. Check Status dropdown

**Expected Options:**
- Pending
- In Progress
- Review (NOT "In Review")
- Completed

### 7. Test Due Date Picker
**Steps:**
1. Click "Add New Task"
2. Click on Due Date field

**Expected Result:**
- Native date picker appears
- Cannot select dates in the past
- Can select today or future dates

### 8. Test Tags Selection
**Steps:**
1. Click "Add New Task"
2. Click on Tags field

**Expected Result:**
- Dropdown shows list of existing tags
- Can select multiple tags
- Can search/filter tags
- Selected tags appear as badges

### 9. Test Program Selection & Auto-Assignment
**Steps:**
1. Click "Add New Task"
2. Select a program from dropdown

**Expected Result:**
- Assignees section appears
- All startups linked to that program are auto-selected
- Can manually deselect or select specific startups

### 10. Edit Existing Task
**Steps:**
1. Find a task in the table
2. Click the edit icon (pencil)
3. Modify any field
4. Click "Submit"

**Expected Result:**
- Modal opens with pre-filled data
- Can modify all fields
- Success message: "Task updated successfully"
- Changes reflect in table

### 11. View Task Details
**Steps:**
1. Find a task in the table
2. Click the view icon (eye)

**Expected Result:**
- Modal shows full task details
- Shows all assignees with their statuses
- Shows tags if any
- Shows due date
- Can trigger status updates for assignees

### 12. Delete Task
**Steps:**
1. Find a task in the table
2. Click the delete icon (trash)
3. Confirm deletion

**Expected Result:**
- Confirmation dialog appears
- After confirming, task is deleted
- Success message appears
- Task removed from table

### 13. Filter Tasks
**Steps:**
1. Use the filter dropdowns above the table:
   - Filter by Program
   - Filter by Priority
   - Filter by Status

**Expected Result:**
- Table updates to show only matching tasks
- Multiple filters can be applied together

### 14. Search Tasks
**Steps:**
1. Use the search box
2. Type task name or description

**Expected Result:**
- Table filters to show matching tasks
- Search works across title and description

### 15. Export Tasks
**Steps:**
1. Click "Export" button
2. Select CSV or Excel

**Expected Result:**
- File downloads with current task data
- Includes all visible columns

## Common Issues & Solutions

### Issue: Submit button doesn't work
**Solution:** 
- Check browser console for errors
- Ensure all required fields are filled
- Verify Select2 is initialized properly

### Issue: Tags don't appear
**Solution:**
- Ensure tags exist in database (Tags table)
- Check Select2 initialization
- Verify tags are passed to view

### Issue: Date picker doesn't work
**Solution:**
- Using native HTML5 date input
- Should work on all modern browsers
- On older browsers, falls back to text input

### Issue: Files won't upload
**Solution:**
- Check file type (.pdf, .docx, .xlsx, .png, .jpg, .jpeg only)
- Check file size (max 10MB per file)
- Check server upload limits in php.ini

## Browser Console Checks

Open browser console (F12) and check for:
- No JavaScript errors
- Select2 initialized successfully
- Form validation working
- AJAX requests completing successfully

## Database Verification

After creating a task, check database:
```sql
-- Check task was created
SELECT * FROM program_tasks ORDER BY created_at DESC LIMIT 1;

-- Check assignees were linked
SELECT * FROM program_task_assignees WHERE program_task_id = [TASK_ID];

-- Check status values are correct
SELECT DISTINCT status FROM program_tasks;
-- Should show: Pending, In Progress, Review, Completed
```

## API Testing (Optional)

Use Postman or curl to test API directly:

```bash
# Create Task
curl -X POST http://localhost:8000/dashboard/tasks \
  -H "Content-Type: multipart/form-data" \
  -F "title=API Test Task" \
  -F "program_id=1" \
  -F "priority=High" \
  -F "status=Pending" \
  -F "assignees[]=1" \
  -F "assignees[]=2"

# Update Task
curl -X PUT http://localhost:8000/dashboard/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated Task","status":"Review"}'

# Delete Task
curl -X DELETE http://localhost:8000/dashboard/tasks/1
```

## Success Criteria

✅ All required fields validate correctly
✅ Optional fields work as expected
✅ File uploads work with correct types and sizes
✅ Tasks are created and assigned to startups
✅ Edit and delete functions work
✅ Filters and search work
✅ Export functions work
✅ Both English and Arabic messages display correctly
✅ No JavaScript console errors
✅ Responsive design works on mobile
