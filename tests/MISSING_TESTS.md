# Missing Unit Tests - Complete List

## Overview

This document lists all missing unit tests that should be created to achieve comprehensive test coverage.

## ✅ Tests Created (11 files)

### Services (3/27)
- ✅ `UserServiceTest.php`
- ✅ `AuthServiceTest.php`
- ✅ `CompanyServiceTest.php`

### Repositories (2/10)
- ✅ `UserRepositoryTest.php`
- ✅ `CompanyRepositoryTest.php`

### Models (2/100+)
- ✅ `UserModelTest.php`
- ✅ `CompanyModelTest.php`

### Policies (1/3)
- ✅ `UserPolicyTest.php`

### Feature Tests (3)
- ✅ `Feature/API/AuthTest.php`
- ✅ `Feature/API/UserManagementTest.php`
- ✅ `Feature/Web/AuthenticationTest.php`

---

## ❌ Missing Unit Tests

### Services (24 Missing)

#### High Priority Services

1. **`OrganizationServiceTest.php`** - CRITICAL
   - `listOrganizations()`
   - `createOrganization()` - with user creation, role assignment, email sending
   - `updateOrganization()` - with user update, password change
   - `deleteOrganization()` - soft delete vs force delete logic
   - `uploadImage()`

2. **`ProgramServiceTest.php`** - CRITICAL
   - `getProgramMentors()` - complex query with joins, search, pagination
   - `getProgramStartups()` - filtering, sorting
   - `createProgram()`
   - `updateProgram()`
   - `deleteProgram()`

3. **`CurrencyServiceTest.php`** - HIGH
   - `getCurrencies()`
   - `createCurrency()`
   - `updateCurrency()`
   - `updateAllCurrency()` - exchange rate updates
   - `deleteCurrency()`

4. **`StepServiceTest.php`** - HIGH
   - `createStep()`
   - `updateStep()`
   - `deleteStep()`
   - `reorderSteps()`
   - `changeStepOrder()`

5. **`FormServiceTest.php`** - HIGH
   - `getForms()`
   - `createForm()`
   - `updateForm()`
   - `deleteForm()`
   - `getFormSubmissions()`

6. **`EmployeeServiceTest.php`** - MEDIUM
   - `getEmployees()`
   - `createEmployee()` - with document uploads
   - `updateEmployee()`
   - `deleteEmployee()` - with document cleanup

7. **`NotificationTemplateServiceTest.php`** - MEDIUM
   - `getTemplates()`
   - `createTemplate()`
   - `updateTemplate()`
   - `deleteTemplate()`
   - `cloneTemplate()`
   - `previewTemplate()`
   - `testSend()`

8. **`NotificationSettingsServiceTest.php`** - MEDIUM
   - `getSettings()`
   - `updateSettings()`
   - `getUserSettings()`
   - `updateUserSettings()`

9. **`DashboardServiceTest.php`** - MEDIUM
   - `getStatistics()`
   - `getUserStats()`
   - `getCompanyStats()`
   - `getProgramStats()`

10. **`TrainingReportServiceTest.php`** - MEDIUM
    - `summary()`
    - `sessionTypes()`
    - `monthlyTrends()`
    - `typeBreakdown()`
    - `hoursTrend()`
    - `trainers()`
    - `topics()`
    - `recentSessions()`
    - `getSessionReportsData()`

11. **`MentorshipReportServiceTest.php`** - MEDIUM
    - `summary()`
    - `weeklyTrends()`
    - `mentorHours()`
    - `mentorPerformance()`
    - `startupEngagement()`
    - `trendsByProgram()`
    - `peakActivity()`
    - `sessionReportsData()`

#### Integration Services

12. **`GoogleCalendarServiceTest.php`** - LOW
    - `syncEvents()`
    - `createEvent()`
    - `updateEvent()`
    - `deleteEvent()`

13. **`MicrosoftCalenderServiceTest.php`** - LOW
    - `syncEvents()`
    - `createEvent()`
    - `updateEvent()`
    - `deleteEvent()`

14. **`ZoomServiceTest.php`** - LOW
    - `createMeeting()`
    - `updateMeeting()`
    - `deleteMeeting()`
    - `getRegistrants()`

15. **`GoogleMeetServiceTest.php`** - LOW
    - `createMeeting()`
    - `updateMeeting()`
    - `deleteMeeting()`

16. **`TeamsServiceTest.php`** - LOW
    - `createMeeting()`
    - `updateMeeting()`
    - `deleteMeeting()`

17. **`WhatsAppServiceTest.php`** - LOW
    - `sendMessage()`
    - `createSession()`
    - `getQrCode()`
    - `terminateSession()`

#### Utility Services

18. **`AdvancedExportServiceTest.php`** - MEDIUM
    - `generateExport()`
    - `getExportStatus()`
    - `downloadExport()`
    - `getProgress()`

19. **`NextSessionsServiceTest.php`** - LOW
    - `getNextSessions()`
    - `getUpcomingSessions()`

20. **`GlobalNotificationSettingsServiceTest.php`** - LOW
    - `getGlobalSettings()`
    - `updateGlobalSettings()`

21. **`NotificationEventDispatcherTest.php`** - MEDIUM
    - `dispatch()`
    - `handleEvent()`
    - `sendNotifications()`

22. **`BroadcastConfigServiceTest.php`** - LOW
    - `getConfig()`
    - `updateConfig()`

23. **`MailConfigTest.php`** - LOW
    - `getConfig()`
    - `updateConfig()`
    - `testConnection()`

---

### Repositories (8 Missing)

1. **`OrganizationRepositoryTest.php`** - CRITICAL
   - `allWithRelations()`
   - `counts()`
   - `find()`
   - `findWithCycles()`
   - `create()`
   - `update()`
   - `delete()`
   - `forceDelete()`

2. **`CurrencyRepositoryTest.php`** - HIGH
   - `getEnabled()`
   - `paginate()`
   - `find()`
   - `create()`
   - `update()`
   - `delete()`
   - `getAllExceptUSD()`
   - `transformToDbColumns()`

3. **`ProgramRepositoryTest.php`** - HIGH
   - `findById()`
   - `create()`
   - `update()`
   - `delete()`
   - `getMentors()`
   - `getStartups()`

4. **`StepRepositoryTest.php`** - HIGH
   - `findById()`
   - `create()`
   - `update()`
   - `delete()`
   - `reorder()`
   - `changeOrder()`

5. **`FormRepositoryTest.php`** - MEDIUM
   - `getForms()`
   - `findById()`
   - `create()`
   - `update()`
   - `delete()`
   - `getSubmissions()`

6. **`SettingRepositoryTest.php`** - MEDIUM
   - `get()` - with caching
   - `set()` - with cache invalidation
   - `all()` - with caching

7. **`NotificationSettingsRepositoryTest.php`** - MEDIUM
   - `getSettings()`
   - `updateSettings()`
   - `getUserSettings()`
   - `updateUserSettings()`

8. **`EmployeeRepositoryTest.php`** - MEDIUM
   - `getByCompany()`
   - `create()` - with document handling
   - `update()`
   - `delete()` - with document cleanup

---

### Models (98+ Missing)

#### Critical Models

1. **`RoleModelTest.php`** - CRITICAL
   - Relationships (users, permissions)
   - `hasPermissionTo()`
   - `givePermissionTo()`
   - `revokePermissionTo()`

2. **`OrganizationModelTest.php`** - CRITICAL
   - Relationships (user, cycles, companies)
   - `hasCycles()`
   - `hasCompanies()`
   - Scopes

3. **`CycleModelTest.php`** - CRITICAL
   - Relationships (organization, steps, companies, mentors)
   - `getProgress()`
   - `isActive()`
   - `getCurrentStep()`

4. **`StepModelTest.php`** - CRITICAL
   - Relationships (cycle, form, companies)
   - `isActive()`
   - `getFormData()`

5. **`MentorshipRequestModelTest.php`** - HIGH
   - Relationships (user, mentor, company)
   - `isCompleted()`
   - `canBeRated()`
   - `getDuration()`

6. **`TrainingModelTest.php`** - HIGH
   - Relationships (sessions, quizzes, surveys)
   - `getProgress()`
   - `isCompleted()`

7. **`TrainingSessionModelTest.php`** - HIGH
   - Relationships (training, attendees, recordings)
   - `isUpcoming()`
   - `isPast()`
   - `getAttendanceRate()`

8. **`FormdataModelTest.php`** - HIGH
   - Relationships (company, form, field)
   - `getValue()`
   - `setValue()`

9. **`ScreeningModelTest.php`** - HIGH
   - Relationships (company, form, judges)
   - `getResults()`
   - `isCompleted()`

10. **`CompanyRelationModelTest.php`** - MEDIUM
    - Relationships (company, sector, industry, technology)

11. **`MilestoneModelTest.php`** - MEDIUM
    - Relationships (company)
    - `isAchieved()`
    - `isPlanned()`

12. **`Team_memberModelTest.php`** - MEDIUM
    - Relationships (company, user)
    - `isFounder()`
    - `isShareholder()`
    - `isInvestor()`

13. **`EmployeeModelTest.php`** - MEDIUM
    - Relationships (company)
    - Document handling

14. **`FundingRoundModelTest.php`** - MEDIUM
    - Relationships (company)
    - `getTotalRaised()`

15. **`CurrencyModelTest.php`** - MEDIUM
    - `isEnabled()`
    - `convertToUSD()`
    - `convertFromUSD()`

16. **`SettingsModelTest.php`** - MEDIUM
    - `getValue()`
    - `setValue()`

17. **`NotificationTemplateModelTest.php`** - LOW
    - `render()`
    - `getVariables()`
    - `clone()`

18. **`NotificationTriggerModelTest.php`** - LOW
    - `isActive()`
    - `matchesConditions()`
    - `shouldFire()`

19. **`ExpertiseModelTest.php`** - MEDIUM
    - Relationships (mentors, parent, children)
    - `isParent()`
    - `isChild()`

20. **`SectorModelTest.php`** - MEDIUM
    - Relationships (companies, parent, children)

21. **`StageModelTest.php`** - MEDIUM
    - Relationships (companies)

22. **`CountryModelTest.php`** - LOW
    - Relationships (users, companies, states)

23. **`StateModelTest.php`** - LOW
    - Relationships (country, cities, users, companies)

24. **`CityModelTest.php`** - LOW
    - Relationships (state, users, companies)

#### Other Models (74+)
- `Almuni`
- `AnswerOption`
- `Assessment`
- `AttendanceTrainingOffline`
- `AuditLog`
- `BuilderField`
- `BuilderForm`
- `Calendar`
- `CalenderSync`
- `Category`
- `CompanyRelation`
- `Conversation`
- `CycleCustomPages`
- `CyclesMentors`
- `Documentation`
- `Event`
- `EventsUser`
- `Expertise`
- `Faq`
- `Field`
- `FinalData`
- `Finalscreening`
- `Finance`
- `FormDataJudgesComments`
- `Formfield`
- `Invitation`
- `Label`
- `License`
- `Like`
- `MentorshipSchedule`
- `MentorshipTask`
- `MentorshipTaskComment`
- `MentorshipTaskFile`
- `MentorshipTaskLog`
- `Menu`
- `Message`
- `MessageDraft`
- `Messages`
- `Messenger`
- `Milestone`
- `NotificationHistory`
- `NotificationTemplate`
- `NotificationTrigger`
- `Otp`
- `Pages`
- `Partner`
- `Permission`
- `permissionsGroup`
- `Posts`
- `Press`
- `Product`
- `ProductCategory`
- `Programs`
- `Project`
- `ProjectLikes`
- `Question`
- `QuestionCategories`
- `QuestionsBank`
- `QuizQuestions`
- `QuizResponses`
- `QuizResults`
- `Quizzes`
- `ReminderLog`
- `Room`
- `Room_booking`
- `Rooms_img`
- `Rooms_inclusive`
- `Schedule`
- `ScreeningEvaluation`
- `ScreeningEvaluationCriteria`
- `ScreeningEvaluationCustomFields`
- `ScreeningEvaluationFieldsValues`
- `ScreeningEvaluationResults`
- `Service`
- `ServiceCategory`
- `ServicesRequests`
- `serviceProviderRegistration`
- `serviceProviders`
- `SessionAttendance`
- `SessionRecording`
- `SessionReport`
- `SharedFile`
- `Slider`
- `StaticCategory`
- `SubCategory`
- `SurveyQuestions`
- `SurveyResponses`
- `Surveys`
- `SurveyScopes`
- `SustainableIcon`
- `SwotAnalysis`
- `Tags`
- `TeamFormation`
- `Technology`
- `TimeSheet`
- `TodoList`
- `TrainingCompleted`
- `TrainingContent`
- `TrainingDuration`
- `Type`
- `UserOauthToken`
- `UsersBio`
- `UserScore`
- `Weight`

---

### Policies (2 Missing)

1. **`OrganizationPolicyTest.php`** - HIGH
   - `viewAny()`
   - `create()`
   - `update()`
   - `delete()`

2. **`CurrencyPolicyTest.php`** - MEDIUM
   - `viewAny()`
   - `create()`
   - `update()`
   - `delete()`
   - `updateRates()`

---

### Feature Tests (Many Missing)

#### API Endpoints

1. **`Feature/API/CompanyManagementTest.php`** - CRITICAL
   - GET `/api/companies`
   - GET `/api/companies/{id}`
   - POST `/api/company/profile`
   - POST `/api/company/founder`
   - PUT `/api/company/founder/{id}`
   - DELETE `/api/company/founder/{id}`
   - POST `/api/company/shareholder`
   - POST `/api/company/investor`
   - POST `/api/company/milestones`
   - PUT `/api/company/milestones/{id}`
   - DELETE `/api/company/milestones/{id}`

2. **`Feature/API/ProgramManagementTest.php`** - CRITICAL
   - GET `/api/v1/programs`
   - GET `/api/v1/programs/{id}`
   - POST `/api/programs`
   - POST `/api/programs/{id}`
   - GET `/api/v1/programs/{id}/mentors`
   - GET `/api/v1/programs/{id}/startups`
   - GET `/api/v1/programs/{id}/reports/summary`
   - GET `/api/v1/programs/{id}/reports/performance`

3. **`Feature/API/StepManagementTest.php`** - HIGH
   - POST `/api/v1/programs/{id}/steps`
   - PUT `/api/v1/programs/{id}/steps/{stepId}`
   - DELETE `/api/v1/programs/{id}/steps/{stepId}`
   - PATCH `/api/v1/programs/{id}/steps/reorder`

4. **`Feature/API/MentorshipTest.php`** - HIGH
   - POST `/api/mentorship-sessions`
   - POST `/api/mentorship-sessions/submit-feedback`
   - GET `/api/mentorship-sessions/completed`
   - GET `/api/v1/availability`
   - POST `/api/v1/availability`
   - PUT `/api/v1/availability/{user_id}`
   - DELETE `/api/v1/availability/{id}`

5. **`Feature/API/TrainingTest.php`** - HIGH
   - GET `/api/training/summary`
   - GET `/api/training/session-types`
   - GET `/api/training/monthly-trends`
   - GET `/api/training/type-breakdown`
   - GET `/api/training/hours-trend`
   - GET `/api/training/trainers`
   - GET `/api/training/topics`
   - GET `/api/training/sessions/recent`
   - POST `/api/training/session-reports`

6. **`Feature/API/CurrencyTest.php`** - MEDIUM
   - GET `/api/currencies`
   - GET `/api/currencies-all`
   - POST `/api/currencies`
   - POST `/api/currencies/{id}`
   - DELETE `/api/currencies/{id}`
   - PATCH `/api/currencies/exchange-rates`

7. **`Feature/API/OrganizationTest.php`** - HIGH
   - GET `/api/organizations`
   - POST `/api/organizations`
   - POST `/api/organizations/{id}`
   - DELETE `/api/organizations/{id}`

8. **`Feature/API/MessageTest.php`** - MEDIUM
   - GET `/api/messages`
   - POST `/api/messages`
   - POST `/api/messages/reply`
   - PATCH `/api/messages/toggle-status`
   - PATCH `/api/messages/star`
   - PATCH `/api/messages/trash`
   - DELETE `/api/messages`

9. **`Feature/API/ChatTest.php`** - MEDIUM
   - POST `/api/chat/groups`
   - POST `/api/chat/groups/{id}/add-user`
   - POST `/api/chat/groups/{id}/remove-user`
   - POST `/api/chat/groups/{id}/exit`
   - DELETE `/api/chat/groups/{id}`
   - GET `/api/chat/my-groups`
   - GET `/api/chat/users`
   - GET `/api/chat/users/search`
   - GET `/api/chat/conversations`
   - POST `/api/chat/conversations`
   - GET `/api/chat/conversations/{id}/messages`
   - POST `/api/chat/groups/{id}/messages`
   - DELETE `/api/chat/messages/{id}`

10. **`Feature/API/FormTest.php`** - MEDIUM
    - GET `/api/v1/forms`
    - Form submission tests

11. **`Feature/API/ExpertiseTest.php`** - LOW
    - GET `/api/expertise`
    - POST `/api/expertise`
    - PUT `/api/expertise/{id}`
    - DELETE `/api/expertise/{id}`
    - POST `/api/expertise/import`
    - GET `/api/specializations`
    - POST `/api/specializations`

12. **`Feature/API/ProfileTest.php`** - MEDIUM
    - GET `/api/me`
    - POST `/api/profile`
    - PUT `/api/profile/password`
    - PUT `/api/profile/bio`
    - GET `/api/profile/{id}`

13. **`Feature/API/NotificationSettingsTest.php`** - LOW
    - GET `/api/notification-settings`
    - PUT `/api/notification-settings`
    - GET `/api/users/{id}/notification-settings`

14. **`Feature/API/PermissionTest.php`** - MEDIUM
    - GET `/api/v1/users/me/permissions`
    - GET `/api/v1/{role}/permissions`
    - PUT `/api/v1/{roleId}/permissions`
    - GET `/api/v1/permissions`
    - POST `/api/v1/permissions`
    - PUT `/api/v1/permissions/{id}`
    - DELETE `/api/v1/permissions/{id}`

15. **`Feature/API/RoleTest.php`** - MEDIUM
    - GET `/api/roles`
    - POST `/api/roles`
    - PUT `/api/roles/{id}`
    - DELETE `/api/roles/{id}`

#### Web Routes

16. **`Feature/Web/DashboardTest.php`** - HIGH
    - GET `/dashboard`
    - Dashboard access control
    - Dashboard data loading

17. **`Feature/Web/CompanyManagementTest.php`** - HIGH
    - GET `/dashboard/myCompany`
    - POST `/dashboard/myCompany`
    - POST `/dashboard/AddCompanyTeams`
    - POST `/dashboard/AddCompanyMileStone`
    - POST `/dashboard/delete-document`
    - POST `/dashboard/delete-image`

18. **`Feature/Web/ProgramManagementTest.php`** - HIGH
    - GET `/dashboard/cycles`
    - POST `/dashboard/cycles/save`
    - GET `/dashboard/cycles/view`
    - POST `/dashboard/cycles/modify`

19. **`Feature/Web/UserManagementTest.php`** - MEDIUM
    - GET `/dashboard/users`
    - GET `/dashboard/users/create`
    - POST `/dashboard/users`
    - GET `/dashboard/users/{id}/edit`
    - PUT `/dashboard/users/{id}`
    - DELETE `/dashboard/users/{id}`

20. **`Feature/Web/SettingsTest.php`** - MEDIUM
    - GET `/dashboard/settings`
    - POST `/dashboard/update_settings`
    - PUT `/dashboard/settings/Updateone`
    - PUT `/dashboard/settings/Updatelogo`

21. **`Feature/Web/MentorshipTest.php`** - MEDIUM
    - GET `/dashboard/mentorship/assign`
    - POST `/dashboard/mentorship/assign/{id}`
    - GET `/dashboard/mentorship/view`
    - POST `/dashboard/mentorship/rate`

22. **`Feature/Web/TrainingTest.php`** - MEDIUM
    - GET `/dashboard/training/all`
    - POST `/dashboard/training/store`
    - GET `/dashboard/training/edit/{id}`
    - POST `/dashboard/training/delete/{id}`

23. **`Feature/Web/ScreeningTest.php`** - MEDIUM
    - GET `/dashboard/screening`
    - POST `/dashboard/screening/save`
    - GET `/dashboard/screening/results`

24. **`Feature/Web/FormTest.php`** - MEDIUM
    - GET `/dashboard/forms`
    - GET `/dashboard/forms-submissions`
    - GET `/dashboard/show-builder/{id}`
    - POST `/dashboard/form/{id}/save-form`
    - POST `/dashboard/submit-form`

---

## Priority Summary

### 🔴 CRITICAL (Must Have)
- `OrganizationServiceTest`
- `ProgramServiceTest`
- `OrganizationRepositoryTest`
- `ProgramRepositoryTest`
- `RoleModelTest`
- `OrganizationModelTest`
- `CycleModelTest`
- `StepModelTest`
- `Feature/API/CompanyManagementTest`
- `Feature/API/ProgramManagementTest`
- `Feature/Web/DashboardTest`
- `Feature/Web/CompanyManagementTest`
- `Feature/Web/ProgramManagementTest`

### 🟠 HIGH (Should Have)
- `CurrencyServiceTest`
- `StepServiceTest`
- `FormServiceTest`
- `CurrencyRepositoryTest`
- `StepRepositoryTest`
- `MentorshipRequestModelTest`
- `TrainingModelTest`
- `TrainingSessionModelTest`
- `FormdataModelTest`
- `ScreeningModelTest`
- `OrganizationPolicyTest`
- `Feature/API/StepManagementTest`
- `Feature/API/MentorshipTest`
- `Feature/API/TrainingTest`
- `Feature/API/OrganizationTest`

### 🟡 MEDIUM (Nice to Have)
- `EmployeeServiceTest`
- `NotificationTemplateServiceTest`
- `NotificationSettingsServiceTest`
- `DashboardServiceTest`
- `TrainingReportServiceTest`
- `MentorshipReportServiceTest`
- `AdvancedExportServiceTest`
- `FormRepositoryTest`
- `SettingRepositoryTest`
- `NotificationSettingsRepositoryTest`
- `EmployeeRepositoryTest`
- `CurrencyPolicyTest`
- Remaining model tests
- Remaining API/Web feature tests

### 🟢 LOW (Future)
- Integration service tests (Google, Microsoft, Zoom, etc.)
- Utility service tests
- Remaining model tests
- Edge case tests

---

## Test Creation Order

### Phase 1 (Week 1-2)
1. OrganizationServiceTest
2. ProgramServiceTest
3. OrganizationRepositoryTest
4. ProgramRepositoryTest
5. OrganizationPolicyTest
6. RoleModelTest
7. OrganizationModelTest
8. CycleModelTest
9. StepModelTest

### Phase 2 (Week 3-4)
10. CurrencyServiceTest
11. StepServiceTest
12. FormServiceTest
13. CurrencyRepositoryTest
14. StepRepositoryTest
15. Feature/API/CompanyManagementTest
16. Feature/API/ProgramManagementTest
17. Feature/Web/DashboardTest

### Phase 3 (Week 5-6)
18. Remaining service tests
19. Remaining repository tests
20. Remaining model tests
21. Remaining feature tests

---

## Estimated Test Count

- **Services**: 24 missing × ~10 tests each = **~240 tests**
- **Repositories**: 8 missing × ~8 tests each = **~64 tests**
- **Models**: 98+ missing × ~5 tests each = **~490 tests**
- **Policies**: 2 missing × ~5 tests each = **~10 tests**
- **Feature Tests**: 24 missing × ~10 tests each = **~240 tests**

**Total Missing: ~1,044 tests**

---

## Notes

- Focus on CRITICAL and HIGH priority tests first
- Use factories for all test data
- Mock external services (email, SMS, APIs)
- Test both success and failure scenarios
- Include edge cases and boundary conditions
- Maintain test independence (use RefreshDatabase)
- Aim for 70%+ code coverage

