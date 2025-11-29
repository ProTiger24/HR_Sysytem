<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KormoShathi - Professional Employee Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c5aa0;
            --primary-dark: #1e3d72;
            --primary-light: #4a7bce;
            --secondary: #6c757d;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --gradient: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        /* Navigation */
        .navbar {
            padding: 1rem 0;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .brand-logo {
            background: var(--gradient);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 12px;
            box-shadow: var(--shadow);
        }

        .brand-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            margin: 0 0.8rem;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary) !important;
            background: rgba(44, 90, 160, 0.1);
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* Hero Section */
        .hero-section {
            background: var(--gradient);
            color: white;
            padding: 120px 0 100px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,1000 1000,0 1000,1000"/></svg>');
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            line-height: 1.6;
            font-weight: 300;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 32px;
            font-weight: 600;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: white;
            color: var(--primary) !important;
            border-color: white;
        }

        .btn-primary:hover {
            background: transparent;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255,255,255,0.2);
        }

        .btn-outline-light {
            background: transparent;
            color: white;
            border-color: rgba(255,255,255,0.3);
        }

        .btn-outline-light:hover {
            background: white;
            color: var(--primary) !important;
            transform: translateY(-2px);
            border-color: white;
        }

        .hero-image {
            position: relative;
            z-index: 2;
        }

        .hero-image img {
            width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
            transition: transform 0.5s ease;
        }

        .hero-image img:hover {
            transform: perspective(1000px) rotateY(0) rotateX(0);
        }

        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: #f8fafc;
        }

        .section-title {
            font-size: 2.8rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--primary-dark);
            letter-spacing: -1px;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: var(--secondary);
            margin-bottom: 4rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-card h4 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-dark);
            font-size: 1.4rem;
        }

        .feature-card p {
            color: var(--secondary);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .feature-card .btn {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 10px 25px;
            font-size: 0.9rem;
        }

        .feature-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(44, 90, 160, 0.3);
        }

        /* About Section */
        .about-section {
            padding: 100px 0;
            background: white;
        }

        .about-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .about-content h2 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary-dark);
            letter-spacing: -1px;
        }

        .about-content .lead {
            font-size: 1.3rem;
            color: var(--secondary);
            margin-bottom: 3rem;
            font-weight: 300;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            padding: 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            background: rgba(44, 90, 160, 0.05);
            transform: translateX(10px);
        }

        .feature-item i {
            color: var(--primary);
            margin-right: 1rem;
            font-size: 1.3rem;
            width: 24px;
            text-align: center;
        }

        .feature-item span {
            font-weight: 500;
            color: var(--dark);
        }

        /* Footer */
        .footer {
            background: var(--primary-dark);
            color: white;
            padding: 60px 0 20px;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .footer-text {
            color: rgba(255,255,255,0.8);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            color: white;
            font-size: 1.3rem;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-links a:hover {
            background: var(--primary-light);
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
            color: rgba(255,255,255,0.6);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .hero-buttons .btn {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
            
            .about-content h2 {
                font-size: 2.2rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        /* Stats Counter */
        .stats-section {
            background: var(--gradient);
            color: white;
            padding: 80px 0;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 300;
        }
    </style>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../images/officeLogo.avif" alt="KormoShathi" height="40" class="me-2">
                <span class="brand-text">KormoShathi</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span><i class="fa-solid fa-bars"></i></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-4 ms-2"
                           href="login.php">
                           <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content fade-in-up">
                    <h1 class="hero-title">
                        Streamline Your HR Operations with Professional Excellence
                    </h1>
                    <p class="hero-subtitle">
                        Comprehensive HR management solution that transforms how you manage employees, 
                        track attendance, process payroll, and analyze workforce data with enterprise-grade security.
                    </p>

                    <div class="hero-buttons">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>HR Portal Login
                        </a>
                        <a href="register.php" class="btn btn-outline-light">
                            <i class="fas fa-user-plus me-2"></i>Employee Registration
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 hero-image mt-5 mt-lg-0 fade-in-up">
                    <img src="../images/hero.avif" alt="Professional HR Dashboard Interface" 
                         onerror="this.src='https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                </div>
            </div>
        </div>
    </section>

    <!-- STATS SECTION -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-6 stat-item">
                    <div class="stat-number" data-count="500">500+</div>
                    <div class="stat-label">Companies Trust Us</div>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <div class="stat-number" data-count="50">50K+</div>
                    <div class="stat-label">Employees Managed</div>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <div class="stat-number" data-count="99">99.9%</div>
                    <div class="stat-label">Uptime Reliability</div>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <div class="stat-number" data-count="24">24/7</div>
                    <div class="stat-label">Support Available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">Enterprise-Grade Features</h2>
            <p class="section-subtitle">Comprehensive tools designed for modern HR departments</p>

            <div class="feature-grid">
                <!-- Feature 1 -->
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-users-gear"></i></div>
                    <h4>Employee Lifecycle Management</h4>
                    <p>End-to-end employee management from onboarding to offboarding with complete digital records and automated workflows.</p>
                    <a href="login.php" class="btn">Explore</a>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-fingerprint"></i></div>
                    <h4>Biometric Attendance</h4>
                    <p>Advanced fingerprint and facial recognition technology for secure, accurate attendance tracking with real-time analytics.</p>
                    <a href="login.php" class="btn">Discover</a>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <h4>Automated Payroll</h4>
                    <p>Intelligent payroll processing with tax calculations, compliance management, and seamless integration with accounting systems.</p>
                    <a href="login.php" class="btn">Learn More</a>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <h4>Leave Management</h4>
                    <p>Streamlined leave approval workflows, balance tracking, and policy enforcement with mobile accessibility.</p>
                    <a href="login.php" class="btn">View Details</a>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <h4>Advanced Analytics</h4>
                    <p>Comprehensive HR analytics with customizable dashboards, predictive insights, and automated reporting.</p>
                    <a href="login.php" class="btn">See Reports</a>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <h4>Enterprise Security</h4>
                    <p>Bank-level security with encryption, role-based access control, audit trails, and compliance certifications.</p>
                    <a href="login.php" class="btn">Security Info</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 about-image mb-5 mb-lg-0">
                    <img src="../images/about.png" alt="KormoShathi Platform Overview" 
                         onerror="this.src='https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                </div>

                <div class="col-lg-6 about-content">
                    <h2>Why Enterprises Choose KormoShathi</h2>
                    <p class="lead">Built for scalability, security, and seamless user experience</p>

                    <div class="about-features">
                        <div class="feature-item">
                            <i class="fa-solid fa-lock"></i>
                            <span>Enterprise-Grade Security & Compliance</span>
                        </div>
                        <div class="feature-item">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Cloud-Native Scalable Architecture</span>
                        </div>
                        <div class="feature-item">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            <span>Automated Workflow Optimization</span>
                        </div>
                        <div class="feature-item">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                            <span>Cross-Platform Mobile Accessibility</span>
                        </div>
                        <div class="feature-item">
                            <i class="fa-solid fa-headset"></i>
                            <span>24/7 Dedicated Customer Support</span>
                        </div>
                        <div class="feature-item">
                            <i class="fa-solid fa-rocket"></i>
                            <span>Continuous Innovation & Updates</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="footer-brand">
                        <div class="brand-logo">EM</div>
                        <span class="brand-text">KormoShathi</span>
                    </div>
                    <p class="footer-text">
                        Transforming HR operations with cutting-edge technology and unparalleled 
                        service excellence. Trusted by leading organizations worldwide.
                    </p>
                </div>

                <div class="col-lg-6 d-flex justify-content-lg-end justify-content-start align-items-center">
                    <div class="social-links">
                        <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#"><i class="fa-brands fa-github"></i></a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                © <?php echo date("Y"); ?> KormoShathi HR Solutions. All Rights Reserved. | Enterprise HR Management System
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Counter animation
        function animateCounter() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-count');
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const updateCounter = () => {
                    current += step;
                    if (current < target) {
                        counter.textContent = Math.ceil(current) + '+';
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target + '+';
                    }
                };
                
                updateCounter();
            });
        }

        // Intersection Observer for animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (entry.target.classList.contains('stats-section')) {
                        animateCounter();
                    }
                    entry.target.style.opacity = 1;
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        // Observe elements
        document.querySelectorAll('.fade-in-up').forEach(el => {
            el.style.opacity = 0;
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.8s ease-out';
            observer.observe(el);
        });
    </script>
</body>
</html>