# Enhanced Consolidated Release Notes

**Version Range**: v2.0.0 → v3.5.0

This document provides a detailed and professional summary of all changes from version 2.0.0 to 3.5.0. The original item names, which were in a mix of English and Arabic, have been analyzed and rewritten to provide clearer and more informative descriptions.

---

## v2.0.0

**Summary**: This release includes 17 new features, 9 bug fixes, 0 enhancements, and 2 completed tasks.

### ✨ Features

- Implemented migration seed data for the Incubator system to facilitate initial setup and ensure consistent baseline data across environments.
- Removed service-related functionalities and options from mentor accounts to streamline user roles and permissions.
- Updated and revised the status labels for guidance session requests to improve clarity and user understanding.
- Fixed the issue preventing user registration via Google and Facebook authentication, restoring social login functionality.
- Added an email notification feature to send account activation messages upon user registration.
- Resolved date selection and functionality issues encountered when adding a new phase from the institution account interface.
- Fixed a system crash occurring when uploading a logo image during new institution creation; the system now handles logo uploads without errors.
- Added the ability to display forms related to a specific course within the forms template for better organization and access.
- Introduced a new "Programs" menu to enhance navigation and access to program-related features.
- Implemented a confirmation message upon successful saving of admin changes to improve user feedback.
- Updated company membership settings to restrict regions and governorates selections to those within Saudi Arabia only.
- [Startup] Hidden programs that have passed their expiration date to prevent user access to outdated offerings.
- [Startup] Disabled the option to join programs once their active period has ended, ensuring enrollment accuracy.
- [Startup] Created configurable settings for "Sectors" to allow better categorization and management of startup programs.
- Enabled Sentry error tracking and monitoring to improve application stability and facilitate issue resolution.
- Upgraded the Laravel framework to the latest stable version to leverage new features, improvements, and security patches.
- Updated all project dependencies and upgraded the PHP version to the latest release to ensure compatibility and performance enhancements.

### 🐛 Fixes

- Fixed an issue where profile pictures, names, and job titles were not displaying correctly in the Add Mentors list.
- Resolved a problem preventing access to company profiles after deleting training courses.
- Corrected the Org Manager dashboard to ensure displayed numbers accurately reflect the current data.
- Fixed an issue in startup accounts where updating company details after registration did not save properly.
- Updated the notification message shown to startups when registering for a new cycle to improve clarity.
- Enabled editing of service titles within the Admin account settings.
- Modified Org Manager to display only mentorships relevant to the current cycle, excluding all previous records.
- Fixed a color display issue when adding a new organization through the Admin interface.
- Enabled editing of personal information fields across all account types.

### 🛠️ Tasks

- Completed the migration of Arweqah data to the new system, ensuring data integrity and consistency throughout the process.
- Deployed and configured a new Docker container on the Test server to support application testing and development activities.

---

## v2.1.0

**Summary**: This release includes 17 new features, 5 bug fixes, 5 enhancements, and 2 completed tasks.

### ✨ Features

- Reestablished connection with the new Sentry account to improve error tracking and monitoring capabilities.
- Removed or concealed menu items that are not relevant to the user’s role to streamline navigation and improve user experience.
- Enabled administrators and organizations to add multiple mentor sessions simultaneously through a bulk upload feature.
- Implemented parallel processing of program phases to allow simultaneous progress and reduce overall program duration.
- Improved the data type handling for time-related fields on the platform to enhance accuracy and consistency in time display.
- Updated the integration with Arweqah Zoom to ensure compatibility and improve the reliability of video conferencing features.
- Developed a dedicated dashboard for mentors to provide an overview of their sessions, schedules, and relevant metrics.
- Created dashboards for organization managers featuring program management tools and comprehensive reporting capabilities.
- Introduced dashboards for administrators and organization managers focused on trainer-related data and performance reports.
- Added dashboard functionality for administrators and organization managers to access mentor-related data and reporting insights.
- Implemented a dashboard widget for administrators and organization managers showing the count of startup applications currently under screening or review by judges.
- Added a dashboard metric displaying the total number of organizations registered within the system for administrators and organization managers.
- Enhanced the Judge account calendar with improved usability and additional features for better scheduling and case management.
- Added a direct menu link in the Mentor account interface for quick access to the “My Schedule” page.
- Developed a comprehensive user profile page for mentors to manage their personal information, expertise, and availability.
- Enabled automated email notifications to startups informing them of any changes in their program cycle status.
- Introduced a configurable pipeline setup feature to allow customization of workflow stages and process management.

### 🐛 Fixes

- Updated the display and management of change cycle information to improve accuracy and usability.
- Added a success message confirmation upon creating a mentor session to enhance user feedback.
- Fixed an unhandled exception that caused the application to crash under certain conditions.
- Improved the visibility and layout of upcoming meetings on the company page by presenting them in a clearer, tabular format.
- Resolved an issue where mentoring and training sessions were not displayed for companies in advanced stages, ensuring these sessions now appear alongside training courses.

### 🚀 Enhancements

- Added new test cases to improve overall test coverage and ensure software quality.
- Expanded the test case suite with additional scenarios to validate key functionalities.
- Implemented a quick link feature to add events directly to Google Calendar for enhanced user convenience.
- Developed further test cases targeting recent feature updates to verify stability and performance.
- Included supplementary test cases to address edge conditions and improve defect detection.

### 🛠️ Tasks

- Updated the startup dashboard to display the organization’s logo above the program list for improved branding and visual identification.
- Fixed an issue preventing company calendars from syncing scandal events, ensuring all relevant events are properly synchronized.

---

## v2.2.0

**Summary**: This release includes 8 new features, 1 bug fixes, 0 enhancements, and 1 completed tasks.

### ✨ Features

- Added support for Microsoft Teams integration, enabling seamless collaboration and communication within the platform.
- Implemented a reply tasks feature on the startup page to facilitate task management and improve user workflow.
- Developed a Startup Progress page accessible to Admins and Organization Managers, providing detailed reports on startup performance and milestones.
- Enabled handling of the Meeting App configuration through environment variables to improve deployment flexibility and security.
- Introduced document upload and management capabilities for elevator pitches within the Magnitt Startup module, streamlining content organization.
- Enhanced the Magnitt Startup profile by adding a Company Overview section to provide a comprehensive summary of the startup’s business.
- Launched Calendar-Connect functionality to synchronize events and schedules across integrated calendar applications.
- Added investment information features to the Magnitt Startup module, allowing startups to input and manage their funding details effectively.

### 🐛 Fixes

- Improved the user experience for mentorship requests by addressing usability issues and enhancing interface responsiveness.

### 🛠️ Tasks

- Added a new field to the Trainer invitation form to include the Trainer's email address.

---

## v2.3.0

**Summary**: This release includes 46 new features, 28 bug fixes, 18 enhancements, and 1 completed tasks.

### ✨ Features

- Updated the message for unassigned training from "خطأ" to "انت غير مشترك فى اى برامج تدريبية" to provide clearer communication when a user is not enrolled in any training programs.
- Implemented restrictions to limit users’ ability to view scheduled training and mentorship sessions based on their roles and permissions.
- Added functionality to arrange and filter mentorship session requests, enabling easier management and prioritization of incoming requests.
- Enabled multi-selection and multi-action capabilities on the startup page to allow users to perform batch operations efficiently.
- Developed and enhanced the user interface for the Super Admin Dashboard to improve usability and data visualization.
- Redesigned the Registration Page UI for a more intuitive and user-friendly sign-up experience.
- Updated the Reset Password UI to streamline the password recovery process with clearer instructions and layout.
- Improved the Login Page UI to enhance accessibility and provide a smoother login experience.
- Created a comprehensive Super Admin Settings Page to centralize configuration options and system management tools.
- Added a Quiz feature to support assessments and knowledge checks within the platform.
- Introduced a Program List page accessible from the side menu for quick navigation and management of available programs.
- Enhanced the Startup Dashboard UI to provide startups with better insights and easier access to relevant data.
- Updated the Mentor Dashboard UI to improve mentor engagement and session tracking.
- Refined the Organisation Admin Dashboard UI to facilitate administrative tasks and organizational oversight.
- Applied updates to the global layout to ensure consistent design and improved responsiveness across the platform.
- Enabled exporting of report data to CSV and Excel formats to support data analysis and offline review.
- Improved the View Profile UI for clearer presentation of user information.
- Updated the Edit Profile UI to simplify profile modifications and enhance user experience.
- Enhanced the Calendar UI for better event visualization and interaction.
- Added functionality for startups to cancel or reschedule mentorship sessions, ensuring compatibility with the existing session management system.
- Removed the Edit Profile options from the Billing, Plans, and Notification tabs to streamline user settings and reduce confusion.
- Implemented a timesheet feature to track and manage time entries for users and mentors.
- Developed Tags Management functionality to organize and categorize content or users effectively.
- Added a language switcher to the Login and Registration pages to support multilingual user access.
- Created a new layout for the course and training creation interface to improve usability for content creators.
- Designed and implemented an unauthorized access page to inform users when they attempt to access restricted areas.
- Developed a custom 404 error page to guide users when they navigate to non-existent pages.
- Fixed icon display issues in the Organization List to enhance visual consistency.
- Implemented phone number validation to ensure correct format and improve data accuracy during user input.
- Corrected layout issues on the Session Page to improve readability and user interaction.
- Updated the Program Dashboard to include a comprehensive Program List for easier program management.
- Modified the profile page to display the "Joined At" date, providing users with information about their registration timeline.
- Updated the UI for cycle list action icons to improve clarity and usability.
- Fixed layout and functionality issues on the Report Page to enhance data presentation and user experience.
- Enabled mentor registration on the GrowthLabs platform, allowing mentors to create accounts and participate in the ecosystem.
- Added a feature for Startup Founders to view a detailed list of all available courses, including relevant information, to facilitate informed course selection and enrollment.
- Provided Program Managers with tools to view mentors’ activities and session records to monitor engagement and assess performance.
- Allowed users to view mentor availability and submit session requests, enabling efficient scheduling of mentorship meetings.
- Enabled users to access detailed mentor profiles, including experience and background information, to support informed mentor selection.
- Developed a searchable list of available mentors for users to browse and choose the most suitable mentor for their needs.
- Empowered Program Managers to create new courses within the LMS, facilitating the delivery of training materials to users.
- Added new configuration settings to the Settings Page to enhance system customization and control.
- Merged the frontend development branch and resolved conflicts to integrate recent code changes smoothly.
- Enabled Admins and Organization Managers to embed Microsoft SharePoint videos within training modules, enriching training content delivery.
- Introduced a pending survey widget for startups to track and complete outstanding surveys efficiently.
- Added functionality for mentors to assign tasks to startups, with visibility of these tasks extended to Admins and Organization Managers for oversight and tracking.

### 🐛 Fixes

- Fixed an issue preventing proper switching of organization status within the system.
- Resolved a problem occurring when selecting the question type "True or False."
- Addressed an error that occurred during user profile updates to ensure data is saved correctly.
- Corrected a bug affecting the posting of mentor session tasks to improve task management.
- Updated and corrected phrasing in the company profile section for clarity and accuracy.
- Fixed the issue where the mentors page was not visible in mentor accounts.
- Resolved functionality issues within the admin "My Profile" section to improve user experience.
- Fixed bugs in the mentor "My Profile" section to ensure accurate profile display and editing.
- Corrected scoring calculation errors to ensure accurate assessment results.
- Fixed issues related to judge result processing to ensure correct evaluation outcomes.
- Resolved problems with session approval workflows to enable proper session validation.
- Added functionality to allow adding mentorship sessions directly from the cycle interface.
- Fixed selection issues related to judge assignment to improve session management.
- Resolved various bugs affecting mentorship session handling for smoother operation.
- Improved the UI/UX design of task management screens for better usability.
- Fixed issues affecting the "My Training" section to ensure accurate training records.
- Addressed errors occurring when adding new sessions to the system.
- Implemented a filter to allow users to view only their sessions with assigned mentors.
- Fixed company logo size display issues in the program list for consistent branding.
- Corrected right-to-left (RTL) layout issues in the program list for proper language support.
- Fixed display errors related to free space availability in the program list.
- Resolved UI issues in the course list's edit function to improve editing experience.
- Fixed the problem causing private links to be invalid or inaccessible.
- Corrected errors encountered when adding new programs to ensure successful creation.
- Updated profile fields to reflect recent changes and improve data accuracy.
- Fixed an issue preventing the mentor list from displaying correctly in startup accounts.
- Resolved language switch bug that caused improper switching between RTL and LTR languages.
- Fixed 401 Unauthorized errors occurring when accessing programs via private links.

### 🚀 Enhancements

- Added functionality to export report data in both CSV and PDF formats, enabling easier data sharing and offline analysis.
- Implemented access restrictions to limit the visibility of scheduled training and mentorship sessions based on user roles or permissions.
- Developed a dedicated Startup Quiz Dashboard to provide startups with an overview and management tools for their quizzes.
- Introduced a comprehensive Quiz Dashboard for users to monitor quiz performance, progress, and related metrics.
- Enabled the assignment of quizzes directly to training sessions, streamlining the integration of assessments within training programs.
- Added the ability to create new quizzes by selecting from a predefined list of questions, simplifying quiz creation.
- Expanded quiz question types to include multiple choice and true/false formats, enhancing quiz variety and assessment options.
- Provided a fully localized Arabic version of all forms to support Arabic-speaking users and improve accessibility.
- Improved the user experience (UX) for the course listing page, making it more intuitive and easier to navigate.
- Enhanced the UX design for the mentors’ portfolio section to better showcase mentor profiles and expertise.
- Redesigned the user interface and experience for adding new courses, facilitating a smoother course creation process.
- Added a Pending Survey widget to the Startup interface, allowing users to easily view and manage outstanding surveys.
- Conducted testing of task deletion functionality for mentors to ensure proper behavior and data integrity.
- Tested the task list feature within mentorship sessions from the mentor’s perspective to verify accuracy and usability.
- Performed testing on the session tasks list for mentors to confirm correct display and interaction.
- Validated the mentorship session tasks page functionality through comprehensive mentor-side testing.
- Tested the task detail view page to ensure all task information is displayed correctly and consistently.
- Conducted testing on the add/edit task pages for mentors to verify form functionality and data handling.

### 🛠️ Tasks

- Conducted a comprehensive review of the project to assess progress, identify issues, and ensure alignment with objectives.

---

## v3.0.0

**Summary**: This release includes 53 new features, 100 bug fixes, 35 enhancements, and 2 completed tasks.

### ✨ Features

- Implemented user experience improvements for the Add/Edit functionality within Program Management to enhance usability and workflow efficiency.
- Added a new survey question type called "Question and Answer" to expand the variety of question formats available for surveys.
- Redesigned the Organization list interface to improve navigation, readability, and overall user experience.
- Fixed an issue where the LinkedIn URL in the Mentor Profile was not being saved correctly, ensuring proper data retention.
- Added a direct link in the startup panel allowing users to easily submit surveys from within their dashboard.
- Developed the backend functionality for generating and processing Survey Reports to provide detailed insights.
- Implemented the user interface for Survey Reports, enabling users to view survey results in a clear and organized manner.
- Completed the design and implementation of the Survey Form UI to improve form usability and visual consistency.
- Developed the Kanban board feature for managing startup tasks, allowing visual tracking and organization of work items.
- Enabled mentors to submit survey responses related to mentorship sessions, facilitating feedback collection.
- Integrated Zoom session reporting and recording features to capture and review mentorship or training sessions.
- Added a new field to the Company Profile with corresponding UI/UX enhancements for better data input and display.
- Introduced a centralized timesheet management feature allowing team hourly rates to be set and adjusted from a single page.
- Implemented the Startup Assessment module to evaluate startup progress and performance systematically.
- Enabled functionality for users to view online training materials directly within the platform.
- Added backend implementation to support the new field introduced in the Company Profile for data storage and retrieval.
- Resolved issues related to listing courses, ensuring accurate and complete course information is displayed.
- Added icons next to the settings tabs to improve visual identification and navigation within the settings menu.
- Developed the Startup Portfolio feature, allowing startups to showcase their projects and achievements.
- Enhanced the Edit Company Profile interface with improved UI/UX for a more intuitive editing experience.
- Completed the backend implementation for editing Company Profile data, ensuring changes are saved correctly.
- Improved the UI/UX design of the Startup Portfolio section to enhance presentation and user interaction.
- Added homepage settings options to the general settings page, enabling easier customization of the homepage.
- Introduced a new data field in the Company Profile to capture additional company information.
- Added an edit link in users’ own profiles, allowing them to quickly access and update their personal information.
- Implemented the backend functionality for viewing Company Profiles, ensuring data is correctly retrieved and displayed.
- Designed and improved the UI/UX for viewing Company Profiles to enhance readability and user engagement.
- Removed dashboard cards to streamline the interface and reduce clutter for a cleaner user experience.
- Installed the platform on a local server environment to facilitate development and testing activities.
- Developed dashboard charts for Admin and Organization Admin roles to provide visual data insights and analytics.
- Enhanced the Startup Assessment interface with improved UI/UX for easier navigation and data interpretation.
- Redesigned the View Program interface to improve user experience and accessibility of program details.
- Added additional data fields (Data Set 2) to the Company Profile to capture more comprehensive company information.
- Added initial data fields (Data Set 1) to the Company Profile to expand the scope of company details collected.
- Enabled Organization Managers and Super Admins to assign surveys specifically for the Training Stage within programs.
- Developed the Mentorship Session Survey feature to collect feedback and evaluations from mentorship interactions.
- Implemented functionality to assign surveys for mentorship sessions, facilitating structured feedback collection.
- Removed the City Address field from both User and Company profiles to simplify data entry and profile information.
- Added LinkedIn login capability for Founders and Mentors to streamline authentication and profile linking.
- Integrated the Shark Tank API to enable data exchange and functionality related to the Shark Tank platform.
- Added a Business Category field to the Company Profile to classify companies by their industry sector.
- Introduced a Product or Service field in the Company Profile to specify the main offerings of the company.
- Added a Year of Operations field to the Company Profile to capture the company’s founding or operational start year.
- Updated the Company Profile to include the Number of Employees field for better organizational data tracking.
- Implemented dynamic language and text direction changes based on the user profile settings to support localization.
- Developed the UI components for Admin and Organization Admin dashboard charts to visually represent key metrics.
- Completed backend implementation for the View Program feature to ensure program details are accessible and accurate.
- Added further data fields (Data Set 3) to the Company Profile to enhance company information comprehensiveness.
- Expanded the platform’s geographic data by adding worldwide countries and cities for improved location selection.
- Added additional company profile data fields (Data Set 4) to capture more detailed company attributes.
- Implemented bulk action capabilities for managing companies during the screening process to improve administrative efficiency.
- Developed the Kanban task management feature to support task tracking and workflow organization.
- Enhanced the Kanban task management interface with improved UI/UX for better usability and visual clarity.

### 🐛 Fixes

- Added a new survey question to enhance data collection capabilities.
- Updated the Google login functionality to use API integration instead of Socialite for improved reliability.
- Fixed incorrect values displayed in the program list to ensure accurate data representation.
- Updated the startup dashboard to display profile images with rounded corners for improved UI consistency.
- Restructured the menu layout to enhance navigation and user experience.
- Corrected state field lookups to display in English instead of Arabic for proper localization.
- Resolved issue where categories entered under "Other" did not appear after form submission.
- Fixed country and state fields to reflect updated values correctly in the edit form.
- Adjusted country dropdown positioning to appear correctly below the field and added live search functionality.
- Implemented validation to prevent invalid inputs in Commercial Registration and Tax Number fields.
- Replaced "Unexpected Error" message with proper required field validation in the categories field.
- Added missing validation for the "Other" category field during startup registration.
- Restricted establishment date field to disallow future dates to ensure data accuracy.
- Enhanced address fields (English and Arabic) to reject invalid inputs and provide immediate validation feedback.
- Updated name fields in Arabic and English to prevent entry of special characters and numbers.
- Fixed icon display issues in the mentorship program list for better visual clarity.
- Documented gaps and inquiries clearly to improve issue tracking and resolution.
- Corrected navigation flow to redirect users to the login page after password reset instead of the profile page.
- Fixed malfunctioning "Remember Me" functionality to maintain user sessions properly.
- Added missing eye icon to toggle password visibility in the forget password fields.
- Enforced password complexity requirements and validation in the forget password fields to prevent weak or invalid inputs.
- Added validation to prevent Arabic letters in the English job title field for mentors.
- Resolved registration errors to ensure smooth user onboarding.
- Fixed issues related to rating and commenting features for improved user interaction.
- Corrected display and calculation of training results for accuracy.
- Enabled uploading and displaying company logos correctly.
- Fixed issue with Org Manager account when adding meetings during final evaluation.
- Resolved widget display issues for startup evaluations.
- Fixed problem preventing mentors from joining mentorship sessions.
- Corrected training stage settings issues within the training phases.
- Addressed various UI issues to improve overall interface consistency.
- Fixed display of customized program data and details for institution managers.
- Resolved icon display issues related to cycles.
- Fixed multiple user profile UI issues to enhance usability.
- Improved error handling when adding new organizations.
- Fixed issues encountered when viewing company details.
- Resolved date picker problems in mentor sessions for companies.
- Fixed issue where arbitration results were not visible to admins or institution managers.
- Corrected inability to control form field titles.
- Fixed issues related to registration using Google accounts.
- Resolved company-related issues caused by missing programs.
- Replaced all instances of the term "Cycle" with "Program" for clarity.
- Fixed problems occurring when selecting a stage from the stages list.
- Enabled viewing of pitch documents and pitch videos in startup profiles.
- Resolved content display issues across various modules.
- Fixed issues during first-time client registration and company activation.
- Activated the "About the Team" section to display correctly.
- Updated permissions list to use checkboxes for easier selection.
- Fixed error handling when no institutions exist in the system.
- Corrected issues related to investor data management.
- Updated sustainability goals section for accuracy and completeness.
- Fixed problems encountered when editing founder information.
- Resolved display issues in the [Startup] MyCompany view.
- Fixed menu hover bugs affecting navigation.
- Corrected date picker functionality in course editing.
- Fixed LinkedIn link update issues in mentor accounts.
- Improved permissions and roles user interface for better management.
- Hid billing and pricing options from the profile menu as per requirements.
- Fixed classic training end date issues to ensure correct scheduling.
- Corrected issues arising when editing institution names.
- Restored visibility of bulk action options for companies.
- Fixed issue where added zip code in account settings was not saved or displayed.
- Fixed issue where added address in account settings was not saved or displayed.
- Resolved login errors encountered by organization administrators.
- Fixed issues affecting new user accounts.
- Corrected problems during new account creation.
- Resolved issues in managing user requests.
- Improved filtering functionality in the companies list.
- Fixed display and access issues in the business plan view.
- Resolved issues in the "about the issue" view.
- Fixed attachment problems related to business model documents.
- Changed startup profile "about the problem" field from text area to text box for better input control.
- Fixed menu issues in company profile editing.
- Resolved issues affecting the display of all companies.
- Fixed problems related to tags and labels management.
- Updated the meeting addition interface for improved usability.
- Enabled display of edits immediately after adding meeting dates.
- Fixed assessment weight and category list issues.
- Added missing country codes to the phone number dropdown list.
- Resolved company profile editing issues for new users.
- Fixed final evaluation display issues in the program list.
- Edited interface language and layout inconsistencies on the profile page for founder accounts in both English and Arabic.
- Resolved issues affecting "My Company" functionality.
- Fixed missing development stage option in the edit profile section.
- Resolved issues opening the program list.
- Fixed security issue where the first password reset link remained valid after issuing a new one.
- Added validation to prevent updating email to invalid formats.
- Enforced email confirmation and login requirements when updating email addresses.
- Fixed issues related to company display by STG.
- Enhanced phone number field to reject invalid inputs and enforce Egyptian format rules.
- Corrected invalid behavior when submitting forms with empty industry expertise fields.
- Removed duplicated industry expertise options in mentor dropdown lists.
- Enforced validation criteria on password fields to reject invalid inputs.
- Added immediate error messages for empty name fields instead of showing errors only after submission.
- Prevented mixing of languages between first name and last name fields.
- Added validation to disallow special characters and numbers in first and last name fields.
- Enforced proper email format validation to prevent invalid email submissions.
- Added validation to LinkedIn field to reject invalid URLs and non-URL inputs.
- Added required field validation for the job title field for mentors.
- Enforced validation on the job title field to prevent invalid inputs such as spaces, numbers, and special characters for mentors.

### 🚀 Enhancements

- Added functionality to track and visualize progress metrics over a specified time period.
- Improved display and accuracy of assessment results for clearer user insights.
- Enhanced the assessment interface to provide a more intuitive and user-friendly experience.
- Enabled administrators to manage and configure assessment settings with greater control and flexibility.
- Fixed language selection issue in the profile editing feature to ensure proper localization.
- Redesigned the "Edit Profile" button on the user profile page for improved visibility and usability.
- Implemented a feature to monitor and display program status trends over time.
- Developed a report categorizing companies based on their business models for better segmentation.
- Added a detailed sector breakdown to analyze company distribution across different industries.
- Introduced target market analysis tools to help identify and evaluate market segments.
- Created a development stage chart to visualize company progress through various growth phases.
- Added a summary of total funds raised by companies for financial overview purposes.
- Provided a breakdown of companies per sector to facilitate industry-specific insights.
- Enabled administrators to view the total number of startups associated with each organization.
- Added reporting on total companies aligned with Sustainable Development Goals (SDGs).
- Included a count of total company founders to track entrepreneurial involvement.
- Added metrics showing total jobs created by companies, reflecting overall team size.
- Developed a country distribution chart to visualize geographic company locations.
- Implemented user experience (UX) improvements to enhance overall usability.
- Applied user interface (UI) enhancements for a more modern and consistent design.
- Enabled users to view the task board for better task management and tracking.
- Added functionality to create new tasks directly within the application.
- Provided the ability to view detailed information for individual tasks.
- (Duplicate of 7) Implemented a feature to monitor and display program status trends over time.
- (Duplicate of 8) Developed a report categorizing companies based on their business models for better segmentation.
- (Duplicate of 9) Added a detailed sector breakdown to analyze company distribution across different industries.
- (Duplicate of 10) Introduced target market analysis tools to help identify and evaluate market segments.
- (Duplicate of 11) Created a development stage chart to visualize company progress through various growth phases.
- (Duplicate of 12) Added a summary of total funds raised by companies for financial overview purposes.
- (Duplicate of 13) Provided a breakdown of companies per sector to facilitate industry-specific insights.
- (Duplicate of 14) Enabled administrators to view the total number of startups associated with each organization.
- (Duplicate of 15) Added reporting on total companies aligned with Sustainable Development Goals (SDGs).
- (Duplicate of 16) Included a count of total company founders to track entrepreneurial involvement.
- (Duplicate of 17) Added metrics showing total jobs created by companies, reflecting overall team size.
- (Duplicate of 18) Developed a country distribution chart to visualize geographic company locations.

### 🛠️ Tasks

- Added a general setting to enable or disable the bypass feature across the application.
- Implemented bypass of email verification specifically in the development environment to streamline testing processes.

---

## v3.1.0

**Summary**: This release includes 33 new features, 72 bug fixes, 0 enhancements, and 0 completed tasks.

### ✨ Features

- Resolved issues with social media links on the Startup Profile to ensure proper display and functionality.
- Updated the Mentor Availability feature by improving the time picker for easier and more accurate time selection.
- Integrated a chat widget accessible to registered users to facilitate real-time communication within the platform.
- Redesigned the Login and Registration pages with a new theme to enhance visual appeal and user experience.
- Modified the welcome message on the Login Page to dynamically display "Welcome to [Organization Name]" based on settings configuration.
- Changed the database type used for storing company information to improve data management and performance.
- Implemented user experience (UX) improvements for the Add/Edit functionality within Program Management.
- Enhanced the LinkedIn icon on Mentor Profiles for better visibility and user interaction.
- Added WhatsApp integration to the Settings Page, allowing users to configure WhatsApp-related options.
- Added a field to the Startup Profile to include a TikTok URL for expanded social media presence.
- Added a field to the Startup Profile to include an Instagram URL to showcase social media activity.
- Merged the Startup Profile’s logo and social media links into the Contact Info section for a streamlined layout.
- Introduced the ability to enable or disable the Mentor List feature as needed by administrators.
- Renamed the context label from "Company" to "Startup" throughout the application for consistency.
- Fixed an unhandled exception occurring when users without an associated company attempt to view their startup or company profile.
- Addressed issues preventing new users from being correctly linked to a company upon registration.
- Updated the Survey Widget on the Mentor dashboard to improve usability and functionality.
- Enabled viewing of current images within the Company Profile for better content management.
- Added a WhatsApp number field to the User Profile, including a verification process to ensure accuracy.
- Fixed the issue preventing startups from attaching files to tasks within the platform.
- Added a Submit Survey button to Mentorship tasks to facilitate survey completion and submission.
- Implemented an Availability feature to support scheduling meetings based on user availability.
- (Duplicate) Implemented an Availability feature to support scheduling meetings based on user availability.
- Updated all email templates to include the footer and header logos as configured in the Settings.
- Developed backend functionality to support the Organization list feature.
- Implemented user interface (UI) enhancements for the Add/Edit functionality within Program Management.
- Developed the UI for Zoom session reports and recorded session access.
- Improved the UX for Zoom session reports and recorded session features to enhance user interaction.
- Added an Email Header configuration option under Settings > Application Identity for branding consistency.
- Removed all required fields from the Company Profile except for the "Company Info" section to simplify data entry.
- Enhanced the user experience of the Survey Report for better readability and interaction.
- Added a new question type option in surveys allowing users to provide answers via text boxes.
- Redesigned the Startup Assessment interface to improve usability and visual presentation.

### 🐛 Fixes

- Fixed incorrect handling of file types in the Documents section of the Pitch Deck to ensure only supported formats are accepted.
- Improved validation for the Pitch Deck video link field to properly handle invalid input and prevent errors.
- Resolved issues with file type validation in the Pitch Deck section to restrict uploads to supported formats.
- Corrected display issue in the Admin Portal where role names were missing and only email addresses were shown.
- Fixed system error caused by incorrect role selection in the Admin Portal to ensure stable role assignment.
- Updated and optimized the Gen-Z dashboard view for startups to enhance user experience.
- Removed the word "Admin" from error messages to improve clarity and professionalism.
- Fixed the non-responsive "Save" button when attempting to save a question, ensuring proper functionality.
- Addressed various bugs and improved usability on the task management page.
- Corrected validation message displayed after adding a judge in the Final Screening stage to reflect accurate status.
- Added the missing "Show Password" icon in the Admin Portal to improve password visibility options.
- Added a clear and descriptive label to the "Add Comment" button during the Initial Screening stage for judges.
- Resolved issues with verification emails not being sent or received for judge accounts.
- Fixed bugs and enhanced functionality within the "My Company" section.
- Adjusted the size display of working team details for better readability and layout consistency.
- Prevented new judges from being automatically added to program lists in the Admin Portal unless explicitly assigned.
- Corrected font and style inconsistencies in empty state messages across the application.
- Fixed display issues where Pitch Deck videos did not appear properly on startup profiles after successful upload.
- Resolved error message "No Video Found" that appeared when uploading videos larger than 2MB in the Pitch Deck video field.
- Restricted Pitch Deck video uploads to supported formats and fixed broken video display issues on upload.
- Addressed documentation inaccuracies and updated relevant materials accordingly.
- Fixed incorrect URL path issues related to the Pitch Deck section.
- Resolved data display and update issues within the team information section.
- Corrected role display inconsistencies in judge profiles to accurately reflect assigned roles.
- Fixed incorrect program assignment display for judges during the Final Screening stage.
- Addressed profile setup issues for new startup users in the Company Profile section.
- Resolved editing issues within program pages to allow smooth updates.
- Fixed error caused by passing an array instead of a string to the htmlspecialchars() function.
- Validated organization ID selection to prevent invalid IDs from being processed.
- Removed all required field constraints from the financial page to allow optional data entry.
- Implemented proper exception handling for Pusher service errors during user registration.
- Added exception handling for missing data in meeting configuration to prevent system crashes.
- Fixed calendar display and interaction issues in Startup and Organization Admin views.
- Resolved bugs related to editing survey questions to ensure changes are saved correctly.
- Fixed issues when adding new survey questions to maintain data integrity.
- Standardized file size validation rules within the Pitch Deck section to ensure consistent enforcement.
- Corrected malfunctioning cancel button behavior in the Team Info section to properly discard changes.
- Enhanced UI for icons in the Stakeholders, Investors, and Founders sections within the Team Info update modal.
- Improved feedback and redirection behavior following deletion actions in the Team Info section.
- Fixed issue preventing social links from appearing on the main user profile page.
- Removed the required validation for the WhatsApp field in the user profile form.
- Improved email update validation logic in the Edit Profile section to prevent invalid entries.
- Corrected handling of WhatsApp numbers starting with "0" to ensure proper formatting and validation.
- Updated startup dashboard to retrieve program image links from the database instead of the upload plugin.
- Fixed startup logo display issues within the Startup Profile section.
- Enabled updating of WhatsApp numbers in the company profile with proper validation.
- Addressed bugs and improved functionality in the Company Profile update process.
- Fixed issues encountered during mentor profile updates to ensure changes are saved correctly.
- Resolved editing issues in training modules to allow seamless content updates.
- Fixed white blank page error occurring when updating session reports.
- Ensured mentors and startups are redirected to the same page after completing surveys.
- Corrected issue where task completion dates were not being updated properly.
- Replaced the Arabic term "مقر الشركة" with the appropriate alternative in user profile data.
- Added functionality to allow users to add events on the calendar page.
- Implemented validation to prevent invalid inputs in the Founder Name field within Team Info.
- Added validation to detect and reject inputs containing only spaces in mandatory fields.
- Prevented duplicate entries when adding founders in the Team Info section.
- Added validation to detect invalid edits to names on the profile page.
- Fixed acceptance and storage of invalid inputs in social links within the Logo & Social section.
- Corrected incorrect display of team member counts in the Company Profile.
- Improved phone number validation and update functionality in the Contact Info section.
- Fixed caching issues causing validation messages to persist after confirming account deactivation.
- Resolved issues with supported attachment formats to ensure proper file uploads.
- Corrected misplacement of validation error messages in financial fields.
- Fixed invalid navigation behavior encountered when attempting to upload documents.
- Enhanced input validation for the "About the Company" field in the Description section.
- Resolved caching issues affecting mobile number validation messages.
- Improved email field validation to reject invalid email formats before submission.
- Corrected language inconsistencies in registration validation messages to match expected standards.
- Fixed validation errors for contact fields that redirected users incorrectly to the Company Info page with errors displayed at the top.
- Resolved navigation failures and incorrect validation errors in the WhatsApp field, ensuring proper account linking.
- Addressed form step issues within the Program List to ensure smooth user progression.

---

## v3.2.0

**Summary**: This release includes 100 new features, 144 bug fixes, 11 enhancements, and 3 completed tasks.

### ✨ Features

- Enabled assignment of a service to a single organization or multiple organizations for improved service management.
- Redesigned and optimized the dashboard codebase to enhance performance and user experience.
- Added date and time selection functionality for scheduling traditional/offline training sessions.
- Implemented attendance reporting features for traditional/offline training to track participant presence.
- Grouped two-factor authentication (2FA) settings within the permissions module for streamlined access control.
- Introduced a button to generate QR codes for traditional/offline training sessions to facilitate attendance tracking.
- Integrated map location display for traditional/offline training venues to assist users with navigation.
- Resolved issue where service requests created by admins did not display vendor information.
- Updated the service list to include vendor details for better transparency and management.
- Identified and documented attributes requiring further clarification following stakeholder review sessions.
- Automated email notifications to vendors upon creation of new service requests to improve communication.
- Enhanced the "Add New Service" feature by including vendor information fields and visibility control options.
- Removed the "Delete my account" option from the user profile to prevent accidental account deletions.
- Implemented Google Authenticator-based two-factor authentication for enhanced user security.
- Updated the ChatWoot integration to the latest version for improved chat support functionality.
- Added support for project management features within the platform.
- Fixed relationship issues between experience and entrepreneurial experience data fields.
- Developed offline session invitation and attendance model for traditional training, including QR code-based attendance verification.
- Created a table view to manage organization team members efficiently.
- Added a password setup page for inviting organization admin team members securely.
- Enhanced organization team management capabilities for better user administration.
- Redesigned the roles and permissions page to improve usability and clarity.
- Fixed issues affecting the organization dashboard display and functionality.
- Displayed the number of mentors associated with each program in the program view.
- Added a pending evaluation indicator within the program view to track outstanding assessments.
- Updated date picker controls in the add/edit steps interface to support duration selection.
- Improved organization settings interface for easier configuration management.
- Implemented a new UI element for WhatsApp number verification window to enhance user experience.
- Enabled WhatsApp notifications for mentorship task creation, updates, and comments sent to mentors and startups.
- Added email notifications for mentorship task creation, updates, and comments sent to mentors and startups.
- Added enable/disable toggle for the assessment tool within settings for flexible usage control.
- Removed mandatory requirement for the "Description" field in the startup profile tab to simplify profile completion.
- Replaced target market selection with flag icons for more intuitive visual identification.
- Updated the company profile document upload functionality to improve reliability and user experience.
- Enhanced the survey widget on the startup dashboard for better data collection and display.
- Developed a registration form specifically for mentors to streamline onboarding.
- Added SWOT analysis tools to support strategic planning and evaluation.
- Added WhatsApp number field with verification UI to user profiles for enhanced contact options.
- Implemented notification delivery via WhatsApp to improve communication effectiveness.
- Changed organization selection in questions from a dropdown menu to a multi-select interface for greater flexibility.
- Created test cases covering startup registration workflows to ensure quality and reliability.
- Developed test cases for mentor registration processes to validate functionality.
- Enhanced the roles and permission module for improved access control management.
- Implemented backend functionality for adding and editing program management data.
- Added a documentation link to the main menu accessible by startups and mentors for easy reference.
- Enabled sending messages directly to organization administrators within the platform.
- Added functionality to send messages to companies participating in programs.
- Included permission list URLs on the role management page for quick access to permission details.
- Added fields in general settings to input Terms and Conditions and Privacy Policy URLs.
- Integrated Terms and Conditions and Privacy Policy links on the signup page to ensure compliance.
- Fixed spacing issues within the assessment tool interface for better readability.
- Resolved language localization issues in the assessment tool to support multiple languages.
- Addressed display issues in the assessment view to improve user experience.
- Restricted assessment report exports to only include completed assessments for data accuracy.
- Granted mentors access to view the Startup Hub for enhanced collaboration.
- Combined email and WhatsApp notifications for mentorship task creation, updates, and comments to mentors and startups.
- Displayed program date durations on the project page list for better timeline visibility.
- Added clickable program links on the project page to facilitate navigation.
- Updated the assessment list interface with improved sorting and filtering options.
- Implemented user interface enhancements for project management pages.
- Developed a CRUD (Create, Read, Update, Delete) interface for managing currencies within the system.
- Updated company profile pitch deck document handling to support new formats and improve upload reliability.
- Enabled viewing of calendar slot information to assist with scheduling and availability.
- Implemented a new interface and routing structure within a specific branch for improved navigation.
- Redesigned the organization list UI for better usability and visual appeal.
- Added data duration filters to the timesheet module for refined data analysis.
- Introduced mentor filtering by area of expertise to help users find relevant mentors quickly.
- Enabled editing of email template content for program registration, new user onboarding, mentorship, training, and other notifications.
- Redesigned the form submission list page UI for improved usability.
- Updated the single form submission view page UI for clearer data presentation.
- Enhanced the form list page UI to facilitate easier form management.
- Optimized mentor page queries to improve data retrieval performance.
- Configured automatic email and internal message notifications to system admins when organization admins create new programs.
- Implemented the program page interface from the mentor’s perspective.
- Redesigned the permission management list UI for better clarity and navigation.
- Added functionality to create and manage meetings based on organization-specific settings.
- Prevented deletion of training and mentorship sessions after their scheduled date and time to maintain data integrity.
- Replaced the existing content editor in the service module with an improved editor for better usability.
- Developed a license system view to display license information clearly.
- Implemented user interface components for managing evaluation models.
- Added settings to enable automatic approval of mentorship requests within programs.
- Introduced core permissions to enhance role-based access control.
- Regrouped roles and permissions lists for better organization and easier management.
- Added mark as read/unread functionality to message lists and individual message options.
- Enabled reply functionality within the messaging system.
- Added draft, trash, and starred status options to messages for improved message management.
- Updated message viewing layout to a standard theme with a new message interface.
- Enhanced audit logs with additional details and improved tracking capabilities.
- Added controls for startup assessments to regulate access and visibility.
- Added a button to update currency exchange rates manually.
- Introduced a button to auto-import currency data for streamlined updates.
- Completed UI implementation of the program page from the mentor’s perspective.
- Released version 2 of the assessment tool’s PDF report template with export functionality.
- Implemented user interface for the screening step process within programs.
- Applied the license system across relevant modules for compliance enforcement.
- Added pending status filters to improve data filtering capabilities.
- Developed the JUDGER test cycle feature for evaluation workflows.
- Added management capabilities for program landing pages.
- Converted company profile editing into a wizard format with save options until finalization.
- Added filtering options to training results for easier data analysis.

### 🐛 Fixes

- Resolved an issue causing errors when updating program duration.
- Fixed the Company Profile view accessibility for Admin and Organization Admin users.
- Corrected functionality to allow editing of program date ranges.
- Addressed errors occurring when adding mentorship sessions related to time settings.
- Updated project filter to display project names instead of IDs for better clarity.
- Fixed a general error occurring in the Shark Tank module.
- Improved the clarity of error messages shown during mentor request submissions.
- Resolved the issue preventing editing of shareholder information.
- Implemented validation to prevent adding shareholders exceeding 100% ownership.
- Restored missing icons in the Service List for improved visual representation.
- Fixed issues affecting the proper display of documentation views.
- Corrected calendar display and functionality issues.
- Added and configured new roles and permissions within the system.
- Corrected inaccurate English phrasing in the welcoming email for users with the Judge role.
- Fixed activation link error causing "419 Page Expired" message for Judge role accounts.
- General error message improvements for better user understanding.
- Enabled mentorship notifications to be sent via WhatsApp.
- Updated login logo to reflect changes made in General Platform Settings.
- Implemented various user interface improvements for enhanced usability.
- Improved appearance and layout of the Gen-Z survey.
- Added meeting links to mentor profiles for easier access.
- Fixed graph display issues for organization managers.
- Resolved errors encountered by organization managers during usage.
- Corrected "Sector Breakdown" graph issues on the dashboard.
- Fixed visual alignment and scaling issues in dashboard graphs (X-Y axes).
- Enabled editing of judge meeting dates without errors.
- Configured service currency settings to be sourced from global settings.
- Updated settings currency selection to pull data from the currency table.
- Added missing Final Screening table and Zoom meeting link in startup account profiles.
- Corrected date format inconsistencies and ensured the year is displayed system-wide.
- Fixed issue where judges did not receive notification emails upon assignment to the Final Screening stage.
- Added validation to prevent start date values from being set after end date values in the Admin portal.
- Enabled booking of mentorship sessions from Admin, Organization Admin, and Startup user roles.
- Resolved issues causing export functionality to malfunction on the Admin platform.
- Fixed incorrect redirection occurring after email verification for Judge users.
- Addressed issues related to the "My Company #2" feature.
- Corrected typographical errors in validation messages for program joining.
- Restricted Pitch Deck field to accept only valid file types and improved error messaging for oversized files.
- Fixed UI alignment issue where the "Update" button was misaligned when uploading videos in the Pitch Deck Video section.
- Enhanced front-end validation for editing company information, specifically for logo and social media fields.
- Resolved issue preventing offline session surveys from appearing on mentor dashboards.
- Added user feedback indication after registration via the Admin interface.
- Removed Pitch Deck information from Company Profile as it has been replaced with company description.
- Fixed errors causing failure in retrieving Sector Breakdown data.
- Resolved issues encountered during course creation.
- Corrected graph display problems in the assessment view.
- Clarified password error messages to specify accepted special characters on registration and user profile pages.
- Addressed multiple Mentor role issues identified during UAT on the beta version.
- Implemented validation to reject invalid LinkedIn URLs in team information.
- Fixed phone number display to include country code on the "My Company" page.
- Corrected time difference discrepancies on the Arweqah server.
- Fixed issue where mentorship sessions moved to "All Requests" with "Waiting" status after canceling edits despite prior approval.
- Resolved errors occurring when modifying existing mentorship sessions.
- Fixed artisan migration file issue related to percentage data handling.
- Corrected invalid value in the Role Type list on the edit role page (Pages Editor).
- Fixed sector filtration functionality on the Startup Hub page within founder profiles.
- Resolved errors occurring when admins attempt to edit or delete mentorship sessions for startups.
- Fixed 401 Unauthorized error when assigning mentors to programs.
- Corrected display issues in the target market block of startup profiles.
- Fixed 401 Unauthorized errors when mentors update or add profile information.
- Resolved display issues with the "Select your expertise" dropdown in mentor profiles.
- Fixed unexpected profile behavior and navigation issues caused by admin-created mentor accounts.
- Enabled Organization Admins to view lists of mentors and startups when sending new messages.
- Fixed dropdown menu display and functionality issues.
- Corrected assessment graph rendering problems.
- Updated placeholder text and validation for the website field in company social media profiles to accept URLs with or without "www" and correct format guidance.
- Enabled sending new messages to portal managers.
- Fixed language display issues in startup profile categories.
- Corrected relationship mapping between experience and entrepreneurial experience fields.
- Fixed issue where job titles were not saved or retrieved correctly in user profiles.
- Updated exchange rate input to accept float values less than 1.
- (No description provided; likely a placeholder or environment reference.)
- Added missing Terms & Conditions page to the platform.
- Implemented validation to require acceptance of Terms & Conditions during registration.
- Fixed issues with static insights display in programs.
- Corrected functionality related to modifying program cycles.
- Resolved errors when admins assign judges for initial screening.
- Made LinkedIn field non-mandatory in company team founder information.
- Enhanced company description field to accept special characters.
- Fixed language inconsistencies on the survey page.
- Resolved display issues in survey views.
- Improved user role update permissions handling.
- Prevented startup forms from remaining editable after initial screening completion to avoid stage reversion.
- Fixed calendar display issue showing empty calendar for newly registered Judge users.
- Implemented validation to prevent invalid inputs in the investor name field.
- Added validation to ownership percentage fields in stakeholders to reject invalid inputs.
- Fixed acceptance of invalid data in the Add New Stakeholder form.
- Updated social media validation to replace Twitter with X.com in company profiles.
- Fixed backend error caused by leaving the "Sustainable Development" field empty.
- Resolved issue where emails were not sent to trainers when adding new online sessions on Arweqah.
- Fixed issue preventing opening of sent emails. (Arabic)
- Resolved issue occurring when sending messages and selecting managers. (Arabic)
- Fixed system error where training hours were not counted for uploaded content.
- Corrected role validation errors in the Final Screening section when assigning judges.
- Fixed errors encountered by admins managing mentors and creating mentorships based on timeslots.
- Resolved 500 Internal Server Error occurring during mentor modification.
- Addressed issues within portal settings configuration.
- Fixed errors related to editing program details.
- Updated Zoom meeting session reports to reflect accurate data.
- Resolved issue preventing mentors from registering via LinkedIn.
- Fixed errors in calculating training completion percentages. (Arabic)
- Corrected functionality to allow creating questions for multiple organizations.
- Fixed issues viewing step-assigned forms.
- Disabled mentorship widget and request functionality for users in the screening step as it is not permitted.
- Updated mentorship request form on mentor profiles to reflect current data.
- Fixed period validation errors in mentorship requests.
- Resolved errors when creating new mentor accounts using Gmail login.
- Corrected program view display issues.
- Fixed job title display issue after language selection in Arabic.
- Enabled adding new currencies without errors.
- Fixed issue where service-related organizations were not appearing.
- Removed mandatory vendor requirement when adding or editing services.
- Fixed inability to attach questions to quizzes.
- Resolved 500 Internal Server Error occurring during online training sessions.
- Fixed training completion percentage display issues. (Arabic)
- Addressed incomplete data issues in training content. (Arabic)
- Added missing time field when creating online training sessions.
- Fixed issues with states list in Admin General Settings.
- Resolved issues with startup booking of mentor sessions.
- Fixed functionality to properly reset filters.
- Corrected organization filter to work as intended.
- Fixed image detail view issues in the assessment tool.
- Added exception handling for missing pitch deck view in startup profiles.
- Corrected missing information in message body and sender details.
- Fixed issues related to adding currencies.
- Resolved listing issues in Startup Hub.
- Clarified and reorganized permission groupings for better understanding.
- Added functionality to allow booking multiple mentor sessions.
- Corrected incorrect rounding of decimal percentages when editing shareholder ownership.
- Enhanced percentage validation for shareholder ownership to reject invalid ranges and support decimals.
- Fixed random failures occurring when adding shareholders with valid ownership percentages.
- Corrected display of multiple targeted market country values in company profiles.
- Fixed caching issues causing validation messages to persist after resetting form values.
- Removed invalid role values from the user roles list.
- Fixed language display issues in the Languages and Country section of user profiles.
- Prevented duplicate registrations using the same phone number.
- Resolved Arabic language display issues on the Currencies page.
- Added restrictions and validations for attachment uploads, specifying accepted formats and sizes.
- Fixed visibility issues with the Final Screening table for startups despite judge assignment.
- Corrected display of currencies on user profiles to exclude those not enabled in admin settings.
- Fixed issue with scheduling final evaluation sessions with companies. (Arabic)
- Improved validation messages for invalid login data when Arabic language is selected.
- Resolved issues editing and updating current mentor time slots.
- Corrected language display for the "My current schedule" option in the mentor interface.

### 🚀 Enhancements

- Added the ability to view all team members assigned to a specific project for improved team management.
- Enabled the removal of individual team members from a project to maintain accurate team composition.
- Introduced functionality to add new team members directly to a project, streamlining team assignment.
- Implemented a feature to display all programs linked to a project on its main page for better project overview.
- Provided the option to unlink a program from a project, allowing for flexible project-program associations.
- Added the capability to link a program to a project directly from the project page to simplify workflow.
- Enabled the deletion of projects, allowing users to remove obsolete or completed projects from the system.
- Improved project editing features to allow users to update project details efficiently.
- Enhanced the projects listing page to provide a comprehensive view of all projects with improved navigation.
- Added functionality to create new projects, facilitating project initiation within the system.
- Updated the user experience design to improve usability and interface consistency across the application.

### 🛠️ Tasks

- Added the capability to export the Startup Assessment Analysis report for easier sharing and offline review.
- Implemented filtering functionality for the Program List to allow users to quickly locate specific programs based on selected criteria.
- Improved the Schedules Meeting table by enhancing its layout and usability for better readability and user interaction.

---

## v3.3.0

**Summary**: This release includes 26 new features, 163 bug fixes, 3 enhancements, and 11 completed tasks.

### ✨ Features

- Enabled selection of a mentor for a startup through a multi-page mentor selection interface, improving user navigation and choice.
- Added a Zoom Meeting Report feature within mentorship sessions to provide detailed meeting analytics and records.
- Integrated email functionality into the Startup Request Service queue to automate notification and communication processes.
- Introduced the ability for startups to rate their mentorship sessions, facilitating feedback collection and quality improvement.
- Developed the Importer Tool’s result display to clearly show the outcome of data import operations.
- Implemented duplicate checking within the Importer Tool to prevent redundant data entries during import.
- Added comprehensive field validation in the Importer Tool to ensure data accuracy and integrity before import.
- Enhanced the survey module with improved features for better data collection and user experience.
- Redesigned the Importer Tool’s user interface to provide a more intuitive and efficient review process.
- Enabled judges to access startup profiles directly during the screening process to facilitate informed evaluations.
- Listed all traditional training information associated with startups to provide a comprehensive training overview.
- Created a dedicated survey report page to display survey results and analytics in a clear format.
- Implemented a single submission view page within the Forms module to allow detailed review of individual form submissions.
- Developed the form list interface to display all available forms for easier access and management.
- Built a form submission list page to present all form submissions in a structured and accessible manner.
- Integrated WhatsApp messaging capabilities via WAPI to enable direct communication through the platform.
- Developed a single program report to consolidate and present data related to individual programs.
- Added role-based permission seeding to streamline initial permission assignments during role creation.
- Configured meeting creation to utilize organization manager settings, ensuring consistent meeting management.
- Updated meeting creation process to apply organization-wide settings for standardized configurations.
- Improved the user experience of the single program report with enhanced layout and usability.
- Implemented backend logic for the Judger screening step process to support application evaluation workflows.
- Designed and enhanced the user experience for the screening step process to facilitate smoother interactions.
- Developed the Importer Tool as a comprehensive solution for bulk data import with validation and review features.
- Defined and implemented screening criteria to standardize the evaluation of startup applications.
- Introduced judge-based scoring functionality to enable individual judges to score startup applications independently.

### 🐛 Fixes

- Fixed issues with admin form submission related to the "Submit by" field and file attachment functionality.
- Resolved management and results display issues within the organization manager’s screening module.
- Corrected email sending functionality when organization managers or admins create training sessions for startups.
- Improved request filtration functionality for organization managers to ensure accurate filtering of requests.
- Fixed incorrect card count numbers displayed to startup users.
- Addressed issues with mentorship request processing for startups.
- Resolved image upload and display problems when organization managers or admins create or edit programs.
- Enabled admin screening manager to edit and assign a single evaluator to all users simultaneously.
- Fixed display and access issues in form submission views for admins and organization managers.
- Added a button in admin screening results to display all evaluation details comprehensively.
- Adjusted the design and layout of the screening results page for improved readability and usability.
- Fixed functionality allowing admins to disapprove registration forms properly.
- Resolved various issues affecting screening tasks performed by judger users.
- Ensured startup users receive correct notifications when admins disapprove their registration forms.
- Improved styling and interface consistency when adding or editing quizzes.
- Fixed attachment upload issues in the startup user registration form.
- Addressed general issues with the startup registration form submission process.
- Prevented startup users from editing profiles using an email address that already exists in the system.
- Corrected the status display of mentorship meeting links in startup user mentorship requests.
- Fixed creation process for both online and traditional sessions by organization managers.
- Resolved login issues experienced by startup users.
- Handled cases where requested mentorship sessions have expired for startup users.
- Fixed screening-related issues encountered by judger users.
- Enabled admins and organization managers to properly disapprove registration forms.
- Improved management of evaluation models within the screening step for organization managers.
- Fixed Arabic localization issues in program reports for organization managers.
- Resolved viewing issues in program reports for organization managers.
- Enhanced program filtration functionality for organization managers.
- Corrected currency display and selection when creating programs for organization managers.
- Updated terminology in program report views, changing "Responsible" to "Mentors" for clarity.
- Increased attachment size limits when creating programs for organization managers.
- Enabled new organization managers to add new forms successfully.
- Fixed various program management issues affecting admins and organization managers.
- Resolved display and functionality issues on the startup user dashboard.
- Addressed quiz management issues for organization managers.
- Fixed the inability of organization managers to add questions to surveys.
- Implemented and fixed login functionality via LinkedIn.
- Corrected floating button behavior in judger user screening interface.
- Resolved file manager issues encountered by judger users.
- Fixed management of evaluation model steps within admin screening.
- Removed unnecessary search label from judger user screening interface.
- Improved screening filtration functionality for judger users.
- Resolved issues in displaying screening results for admins.
- Fixed localization of portal screening list in admin screening steps.
- Corrected report generation for mentorship sessions in the admin panel.
- Fixed management and display of online and traditional sessions for admins.
- Resolved issues adding online training sessions in the admin interface.
- Addressed mentorship session request issues experienced by startups.
- Fixed general form-related issues for startups.
- Ensured mentor calendars update correctly when startups request mentorship sessions.
- Fixed issues preventing mentors from starting mentorship meetings.
- Adjusted design of delete confirmation pop-ups across all features for consistency.
- Resolved startup mentorship session request issues.
- Enabled organization managers to delete questions from the question bank successfully.
- Fixed result display issues in screening steps for admins.
- Enabled judger users to activate their accounts properly.
- Resolved issues with organization users adding questions.
- Fixed display of assessment information for organizer managers.
- Corrected image upload and editing issues in admin service management.
- Fixed startup user issues when requesting mentorship sessions.
- Resolved editing issues in admin service management.
- Fixed startup user issues when requesting services.
- Corrected mentor session display on startup user dashboards.
- Added filtering capability by status in admin quiz management.
- Fixed calendar display and functionality issues for judger users.
- Resolved screening-related issues for judger users.
- Fixed admin functionality for creating judger users.
- Improved form submission management for admins and organization managers.
- Fixed private link functionality in program management for organization managers and admins.
- Resolved program step management issues for organization manager users.
- Fixed program creation issues for organizer users.
- Corrected display of upcoming mentorship sessions for mentor users.
- Resolved issues with custom forms for startup users.
- Fixed date range selection when adding new training sessions in admin panel.
- Addressed login issues for startup users.
- Fixed training session management issues in the admin interface.
- Resolved issues when creating training sessions in admin panel.
- Corrected content page display issues in training courses managed by admins.
- Fixed localization issues on the startup dashboard.
- Ensured online training session links are included in notification emails.
- Removed action buttons for sessions whose dates have passed to prevent invalid actions.
- Fixed localization issues on the startup user dashboard.
- Resolved issues with the Contact Us feature for startup users.
- Corrected profile display issues in the My Startup/Idea section for startup users.
- Improved search functionality in the Startup Hub user management for admins.
- Fixed incorrect request count display on startup user dashboards.
- Corrected services count display on startup user dashboards.
- Resolved issues in Startup Hub user management for admins.
- Fixed mentor count display on mentor user dashboards.
- Enabled export of training results in the admin panel.
- Fixed modification issues in mentorship session management for admins.
- Improved sorting functionality across all admin features.
- Fixed timer editing issues for online training sessions in admin panel.
- Enabled mentor users to sync their schedules with Google Calendar.
- Corrected display of upcoming mentorship sessions on mentor dashboards.
- Resolved issues in mentorship management for admins.
- Fixed display of current program status in single program reports.
- Corrected training results display issues in admin panel.
- Fixed push notification errors when no data is available.
- Enabled deletion of training sessions by admins without errors.
- Resolved errors when editing programs with no description in admin panel.
- Fixed display and access issues for startup users viewing course contents.
- Improved filtration functionality in admin quiz management.
- Fixed Arabic localization issues in screening types for admins.
- Resolved issues managing screening steps in admin panel.
- Added progress bar functionality to quizzes for startup users.
- Fixed missing screening tables for judger users.
- Addressed training setting step issues in admin panel.
- Fixed access and display issues for startup users viewing their courses.
- Enabled PDF download functionality for programs in admin panel.
- Resolved issues editing training sessions in admin panel.
- Fixed editing problems in training sessions for admins.
- Enabled mentor users to add availability schedules successfully.
- Fixed startup display issues within programs managed by admins.
- Translated missing fields to Arabic in admin project management.
- Localized delete confirmation pop-ups for linked programs and team members in admin projects.
- Fixed localization issues in organization management for admins.
- Enabled deletion of organizations by admins without errors.
- Resolved issues with mentorship task management.
- Fixed localization issues in form submission details for admins.
- Corrected update issues in the evaluation tab display.
- Enabled support for multiple sub-criteria in judger screening.
- Fixed private link issues in program lists for admins.
- Prevented new startup users from joining programs without an associated company.
- Improved filtering functionality in form submissions for organizer users.
- Enhanced form submission filtering for admins.
- Fixed user interface issues in the quiz interface.
- Ensured imported users have the "have_company" value updated correctly.
- Linked imported companies correctly with their respective organization IDs.
- Fixed issues related to steps and startup management in admin panel.
- Corrected sorting order in services display to show newest items first.
- Resolved various issues in admin form submissions.
- Improved program filtration functionality in admin panel.
- Fixed training session management issues in admin panel.
- Corrected data inconsistencies in single program report (issue 2).
- Fixed data inaccuracies in single program report.
- Resolved filtering functionality issues in single program reports.
- Improved performance to reduce request time when adding sessions in admin panel.
- Fixed PDF generation issues in program reports for admins.
- Addressed behavioral issues in survey functionality.
- Resolved quiz-related issues for startup users.
- Fixed issues when creating steps within programs in admin panel.
- Corrected quiz access and display issues for startup users.
- Fixed editing issues in quizzes for admins.
- Improved filtering functionality in admin quiz management.
- Resolved search issues in program management for admins.
- Fixed program editing issues in admin panel.
- Corrected issue where organization list did not appear when adding or editing programs.
- Fixed fatal error occurring during program detail updates.
- Improved sorting of startup session requests.
- Resolved issues with startup users requesting services.
- Updated mentorship request view to use the current interface on mentor pages.
- Fixed permission issues preventing creation of mentorship tasks.
- Resolved issues preventing startup users from requesting services.
- Fixed broken status display for mentor sessions.
- Addressed missing information in mentor post-session tasks.
- Ensured training emails to trainers include meeting links during test execution.
- Improved exception handling in mentor filtering functionality.
- Fixed scheduling conflicts where mentors or startups were assigned overlapping sessions.
- Corrected judger assignment display on judger profiles after admin assigns initial screening.
- Clarified and corrected permission group definitions.
- Fixed email update validation and language display in user profile editing.
- Resolved issues with Pusher notification delivery and display.

### 🚀 Enhancements

- Addressed and resolved behavioral issues identified during survey test execution to improve stability and performance.
- Implemented field validation within the Importer Tool during test execution to ensure data accuracy and integrity.
- Enhanced the user interface for improved usability and visual appeal.

### 🛠️ Tasks

- Enhanced the Mentor Control feature to improve functionality and user experience.
- Addressed potential SQL injection vulnerabilities caused by string-based query concatenation to enhance database security.
- Remediated additional instances of SQL injection risks due to unsafe string concatenation in database queries.
- Fixed further occurrences of SQL injection vulnerabilities by replacing string concatenation with parameterized queries.
- Eliminated potential SQL injection threats by refactoring query construction methods to use secure practices.
- Secured database interactions by mitigating SQL injection risks associated with dynamic query strings.
- Strengthened application security by resolving remaining SQL injection issues linked to string-based query concatenation.
- Refactored the TrainingController to reduce heavy logic in the DoQuizz method, improving maintainability and performance.
- Improved error handling in TrainingController for external API calls to ensure better fault tolerance and user feedback.
- Removed redundant code in the addsession and editsession methods of TrainingController to streamline operations.
- Enhanced date handling in addsession and editsession methods within TrainingController to ensure accurate processing and validation.

---

## v3.3.1

**Summary**: This release includes 1 new features, 15 bug fixes, 0 enhancements, and 0 completed tasks.

### ✨ Features

- Updated the Organization Manager’s Services section to improve usability and provide clearer service management options.

### 🐛 Fixes

- Fixed an issue in the Organization Manager module where form submissions did not correctly display the "Submitted By" information.
- Resolved a problem in the Organization Manager’s Final Evaluation step to ensure proper processing and completion of evaluations.
- Corrected a delay in reflecting Zoom meeting status for startup users on mentors’ dashboards, ensuring real-time updates.
- Fixed tagging functionality in the Organization Manager and Admin Survey module to allow proper assignment and filtering of survey tags.
- Addressed multiple issues in the Organization Manager and Admin Quizzes module to improve quiz creation, editing, and management.
- Resolved an issue where mentorship requests were not visible in the Organization Manager’s view, ensuring all requests are properly displayed.
- Fixed a bug preventing requests from appearing in the Organization Manager interface, restoring full visibility of incoming requests.
- Improved mentor filtering functionality for startup users to enable accurate and efficient mentor selection.
- Corrected the "Create Draft Quiz" feature in the Organization Manager’s Quizzes module to allow saving quizzes as drafts without errors.
- Fixed attachment upload issues encountered when creating a new program in the Organization Manager module.
- Resolved problems affecting the creation of new programs by newly assigned Organization Managers, ensuring smooth program setup.
- Enhanced the forms builder filtration feature in the Organization Manager module to allow accurate filtering of forms.
- Addressed issues in the Admin module related to creating new organizations, ensuring successful organization setup without errors.
- Fixed evaluation form issues for Judger users during the screening process to ensure accurate and complete form submissions.
- Resolved status display problems on the Judger user dashboard to accurately reflect current evaluation statuses.

---

## v3.4.0

**Summary**: This release includes 7 new features, 47 bug fixes, 24 enhancements, and 0 completed tasks.

### ✨ Features

- Implemented the ability to send messages directly within job postings to improve communication between employers and candidates.
- Introduced a filter by Program Type in the Program List to enable users to easily sort and find programs based on their category.
- Added a new Program Type labeled "Hackathon" to categorize and manage hackathon-related programs separately.
- Developed a dedicated “Team Formation” step exclusively for Hackathon Programs to facilitate participant collaboration and team setup.
- Enabled participants in Hackathon Programs to either create a new team or join an existing one, streamlining team management.
- Created a detailed single-page view for individual Hackathon Programs to provide comprehensive information and enhance user experience.
- Allowed Startup Team leads to submit their final project deliverables through the platform, ensuring streamlined project submission for hackathons.

### 🐛 Fixes

- Fixed issue with role assignments and access permissions for Startup, Admin, and Organization Manager users in Past Hackathon modules.
- Resolved an issue where step changes made by Admin were not retained after navigating away and returning to the submission page; step status now saves correctly.
- Removed the redundant "Apply" button from the Hackathon details page for Admin users to streamline the interface.
- Corrected the synchronization issue where step status changes made by Admin were not reflected on the Startup user interface; status now updates properly.
- Addressed various bugs related to Hackathon submissions management for Admin users to improve stability and functionality.
- Fixed the problem preventing Admin users from successfully editing organization names.
- Resolved the issue where the custom application form was missing when Startup users clicked "Apply" on hackathons.
- Corrected the application deadline text display for Startup users to show accurate, dynamic deadlines across all programs.
- Fixed navigation and display issues encountered by Startup users when clicking the "Details" button on hackathon pages.
- Resolved error messages appearing for Startup users when accessing the Messages section.
- Fixed input validation errors that occurred when Admin or Organization Manager users entered 100 characters in Title, Step, or Description fields.
- Addressed input validation to prevent errors when non-numeric values were entered for "Maximum Number of Team Members" by Admin or Organization Manager users.
- Improved form design adjustments for Admin and Organization Manager roles to enhance usability and consistency.
- Fixed attachment upload issues encountered during Hackathon program creation by Admin and Organization Manager users.
- Resolved errors occurring when new Startup users joined a program, ensuring smooth onboarding.
- Enabled Admin users to successfully add custom forms within Hackathon modules.
- Fixed scheduling conflicts allowing Admin and Organization Manager users to assign mentors or startups to multiple sessions with overlapping time slots.
- Resolved issue preventing Organization Managers from adding traditional training sessions.
- Fixed attachment upload problems during program creation by Organization Managers.
- Addressed various bugs affecting the Startup user dashboard for improved performance and accuracy.
- Fixed "413 Request Entity Too Large" error occurring during registration form submissions by Startup users.
- Corrected errors related to contact information input and display for Startup users.
- Resolved issues preventing Startup users from adding or changing their profile images.
- Fixed problems encountered during the creation of new mentor accounts, specifically with the Industry Expertise field.
- Prevented creation of duplicate program names by Organization Managers to maintain data integrity.
- Resolved issues in the beta version affecting Organization Manager access to training results.
- Fixed bugs preventing Organization Managers from adding new programs in the beta environment.
- Addressed the issue of empty quiz pages appearing for Startup users.
- Fixed various quiz-related issues affecting Startup users to ensure proper functionality.
- Resolved display and upload issues related to program images for Mentor users.
- Fixed problems with viewing and updating startup information fields such as financial data and pitch decks for Startup users.
- Corrected synchronization issues between Mentor users’ calendars and the application.
- Updated permissions to allow Startup users to attach files within data forms securely.
- Fixed final screening process issues encountered by Organization Managers.
- Resolved bugs affecting the display and management of screening results for Organization Managers.
- Corrected weighting calculation and assignment issues within Organization Manager workflows.
- Improved assessment design tools and functionality for Organization Managers.
- Fixed bugs related to the creation of assessments by Organization Managers.
- Resolved issues within the question bank management for Organization Managers.
- Enhanced the survey design interface for Organization Managers, including adding new questions.
- Fixed the "View file" button functionality within forms for Startup users to enable proper file access.
- Resolved issues with delegation attachments during program editing.
- Fixed attachment submission problems within forms managed by Organization Managers.
- Improved sorting features across all modules for Organization Managers to enhance data organization.
- Addressed bugs affecting training session management for Organization Managers.
- Fixed issues related to user registration via LinkedIn integration.
- Resolved filtering and localization issues on the Mentors page for Startup users.

### 🚀 Enhancements

- Resolved an error occurring for Startup users when clicking on the Messages section during test execution.
- Fixed an issue where entering non-numeric values in the "Maximum Number of Team Members" field caused an error for Admin and Organization Manager roles during test execution.
- Updated and refined the form design for Admin and Organization Manager interfaces to improve usability and consistency.
- Added a filter option by Program Type in the Program List to enhance search and navigation capabilities during test execution.
- Introduced a new Program Type labeled "Hackathon" to categorize relevant programs within the system.
- Enabled Admin users to add custom forms specifically for Hackathon programs within the Test Execution module.
- Implemented frontend support for the newly added Hackathon Program Type, ensuring proper display and interaction.
- Created detailed test cases to validate the functionality and integration of the Hackathon Program Type.
- Developed backend logic and database support to fully integrate the Hackathon Program Type into the system.
- Designed comprehensive test cases to verify the functionality of the Program Type filter within the Program List.
- Implemented frontend components to support the Program Type filter feature in the Program List interface.
- Developed backend services to enable filtering programs by type in the Program List.
- Added a new “Team Formation” step exclusively for Hackathon programs within the Test Execution workflow.
- Designed test cases to ensure correct implementation and behavior of the “Team Formation” step for Hackathon programs.
- Implemented frontend features to support the “Team Formation” step in Hackathon programs during test execution.
- Developed backend functionality to manage and process the “Team Formation” step for Hackathon programs.
- Conducted test execution for the LinkedIn registration integration to ensure seamless user sign-up via LinkedIn.
- Performed test execution on form submission attachments functionality for Organization Manager users to verify proper handling and storage.
- Added the capability to view detailed information on a single Hackathon page during test execution.
- Designed test cases to validate the display and functionality of the single Hackathon detail page.
- Implemented frontend components to support viewing a single Hackathon page with all relevant details.
- Created test cases for the discovery and viewing of Hackathon programs targeted at Startup users.
- Developed frontend features enabling Startup users to discover and view Hackathon programs effectively.
- Implemented backend services to support the discovery and viewing of Hackathon programs for Startup users.

---

## v3.4.1

**Summary**: This release includes 52 new features, 130 bug fixes, 66 enhancements, and 3 completed tasks.

### ✨ Features

- The Project Submissions page within the hackathon program has been hidden for Admin and Organization Manager roles to streamline their interface.
- When a new startup is created, the dashboard no longer displays an email verification prompt message to avoid unnecessary notifications.
- The design of the "View My Team" page has been refined to improve usability and visual consistency.
- City selection and display functionality in the Edit Project section have been enhanced for better accuracy and user experience.
- An "Edit Team" button has been added to the team information display, allowing users to modify team details directly.
- Pagination and export options have been implemented for tables on the "Manage Hackathon" page to facilitate data navigation and reporting.
- Final evaluation features have been implemented in the Single Program Report to support comprehensive assessment processes.
- A dedicated Mentorship & Workshop tab has been added to the hackathon interface to organize related activities and resources.
- Hackathon teams are now presented in card format for a more intuitive and visually appealing list view.
- The Single Hackathon view now includes sections to display mentors and judges associated with the event.
- Content for the Program Landing Page has been developed to provide clear and engaging information to users.
- The Admin and Organization Dashboard now includes a "Startup By Program" section for better program-specific startup management.
- Admin and Organization Managers can now view and analyze all user login activity to enhance monitoring and security.
- A dedicated Hackathon Admin Dashboard has been introduced to centralize administrative controls and insights.
- A "Likes" feature has been added to the Winning Teams page, enabling users to express appreciation for winning solutions.
- Access to the Winning Teams page has been configured to support public viewing or role-based restrictions as needed.
- Winning Teams page results are now dynamically fetched from the backend to ensure up-to-date information is displayed.
- Users can view detailed solution information on the Winning Teams page via a popup or modal window.
- The Single Hackathon page now includes a "Manage Team" or "View Team" button for easier team administration.
- Users can now edit or delete tasks within the Hackathon Workspace to maintain accurate task management.
- Functionality to assign or reassign to-do items in the Hackathon Workspace has been added to improve task delegation.
- Task status updates can now be performed within the Hackathon Workspace to reflect progress accurately.
- A to-do list view has been introduced in the Hackathon Workspace for better task tracking.
- Users can create new to-do items directly within the Hackathon Workspace to facilitate task planning.
- The Hackathon Workspace now displays the sender information for messages and files to enhance communication transparency.
- Users can delete files within the Hackathon Workspace to manage shared resources effectively.
- File download functionality has been added to the Hackathon Workspace for easy access to shared documents.
- Shared files are now viewable within the Hackathon Workspace to centralize resource management.
- Users can upload new files in the Hackathon Workspace to support collaboration and information sharing.
- Hackathon team members have access to a dedicated Team Workspace for coordinated project work.
- Team members can view join requests to manage team membership efficiently.
- Existing team members can be removed by authorized users to maintain appropriate team composition.
- Pending invitations to join teams can be canceled to control membership invitations.
- Dynamic pages functionality has been added to programs to allow customizable content and layouts.
- Program visibility settings have been enhanced to support public access configurations.
- Team members can invite new members to join their hackathon teams to facilitate team growth.
- Detailed team information can be viewed by authorized users to understand team composition and roles.
- The registration and step change forms have been reviewed and updated to improve user flow and accuracy.
- Users can track their own login activity to monitor account access history.
- Startups can define their project ideas and track progress within the platform for better project management.
- Startups are now able to apply for hackathons directly through the system to streamline participation.
- Startups can discover and view available hackathons to identify relevant opportunities.
- Technical interface jobs have been implemented to support system integration and background processes.
- The judging process is now managed within the platform, including winner announcement capabilities.
- Implementation details have been added to the Single Program Report to support comprehensive program tracking.
- Final screening criteria have been defined and integrated to standardize evaluation processes.
- Functionality to export all submitted forms has been added for reporting and analysis purposes.
- User notification preferences for mentorship reminders can now be configured to personalize communication.
- Delivery logs for mentorship reminder notifications have been implemented to track message status.
- Programs can configure rules for mentorship reminder scheduling to automate notification workflows.
- Mentorship reminder notifications support multi-channel delivery to ensure effective user engagement.
- The system now automatically schedules mentorship reminders by default within program settings to maintain timely communication.

### 🐛 Fixes

- Fixed issues related to quiz management in the Org Manager module, specifically addressing problems with managing quiz questions.
- Resolved bugs affecting tag management functionality for Org Manager and Admin roles.
- Corrected errors encountered when creating quizzes within the Org Manager interface.
- Addressed screening-related issues experienced by Judger users during the evaluation process.
- Fixed notification logic to prevent all users from receiving welcome emails or system notifications when a new user is created as a Judger user.
- Corrected user creation process in Admin panel to prevent saving unspecified gender as “Admin.”
- Fixed issue where user verification actions performed by Admin were not being saved.
- Prevented duplicate creation of training sessions when Admin or Org Manager clicks the Submit button multiple times.
- Resolved display issue where all mentors appeared on the mentor page after a team member was removed, even if no mentors were assigned to the hackathon program.
- Fixed the "Apply" button on the teams page to either remove it after member removal or ensure it functions correctly.
- Eliminated erroneous error message shown to Startup users when a team leader successfully removes a member.
- Clarified and fixed behavior when a team fulfills a request and the startup clicks the "Request to Join" button.
- Resolved invitation handling issues related to rejecting and subsequently re-accepting invitations in the Startup module.
- Corrected dashboard display to show the actual hackathon program a startup joined, rather than the last program added to the system.
- Fixed status update issue where hackathons without custom forms did not change to ‘Details’ after a member joined by invitation without applying via form.
- Fixed error message that appeared when attempting to create a new user who already exists in the system.
- Resolved errors encountered during the startup registration process.
- Fixed errors appearing on the Startup Hub page for Admin and Org Manager users.
- Addressed errors occurring when Startup users attempt to create a team.
- Corrected the reset button functionality for images within program creation and editing pages for Admin and Org Manager.
- Fixed issues related to public visibility settings of programs managed by Admin and Org Manager.
- Added missing "In Progress" status option for startups; previously only "To Do" and "Done" were available.
- Made training session titles clickable in the "My Latest Training Courses & Sessions" section to display detailed information.
- Restricted task assignment and reassignment capabilities to team leaders only; team members are no longer able to assign tasks.
- Improved design and ensured proper display of pop-up messages for task deletion and editing in Arabic localization for Startup leaders and members.
- Fixed non-functional checkbox controls within the Startup module.
- Enhanced design and Arabic localization support for pop-up messages when adding tasks by Startup leaders and members.
- Corrected issue where task descriptions were not displayed after tasks were added.
- Implemented sorting of displayed files from newest to oldest in the Startup module.
- Fixed missing description display for shared files after they are added by startups.
- Improved design and Arabic localization of pop-up messages when deleting shared files for Startup leaders and members.
- Resolved errors occurring when uploading ZIP files or file types other than PDF or PPT.
- Enhanced design and Arabic localization of pop-up messages during shared file uploads by Startup leaders and members.
- Fixed issue where startup images did not display on the Teams page.
- Added pagination support for Past Hackathon listings accessible to all users.
- Corrected translation of city names when switching to Arabic localization for Admin and Org Manager.
- Fixed errors encountered during organization creation in the Admin panel.
- Resolved issue where team images added by startups did not appear on the Edit Team Info or Team Management pages.
- Fixed region display to show Arabic names when English localization is selected in the Startup module.
- Corrected display of judges assigned to hackathon programs on the Mentors and Judging Panel page for Startup, Admin, and Org Manager roles.
- Fixed inability to select cities in the Edit Project Idea form when Arabic localization is active.
- Resolved errors in Org Manager when navigating different screens without adding any program or steps.
- Fixed issue where hackathon names and descriptions disappeared when switching localization from English to Arabic for all users.
- Enhanced the design of the “Details” section in the Manage Participating Teams interface.
- Allowed Startup members to view pending invitations.
- Prevented adding team members beyond the maximum allowed number in the Startup module.
- Restricted startups from receiving and accepting invitations to multiple teams simultaneously after joining a team as leader or member.
- Adjusted the design of the screening management page for Admin and Org Manager.
- Enhanced the design of the Final Screening and Screening management pages for Admin and Org Manager.
- Adjusted the maximum length allowed for project descriptions in the Manage Projects section within Manage Hackathon.
- Fixed export functionality from Manage Hackathon sections to correctly display views and details in exported files.
- Resolved issue where users did not receive password reset emails.
- Added invitation notification on the dashboard for startups after first-time registration and email verification.
- Fixed errors occurring when Startup members accept or reject invitations from the dashboard.
- Corrected issues related to updating company profiles in the Startup module.
- Fixed errors encountered when creating new hackathon programs in Org Manager.
- Made the hackathon header fixed and visible on the dashboard when hackathons are available.
- Resolved issue where mentors assigned to hackathon programs did not appear on the program’s mentors page.
- Fixed errors occurring when creating new organization managers in the Admin panel.
- Corrected display of attachments submitted with final evaluation forms for Judger users in company data.
- Updated the message text displayed to startups upon submitting the final evaluation form.
- Resolved discrepancy between final evaluation result counts shown to Judger and Admin users.
- Fixed errors occurring when creating new startups, mentors, or any user in the Admin panel.
- Allowed startup users to be removed from the system and re-register for the same hackathon without issues.
- Fixed navigation so that invited startups open the 'Manage Team' page directly upon invitation acceptance.
- Added Back and Dashboard buttons to the View Team page for Startup users.
- Fixed display of leader and member images on the View Team page.
- Implemented Log Viewer functionality for Admin and Org Manager roles.
- Fixed the filter functionality in User Activities for Admin and Org Manager.
- Corrected export functionality in User Activities for Admin and Org Manager.
- Fixed incorrect location display for Admin and Org Manager users.
- Added a button inside the Hackathons page for all users to return to the Dashboard.
- Fixed missing sections in the details view when creating steps from a hackathon in Org Manager.
- Corrected default form selection behavior when creating a Final Evaluation step in Org Manager.
- Fixed issue where Screening type did not display after creating or editing a screening step in Org Manager.
- Resolved error appearing when clicking the Manage Hackathon button after creating a new hackathon program.
- Modified organization creation process to display only hackathons associated with the newly created organization.
- Removed unused and redundant UI code to improve performance and maintainability.
- Restricted team leaders from inviting or accepting members beyond the maximum allowed team size.
- Fixed issue where changing a startup’s status from 'Disapproved' to 'Team Formation' did not update correctly.
- Added a 'Manage Team' button in the Admin panel to enable team leaders to share progress on initial project ideas.
- Cleared all form inputs when a startup reapplies after being disapproved to prevent retention of previous data.
- Replaced the "Request Sent" button with "View My Team" for team members after leader approval of join requests.
- Prevented team members from adding, removing, approving, or rejecting join requests; only team leaders have these permissions.
- Enabled team members to edit the project idea within their team.
- Fixed Admin view to prevent the 'Manage My Team' button from being incorrectly replaced with 'Request to Join' after sending a join request.
- Updated buttons to replace all 'Apply for Hackathon' and 'Apply – Non-Form' with 'Manage Team' after team creation.
- Displayed the Team page within Hackathon Details to allow new users to request to join a team.
- Adjusted the design of the Add Project Idea interface within Manage Team in the Hackathon module.
- Fixed missing startup profile images on the 'Manage My Team' page.
- Resolved errors occurring when clicking on hackathon details in Admin and Org Manager views.
- Corrected display of custom pages to show the correct hackathon details when a startup joins a hackathon.
- Adjusted sorting on the Submissions page to display items from newest to oldest in the Admin panel.
- Implemented filtering by applicant status on the Submissions page in the Admin panel.
- Fixed issue where the apply button status changed to "Wait for Approval" for hackathons the startup had already joined.
- Resolved errors appearing when clicking on Hackathon details in the Startup module.
- Fixed sorting functionality in Past Hackathon listings for Startup, Admin, and Org Manager users.
- Corrected errors occurring when uploading photos during hackathon creation in the Admin panel.
- Disabled clickable access to other hackathons for startups and admins once a startup’s application has been approved for one hackathon.
- Fixed attachment submission issues in startup user forms.
- Resolved non-functional search and filter features in the Startup module.
- Prevented entering negative or zero values for the 'Maximum Number of Team Members' field in Admin and Org Manager panels.
- Fixed issues related to the maximum number of team members in the Team Formation step for Admin and Org Manager.
- Corrected non-functional Reset button on the Edit Program page for Admin and Org Manager.
- Fixed issue where expired quizzes still appeared in the startup’s quiz list.
- Improved styling of quiz questions for better readability and user experience in the Startup module.
- Resolved form registration submission issues for Judger users.
- Fixed issues related to tasks and mentorship sessions for Startup users.
- Addressed task-related bugs for Mentor users.
- Implemented sorting of schedules from newest to oldest when adding new schedules in Admin and Org Manager.
- Translated the delete schedule pop-up message into Arabic when Arabic localization is selected for Admin and Org Manager.
- Fixed design update issue after deleting a schedule; page now refreshes correctly to reflect changes.
- Resolved error appearing when creating new programs in Admin and Org Manager panels.
- Fixed issue where adding mentorship sessions between mentors and startups created unclear rows in the mentor’s schedule table.
- Corrected inaccurate numbers shown in Program Details due to mentorship sessions table inconsistencies.
- Fixed non-functional "Request Join" button in the Startup module.
- Resolved errors occurring when team leaders click the Manage Team button.
- Fixed login errors for newly created organization managers.
- Addressed various issues on the team page within the Startup module.
- Fixed inability to add mentorship sessions between mentors and startups in Admin and Org Manager panels.
- Updated survey creation form to include required category input, allowing surveys to be saved successfully.
- Fixed Like and Love functionality for visitors and corrected default liked status of teams in the Results & Winners section.
- Corrected popup message display when a startup member accepts an invitation.
- Fixed issue where the Hackathon header did not appear on the dashboard for any user.
- Restricted visibility of team tasks and files to team members only; hidden from all other users.
- Removed the incorrect 'Admin' label appearing after Mentorship Requests in the Program List for Org Manager.
- Fixed issues related to the Team Formation step in Org Manager’s Steps module.
- Resolved issue where trainers did not receive email notifications when creating online or traditional training sessions.
- Fixed bugs related to survey and quiz results display for Admin and Org Manager.
- Resolved errors encountered during registration as a Startup in the Admin panel.

### 🚀 Enhancements

- Implemented functionality to upload new files within the Hackathon Workspace during test execution.
- Enabled the application process for startups to apply for participation in hackathons during test execution.
- Developed and tested the Hackathon Mentorship & Workshop tab functionality within the test execution phase.
- Implemented frontend components for the Hackathon Mentorship & Workshop tab to enhance user interaction.
- Developed backend services supporting the Hackathon Mentorship & Workshop tab features.
- Designed test cases to verify the display of mentors and judges in the single hackathon view.
- Implemented frontend interface to display mentors and judges in the single hackathon view.
- Developed backend logic to support viewing mentors and judges in the single hackathon interface.
- Created test cases for discovering and viewing hackathons targeted at startups.
- Designed test cases for the display and functionality of hackathon team list cards.
- Developed frontend components for presenting hackathon team list cards.
- Implemented backend services to manage data for hackathon team list cards.
- Executed tests for submitting the final project by the startup team lead.
- Developed frontend functionality allowing startup team leads to submit their final projects.
- Implemented backend processes to handle final project submissions from startup team leads.
- Designed test cases for canceling pending invitations within hackathon teams.
- Developed frontend features enabling users to cancel pending invitations in hackathon teams.
- Created backend support for managing cancellation of pending invitations in hackathon teams.
- Designed test cases for content management on the program public page.
- Implemented frontend elements to display program public page content effectively.
- Developed backend functionality to manage and serve content on the program public page.
- Executed tests reviewing registration processes and form step changes.
- Developed frontend components for reviewing registration and modifying form steps.
- Implemented backend logic supporting registration review and form step changes.
- Designed test cases for managing the judging process and announcing winners.
- Developed frontend interfaces to facilitate judging management and winner announcements.
- Implemented backend services to support judging workflows and result publication.
- Created test cases for the startup application process to hackathons.
- Developed frontend application forms and workflows for startups applying to hackathons.
- Implemented backend processing for startup hackathon applications.
- Executed tests to view and analyze all user login activities accessible by Admin and Organization Manager roles.
- Conducted test execution for the "Track My Login Activity" feature for individual users.
- Executed tests for defining project ideas and tracking progress within startup teams.
- Developed frontend interfaces for startups to define project ideas and monitor progress.
- Implemented backend services to support project idea definition and progress tracking for startups.
- Tested the Admin and Organization Manager ability to enter zero or negative values in the 'Maximum Number of Team Members' field.
- Conducted test execution addressing issues related to the 'Maximum Number of Team Members' during the team formation step for Admin and Organization Manager roles.
- Developed frontend dashboard components displaying startups by program.
- Implemented backend services to support the startup-by-program dashboard view.
- Designed test cases for the Hackathon Admin Dashboard functionalities.
- Developed frontend features for the Hackathon Admin Dashboard interface.
- Implemented backend logic supporting the Hackathon Admin Dashboard operations.
- Executed tests for the “Manage Team” and “View Team” buttons on the single hackathon page.
- Developed frontend components for the “Manage Team” and “View Team” buttons on the single hackathon page.
- Implemented backend support for team management and viewing functionalities on the single hackathon page.
- Designed test cases for viewing and analyzing all user login activities.
- Developed frontend interfaces to display and analyze user login activity data.
- Implemented backend services to collect and provide user login activity information.
- Designed test cases to log the last user activity data accurately.
- Developed frontend mechanisms to capture and log the last user activity.
- Implemented backend processes for recording and storing last user activity data.
- Designed test cases for managing dynamic pages within the program module.
- Developed frontend support for dynamic page rendering in the program section.
- Implemented backend functionality to manage dynamic pages within programs.
- Designed test cases for public custom form submission processes.
- Developed frontend components enabling users to submit custom public forms.
- Implemented backend services to handle submissions of public custom forms.
- Executed tests for inviting new members to hackathon teams.
- Developed frontend features allowing hackathon teams to invite new members.
- Implemented backend support for managing hackathon team member invitations.
- Designed test cases for viewing detailed information about hackathon teams.
- Developed frontend interfaces to display hackathon team details.
- Implemented backend services to retrieve and manage hackathon team detail data.
- Conducted test execution for mentor user tasks within the platform.
- Completed missing data entries to ensure data integrity across modules.
- Executed tests for active programs accessible to mentors and judges across all companies.

### 🛠️ Tasks

- Resolved an issue allowing multiple startups to simultaneously request the same mentorship slot with a single mentor, ensuring exclusive booking per slot.
- Enhanced the design of the Suspicious Logins feature for Admin and Organization Managers to improve usability and security monitoring.
- Implemented a feature displaying all companies alongside their active programs for Mentors and Judges, facilitating better program management and oversight.

---

## v3.5.0

**Summary**: This release includes 37 new features, 16 bug fixes, 1 enhancements, and 0 completed tasks.

### ✨ Features

- Updated the placement of the Startup Hub link within the main navigation menu to improve accessibility and user experience.
- Removed the "Done" and "Not Done" status cards from the Surveys Startup Report for Super Admin and Tenant Admin roles to streamline report content.
- Removed the URL link from the Total Surveys card in the Surveys Startup Report for Super Admin and Tenant Admin roles to simplify the report interface.
- Removed the "Done" and "Not Done" status cards from the Mentor Feedback Report for Super Admin and Tenant Admin roles to enhance report clarity.
- Removed the URL link from the Total Surveys card in the Mentor Feedback Report for Super Admin and Tenant Admin roles to reduce visual clutter.
- Added the ability for Super Admin and Tenant Admin users to export the list of startups for offline review and analysis.
- Implemented filtering options for startups, enabling Super Admin and Tenant Admin users to refine startup lists based on specific criteria.
- Updated the URL link in the Mentors card for Super Admin and Tenant Admin users to ensure accurate navigation within the platform.
- Enabled export functionality for mentorship sessions reports, allowing Super Admin and Tenant Admin users to download session data.
- Added a view that displays all startups currently in the Final Screening stage for easier monitoring and management.
- Updated the URL link in the Judgers card for Super Admin and Tenant Admin users to correct navigation paths.
- Renamed the term "Judger" to "Judge" throughout the platform to align with standard terminology.
- Added a feature to view the complete list of judges accessible to Super Admin and Tenant Admin users.
- Enabled viewing of all training sessions along with their durations to provide detailed session insights.
- Provided the ability to view all training sessions without duration details for a simplified overview.
- Developed a comprehensive training sessions report accessible by Super Admin and Tenant Admin users.
- Displayed the total duration of mentorship sessions within reports for Super Admin and Tenant Admin users to facilitate time tracking.
- Added export capabilities for training sessions reports for Super Admin and Tenant Admin users to support data analysis.
- Implemented backend functionality to create and edit new programs, enhancing program management capabilities.
- Added the option to include technology categories within program or startup profiles for better classification.
- Introduced the ability to add industry classifications to improve categorization and filtering.
- Developed settings for managing industry and sector information to standardize data entry.
- Enabled viewing of all training sessions with durations filtered by selected program for targeted reporting.
- Provided the ability to view all training sessions associated with a selected program without duration details.
- Added a view for all mentorship sessions with durations filtered by selected program to track engagement.
- Updated the URL link in the Mentors card within program reports for Super Admin and Tenant Admin users to ensure correct navigation.
- Implemented filtering options for mentorship sessions, allowing Super Admin and Tenant Admin users to refine session data.
- Updated the URL link in the Mentors in Programs card for Super Admin and Tenant Admin users to correct navigation paths.
- Added filtering capabilities to the mentors list for Super Admin and Tenant Admin users to facilitate user management.
- Enabled export functionality for the Final Screening report, allowing Super Admin and Tenant Admin users to download screening data.
- Added a view to display all startups currently in the Screening stage for improved tracking and management.
- Provided the ability to view all online training sessions to distinguish between training delivery methods.
- Enabled viewing of all offline training sessions to support comprehensive training management.
- Added a dedicated view for all online training sessions to facilitate user access and reporting.
- Implemented filtering options for training sessions accessible to Super Admin and Tenant Admin users to refine session lists.
- Displayed the count of mentorship requests for Super Admin and Tenant Admin users to monitor demand and engagement.
- Enhanced the startup module with notification features to improve communication and alert management.

### 🐛 Fixes

- Fixed the pagination and row count functionality on the Users page in the Admin panel to ensure proper navigation and display of user data.
- Resolved an issue where programs under organizations did not appear when filtering startups with Arabic localization selected.
- Addressed various issues related to Mentorship Requests in the Admin panel to improve functionality and user experience.
- Translated the "Apply Filter" button into Arabic when Arabic localization is selected on the Mentorship Requests page after applying filters.
- Fixed multiple technology-related issues within the Admin panel to enhance stability and performance.
- Corrected the popup message on the Startup profile page that incorrectly appeared despite technologies being previously added in the Admin panel.
- Resolved issues related to Industry data and functionality within the Admin panel.
- Fixed the Server Error 500 that occurred when accessing the Startup profile page.
- Improved the account creation design by properly handling cases where a founder or mentor registers using existing data, such as an already registered email.
- Fixed the error that appeared when clicking the edit button on a startup or idea profile.
- Adjusted the design of the Training Session Duration field in Admin reports and removed the redundant "Hours" label following the duration value.
- Corrected the sorting order in training sessions to display entries from newest to oldest.
- Fixed the issue preventing startups from adding tasks during testing and resolved errors occurring when clicking on existing tasks.
- Removed the Winner Selection page from the Program’s section in the Admin and Org Manager interfaces as per updated requirements.
- Translated the delete log confirmation pop-up message in Reminder Logs into Arabic when Arabic localization is enabled.
- Addressed multiple issues affecting the Landing Page to improve layout, functionality, and user experience.

### 🚀 Enhancements

- Developed and implemented test cases for the "View Training Sessions Report" feature, specifically for Super Admin and Tenant Admin roles, including completion of all related subtasks associated with this story.

---

