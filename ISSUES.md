
Open Issues

 ✗ [LOW] Unsafe JQuery Plugin
   Finding ID: 21c6de63-96d2-4990-9a84-4dafd28a5fa8
   Path: public/js/jquery-ui.js, line 1178
   Info: Unsanitized input to JQuery plugin from options flows into $, where it may be evaluated as HTML. If the input is intended to be used as a selector, then a JQuery method that only accepts selectors should be used. If the input is intended to be used as HTML, the developer needs to ensure that it's well documented that the user is responsible for sanitizing the input.

 ✗ [LOW] Use of Password Hash With Insufficient Computational Effort
   Finding ID: 86d4fbb7-45e3-4d28-9e3e-ecdec6887bbc
   Path: public/assets/revolution/php/twitter/connection/User.php, line 90
   Info: hash_hmac hash (used in hash_hmac) is insecure. Consider changing it to a secure hashing algorithm.

 ✗ [LOW] Use of Password Hash With Insufficient Computational Effort
   Finding ID: e16e5e0e-9573-41de-bd8a-b91b13e50e26
   Path: app/Http/Controllers/quizzesController.php, line 438
   Info: MD5 hash (used in md5) is insecure. Consider changing it to a secure hashing algorithm.

 ✗ [LOW] Use of Password Hash With Insufficient Computational Effort
   Finding ID: 45ae7089-45a2-4b85-bb84-8e9b64a09a43
   Path: app/Http/Controllers/CyclesController.php, line 733
   Info: SHA1 hash (used in sha1) is insecure. Consider changing it to a secure hashing algorithm.

 ✗ [LOW] Use of Password Hash With Insufficient Computational Effort
   Finding ID: 1cfcfed6-126a-4f97-82fc-5717eec20129
   Path: app/Http/Controllers/CyclesController.php, line 311
   Info: SHA1 hash (used in sha1) is insecure. Consider changing it to a secure hashing algorithm.

 ✗ [LOW] Use of Password Hash With Insufficient Computational Effort
   Finding ID: 4a296d73-5ea1-40c6-878c-a18c7912493b
   Path: app/Http/Controllers/API/ProgramController.php, line 315
   Info: SHA1 hash (used in sha1) is insecure. Consider changing it to a secure hashing algorithm.

 ✗ [LOW] Use of Password Hash With Insufficient Computational Effort
   Finding ID: 0f8518a0-e022-47bb-931d-68c123cf8122
   Path: app/Http/Controllers/API/ProgramController.php, line 149
   Info: SHA1 hash (used in sha1) is insecure. Consider changing it to a secure hashing algorithm.

 ✗ [LOW] Cross-site Scripting (XSS)
   Finding ID: 524688f8-8804-4b68-90d3-d9472f6b3a6b
   Path: test_chat_api.php, line 15
   Info: Unsanitized input from data from a remote resource flows into the echo statement, where it is used to render an HTML page returned to the user. This may result in a Cross-Site Scripting attack (XSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: dc1573b2-23f4-499f-9d78-802b841e67cb
   Path: public/src/js/scripts/pages/page-users.js, line 43
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 099d71e0-239d-4249-8350-09bc38b4a016
   Path: public/app-assets/js/scripts/pages/page-organizations.js, line 76
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: da26e427-85fa-4eaf-8fc6-26ec137e481c
   Path: public/app-assets/js/scripts/pages/page-organizations.js, line 79
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 9a6e74b7-6f07-4bbc-9047-65808fe58a64
   Path: public/app-assets/js/scripts/pages/page-organizations.js, line 80
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 38ed077f-8845-467e-9202-8f13a9d9b62b
   Path: public/app-assets/js/scripts/pages/page-users.js, line 78
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: eb2c5f8c-5c7f-46e8-b232-1a2ef85c3dd9
   Path: public/app-assets/js/scripts/pages/page-users.js, line 79
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 045a7a57-93f3-4f0b-9b49-77e140c299b1
   Path: public/app-assets/js/scripts/pages/page-users.js, line 80
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: c2a1e78c-f1bf-4af9-adc9-c2d2dcd703c3
   Path: public/app-assets/js/scripts/pages/page-users.js, line 81
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: c01959a8-9edf-45f0-a6c9-aabb65f7843a
   Path: public/app-assets/js/scripts/pages/page-users.js, line 82
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 847e19cd-6428-4684-8914-317e6fc83a60
   Path: public/app-assets/js/scripts/pages/page-users.js, line 83
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: f11e32e1-b632-4b8e-8bbd-f7bbb84ad586
   Path: public/src/js/scripts/pages/page-users.js, line 38
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 7eff8ecf-b21f-4eb9-9844-36a4c7d5fdc5
   Path: public/src/js/scripts/pages/page-users.js, line 39
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 94adf611-23b9-40d0-9c86-31b66de67b5f
   Path: public/src/js/scripts/pages/page-users.js, line 40
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: f771988d-c9f5-418a-9cb6-6e9858d17fdc
   Path: public/src/js/scripts/pages/page-users.js, line 41
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 432e0fb5-4094-46d8-9691-1c417d52fa2c
   Path: public/src/js/scripts/pages/page-users.js, line 42
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: d766022e-cc39-4768-b375-6ac3dcbb97f5
   Path: public/app-assets/js/1.js, line 290
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 5e5bcedd-b6e8-4f87-b23f-fb11e82d8fc0
   Path: public/app-assets/js/scripts/pages/page-organizations.js, line 77
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] Path Traversal
   Finding ID: 8980c180-54e7-4686-b3c5-c56fda3ac268
   Path: public/assets/revolution/php/facebook/class-facebook.php, line 69
   Info: Unsanitized input from data from a remote resource flows into file_get_contents, where it is used as a path. This may result in a Path Traversal vulnerability and allow an attacker to read arbitrary files.

 ✗ [MEDIUM] Open Redirect
   Finding ID: fbe68847-0f00-4f61-b2cd-4765829e3833
   Path: app/Http/Controllers/Mentor/MentorshipTaskController.php, line 766
   Info: Unsanitized input from a web form flows into redirect, where it is used as input for request redirection. This may result in an Open Redirect vulnerability.

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 1baf812b-f0c7-4374-8e3e-cb0322162098
   Path: public/app-assets/js/1.js, line 291
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: aff29edd-2db4-4588-a19b-515334d4d140
   Path: public/app-assets/js/1.js, line 292
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 2d3a81f3-7d6e-49f3-8e89-9335a575573d
   Path: public/app-assets/js/scripts/pages/page-organizations.js, line 78
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] Use of Hardcoded Passwords
   Finding ID: 3fd30ad3-2fb4-4631-a972-8534d0c6115f
   Path: resources/lang/ar/admin.php, line 112
   Info: Do not hardcode passwords in code. Found a hardcoded password used in variable.

 ✗ [MEDIUM] Use of Hardcoded Passwords
   Finding ID: fc6acfb1-8549-4069-928e-41530bdf2e44
   Path: resources/lang/ar/auth.php, line 9
   Info: Do not hardcode passwords in code. Found a hardcoded password used in variable.

 ✗ [MEDIUM] Use of Hardcoded Passwords
   Finding ID: b34a2847-d7a8-453a-83c9-01c1bd8bb358
   Path: resources/lang/ar/passwords.php, line 16
   Info: Do not hardcode passwords in code. Found a hardcoded password used in variable.

 ✗ [MEDIUM] Use of Hardcoded Passwords
   Finding ID: e9f97225-c42f-42c9-b882-95d8fd7165ef
   Path: resources/lang/ar/validation.php, line 95
   Info: Do not hardcode passwords in code. Found a hardcoded password used in variable.

 ✗ [MEDIUM] Use of Hardcoded Passwords
   Finding ID: 72367952-d7a5-4ad8-94e1-be7b465b1198
   Path: resources/lang/ar/validation.php, line 170
   Info: Do not hardcode passwords in code. Found a hardcoded password used in variable.

 ✗ [MEDIUM] Use of Hardcoded Passwords
   Finding ID: 0e9e1c69-30c8-403b-b823-97f781d16e87
   Path: resources/lang/ar/validation.php, line 171
   Info: Do not hardcode passwords in code. Found a hardcoded password used in variable.

 ✗ [MEDIUM] Use of Hardcoded Passwords
   Finding ID: b1537f3e-dd55-4a49-be3b-2327ce5b188c
   Path: resources/lang/de/passwords.php, line 16
   Info: Do not hardcode passwords in code. Found a hardcoded password used in variable.

 ✗ [MEDIUM] Use of Hardcoded Passwords
   Finding ID: 802304b1-71c4-4c44-8e24-f7b44571cefa
   Path: resources/lang/en/passwords.php, line 16
   Info: Do not hardcode passwords in code. Found a hardcoded password used in variable.

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 8b1ec83c-96c0-4a2b-a2bc-af9ec80bfd09
   Path: public/app-assets/js/scripts/pages/page-organizations.js, line 75
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 47432d38-b304-4a17-bd56-2c4c3c345585
   Path: public/app-assets/js/1.js, line 295
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: 1d11c0fa-11d5-49de-ae29-fc91707c918a
   Path: public/app-assets/js/1.js, line 294
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [MEDIUM] DOM-based Cross-site Scripting (XSS)
   Finding ID: e91c7aa4-0350-4da2-8f7e-bf3acf0a5947
   Path: public/app-assets/js/1.js, line 293
   Info: Unsanitized input from browser storage flows into html, where it is used to dynamically construct the HTML page on client side. This may result in a DOM Based Cross-Site Scripting attack (DOMXSS).

 ✗ [HIGH] Server-Side Request Forgery (SSRF)
   Finding ID: ed3882ef-b915-43c9-95e2-e40720481701
   Path: app/Http/Controllers/HackathonController.php, line 1388
   Info: Unsanitized input from a web form flows into unlink, where it is used as an URL to perform a request. This may result in a Server-Side Request Forgery vulnerability.

 ✗ [HIGH] Server-Side Request Forgery (SSRF)
   Finding ID: cdb86ac1-833a-4765-a2f0-58bf2d49cce6
   Path: app/Http/Controllers/HackathonController.php, line 1372
   Info: Unsanitized input from a web form flows into unlink, where it is used as an URL to perform a request. This may result in a Server-Side Request Forgery vulnerability.

 ✗ [HIGH] SQL Injection
   Finding ID: 9bf33702-58de-4eea-ad91-0c86f0089299
   Path: app/Http/Controllers/ClientCompanyController.php, line 1407
   Info: Unsanitized input from the HTTP request body flows into update, where it is used in an SQL query. This may result in an SQL Injection vulnerability.

 ✗ [HIGH] Path Traversal
   Finding ID: b38fef12-72b7-46fb-89b9-142e1fceee8b
   Path: app/Http/Controllers/HackathonController.php, line 1372
   Info: Unsanitized input from a web form flows into unlink, where it is used as a path. This may result in a Path Traversal vulnerability and allow an attacker to delete arbitrary files.

 ✗ [HIGH] Path Traversal
   Finding ID: 30dfbb85-e59b-446d-b860-3a70179a5185
   Path: app/Http/Controllers/HackathonController.php, line 1388
   Info: Unsanitized input from a web form flows into unlink, where it is used as a path. This may result in a Path Traversal vulnerability and allow an attacker to delete arbitrary files.

 ✗ [HIGH] Path Traversal
   Finding ID: ed905324-7f77-447d-af93-86126b4d4c6a
   Path: app/Http/Controllers/Mentor/MentorshipTaskController.php, line 634
   Info: Unsanitized input from a web form flows into makeDirectory, where it is used as a path. This may result in a Path Traversal vulnerability and allow an attacker to write to arbitrary files.

 ✗ [HIGH] Path Traversal
   Finding ID: 2978bd15-c084-4850-ad62-f1ce9807a0f7
   Path: app/Http/Controllers/Mentor/MentorshipTaskController.php, line 635
   Info: Unsanitized input from a web form flows into makeDirectory, where it is used as a path. This may result in a Path Traversal vulnerability and allow an attacker to write to arbitrary files.

 ✗ [HIGH] Path Traversal
   Finding ID: a39e8efd-d5c9-4b00-9455-14a53e389f05
   Path: app/Http/Controllers/Mentor/MentorshipTaskController.php, line 637
   Info: Unsanitized input from a web form flows into makeDirectory, where it is used as a path. This may result in a Path Traversal vulnerability and allow an attacker to write to arbitrary files.

 ✗ [HIGH] SQL Injection
   Finding ID: ae3c5939-4a94-47de-a678-df8b0d4900fe
   Path: app/Http/Controllers/ClientCompanyController.php, line 1371
   Info: Unsanitized input from the HTTP request body flows into update, where it is used in an SQL query. This may result in an SQL Injection vulnerability.

