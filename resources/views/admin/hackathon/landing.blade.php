<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InnovateFest 2025 - Hackathon Landing Page</title>
    <link rel="stylesheet" href="/css/hackathon-landing.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span>⚡</span>
                InnovateFest 2025
            </div>
            <ul class="nav-links">
                <li><a href="#about">About</a></li>
                <li><a href="#schedule">Schedule</a></li>
                <li><a href="#prizes">Prizes</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><button class="cta-button">Register Now</button></li>
            </ul>
            <div class="mobile-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>InnovateFest 2025</h1>
            <p>48 hours of coding, creating, and innovating.<br>Join 500+ developers in building the future.</p>
            
            <div class="event-info">
                <div class="info-item">
                    <span>📅</span>
                    <span>March 15-16, 2025</span>
                </div>
                <div class="info-item">
                    <span>📍</span>
                    <span>Tech Hub Center</span>
                </div>
            </div>

            <div class="countdown" id="countdown">
                <div class="countdown-item">
                    <div class="countdown-number" id="days">00</div>
                    <div class="countdown-label">Days</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="hours">00</div>
                    <div class="countdown-label">Hours</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="minutes">00</div>
                    <div class="countdown-label">Minutes</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="seconds">00</div>
                    <div class="countdown-label">Seconds</div>
                </div>
            </div>

            <div class="hero-buttons">
                <button class="btn-primary">Register Now - Free!</button>
                <button class="btn-secondary" onclick="scrollToSection('about')">Learn More</button>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section about">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Why Join InnovateFest?</h2>
                <p class="section-subtitle">InnovateFest is where brilliant minds converge to tackle real-world challenges, learn cutting-edge technologies, and build solutions that matter.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3 class="feature-title">Network & Collaborate</h3>
                    <p class="feature-description">Connect with 500+ developers, designers, and entrepreneurs. Build lasting professional relationships and find your next co-founder.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3 class="feature-title">Solve Real Problems</h3>
                    <p class="feature-description">Work on challenges from industry partners including sustainability, healthcare, and social impact projects that make a difference.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⭐</div>
                    <h3 class="feature-title">Learn & Grow</h3>
                    <p class="feature-description">Attend workshops, get mentored by industry experts, and learn new technologies through hands-on experience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Schedule Section -->
    <section id="schedule" class="section schedule">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Event Schedule</h2>
                <p class="section-subtitle">Two action-packed days of learning, building, and innovating</p>
            </div>

            <div class="schedule-tabs">
                <div class="tab-container">
                    <button class="tab-button active" onclick="showSchedule('day1')">Day 1 - March 15</button>
                    <button class="tab-button" onclick="showSchedule('day2')">Day 2 - March 16</button>
                </div>
            </div>

            <div class="schedule-content">
                <div id="day1-schedule" class="schedule-day">
                    <div class="schedule-item">
                        <div class="schedule-time">9:00 AM</div>
                        <div class="schedule-details">
                            <h3>Registration & Welcome</h3>
                            <p>Check-in, breakfast, and opening ceremony</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">10:00 AM</div>
                        <div class="schedule-details">
                            <h3>Keynote Speech</h3>
                            <p>Industry leaders share insights on emerging technologies</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">11:00 AM</div>
                        <div class="schedule-details">
                            <h3>Team Formation</h3>
                            <p>Network and form teams with fellow participants</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">12:00 PM</div>
                        <div class="schedule-details">
                            <h3>Hacking Begins!</h3>
                            <p>Start building your innovative solutions</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">1:00 PM</div>
                        <div class="schedule-details">
                            <h3>Lunch Break</h3>
                            <p>Fuel up with delicious food and networking</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">6:00 PM</div>
                        <div class="schedule-details">
                            <h3>Workshop: AI/ML Basics</h3>
                            <p>Learn from experts about machine learning implementation</p>
                        </div>
                    </div>
                </div>

                <div id="day2-schedule" class="schedule-day hidden">
                    <div class="schedule-item">
                        <div class="schedule-time">9:00 AM</div>
                        <div class="schedule-details">
                            <h3>Morning Check-in</h3>
                            <p>Coffee, breakfast, and progress updates</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">10:00 AM</div>
                        <div class="schedule-details">
                            <h3>Mentor Sessions</h3>
                            <p>Get guidance from industry professionals</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">2:00 PM</div>
                        <div class="schedule-details">
                            <h3>Final Submissions</h3>
                            <p>Submit your projects and prepare presentations</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">3:00 PM</div>
                        <div class="schedule-details">
                            <h3>Project Demos</h3>
                            <p>Present your solutions to judges and peers</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">5:00 PM</div>
                        <div class="schedule-details">
                            <h3>Awards Ceremony</h3>
                            <p>Celebrate winners and recognize outstanding projects</p>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">6:00 PM</div>
                        <div class="schedule-details">
                            <h3>Networking Reception</h3>
                            <p>Connect with participants, mentors, and sponsors</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Prizes Section -->
    <section id="prizes" class="section prizes">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Amazing Prizes Await</h2>
                <p class="section-subtitle">Over $50,000 in prizes and opportunities for the most innovative solutions</p>
            </div>

            <div class="prizes-grid">
                <div class="prize-card">
                    <div class="prize-icon">🏆</div>
                    <h3 class="prize-place">1st Place</h3>
                    <div class="prize-amount">$15,000</div>
                    <ul class="prize-benefits">
                        <li>Cash Prize</li>
                        <li>Mentorship Program</li>
                        <li>VC Pitch Opportunity</li>
                        <li>Media Coverage</li>
                    </ul>
                </div>
                <div class="prize-card">
                    <div class="prize-icon">🥈</div>
                    <h3 class="prize-place">2nd Place</h3>
                    <div class="prize-amount">$10,000</div>
                    <ul class="prize-benefits">
                        <li>Cash Prize</li>
                        <li>Incubator Program</li>
                        <li>Industry Connections</li>
                        <li>Tech Credits</li>
                    </ul>
                </div>
                <div class="prize-card">
                    <div class="prize-icon">🥉</div>
                    <h3 class="prize-place">3rd Place</h3>
                    <div class="prize-amount">$5,000</div>
                    <ul class="prize-benefits">
                        <li>Cash Prize</li>
                        <li>Mentorship</li>
                        <li>Platform Credits</li>
                        <li>Recognition</li>
                    </ul>
                </div>
            </div>

            <div style="text-align: center; margin-top: 3rem;">
                <p style="font-size: 1.125rem; color: #6B7280; margin-bottom: 2rem;">
                    Plus special category prizes for Best UI/UX, Most Innovative Use of AI, and Social Impact Award!
                </p>
                <button class="cta-button" style="font-size: 1.125rem; padding: 1rem 2rem;">View All Prizes</button>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="section faq">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Everything you need to know about InnovateFest 2025</p>
            </div>

            <div class="faq-container">
                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFaq(this)">
                        What is a hackathon?
                        <span class="faq-icon">▼</span>
                    </button>
                    <div class="faq-answer">
                        A hackathon is a collaborative event where developers, designers, and innovators come together to create innovative solutions to real-world problems within a limited timeframe.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFaq(this)">
                        Who can participate?
                        <span class="faq-icon">▼</span>
                    </button>
                    <div class="faq-answer">
                        Anyone with a passion for technology and innovation! Whether you're a student, professional developer, designer, or entrepreneur, all skill levels are welcome.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFaq(this)">
                        How are teams formed?
                        <span class="faq-icon">▼</span>
                    </button>
                    <div class="faq-answer">
                        You can register as a team (2-4 members) or as an individual. We'll have a team formation session where solo participants can connect with others.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFaq(this)">
                        What should I bring?
                        <span class="faq-icon">▼</span>
                    </button>
                    <div class="faq-answer">
                        Bring your laptop, charger, any development tools you prefer, and lots of enthusiasm! We'll provide food, drinks, and workspace.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" onclick="toggleFaq(this)">
                        What can I win?
                        <span class="faq-icon">▼</span>
                    </button>
                    <div class="faq-answer">
                        Amazing prizes including cash rewards, latest tech gadgets, internship opportunities, and the chance to pitch to VCs!
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section cta-section">
        <div class="container">
            <h2 class="section-title">Ready to Innovate?</h2>
            <p class="section-subtitle">Join hundreds of developers, designers, and entrepreneurs in creating solutions that matter. Registration is free and closes March 10th!</p>
            
            <div class="hero-buttons" style="margin-top: 3rem;">
                <button class="btn-primary">Register Now - Free!</button>
                <button class="btn-secondary">Become a Sponsor</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <span style="font-size: 1.5rem;">⚡</span>
                    <h3>InnovateFest 2025</h3>
                </div>
                <p>The premier hackathon bringing together the brightest minds to solve tomorrow's challenges today.</p>
                <div class="social-links">
                    <a href="#">🐦</a>
                    <a href="#">💻</a>
                    <a href="#">💼</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <p><a href="#about">About</a></p>
                <p><a href="#schedule">Schedule</a></p>
                <p><a href="#prizes">Prizes</a></p>
                <p><a href="#faq">FAQ</a></p>
            </div>
            
            <div class="footer-section">
                <h3>Contact</h3>
                <p>📧 info@innovatefest.com</p>
                <p>📍 Tech Hub Center, Innovation District</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>© 2025 InnovateFest. All rights reserved. Built with passion for innovation.</p>
        </div>
    </footer>

    <script>
        // Countdown Timer
        function updateCountdown() {
            const targetDate = new Date('2025-03-15T09:00:00').getTime();
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById('days').textContent = days.toString().padStart(2, '0');
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            }
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown(); // Initial call

        // Smooth scrolling
        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Schedule tabs
        function showSchedule(day) {
            // Hide all schedule days
            document.querySelectorAll('.schedule-day').forEach(schedule => {
                schedule.classList.add('hidden');
            });
            
            // Show selected day
            document.getElementById(day + '-schedule').classList.remove('hidden');
            
            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // FAQ toggle
        function toggleFaq(button) {
            const answer = button.nextElementSibling;
            const icon = button.querySelector('.faq-icon');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-answer').forEach(faq => {
                if (faq !== answer) {
                    faq.classList.remove('active');
                }
            });
            
            document.querySelectorAll('.faq-icon').forEach(faqIcon => {
                if (faqIcon !== icon) {
                    faqIcon.classList.remove('active');
                }
            });
            
            // Toggle current FAQ
            answer.classList.toggle('active');
            icon.classList.toggle('active');
        }

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
            }
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observe all sections for animations
        document.querySelectorAll('.section').forEach(section => {
            observer.observe(section);
        });

        // Mobile menu toggle (basic implementation)
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            const navLinks = document.querySelector('.nav-links');
            navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>