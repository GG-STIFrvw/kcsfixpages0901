<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);
$username = $isLoggedIn ? $_SESSION['user']['full_name'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KCS Auto Repair Shop - Professional Auto Repair Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .hero-bg {
            position: relative;
            overflow: hidden; /* Ensures pseudo-element doesn't overflow */
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://i.ibb.co/LdmTPBCL/cover.jpg');
            background-size: cover;
            background-position: center;
            filter: grayscale(100%);
            opacity: 0.85; /* opacity */
            z-index: -1; /* Place behind the content */
        }
        .text-primary { color: #d63031; }
        .bg-primary { background-color: #d63031; }
        .border-primary { border-color: #d63031; }
        .hover-lift { transition: all 0.3s ease; }
        .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
        .fade-in { opacity: 0; transform: translateY(30px); transition: all 0.6s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }
        .car-hotspot { position: absolute; width: 40px; height: 40px; background: #d63031; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; cursor: pointer; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
        .accordion-content { display: none; transition: all 0.3s ease; }
        .accordion-content.active { display: block; }
        .form-input { transition: border-color 0.3s ease; }
        .form-input:focus { border-color: #d63031; box-shadow: 0 0 0 3px rgba(214, 48, 49, 0.1); }
        .form-error { border-color: #e74c3c !important; }
        .loader { border: 3px solid #f3f3f3; border-top: 3px solid #d63031; border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .blend-multiply { mix-blend-mode: multiply; }
    </style>
</head>
<body class="bg-white text-gray-800">
    <!-- Header -->
    <header class="fixed top-0 w-full bg-white shadow-lg z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-gray-800">
                <span class="text-primary">KCS</span> Auto Repair Shop
            </div>
            <div class="hidden md:flex space-x-8">
                <a href="#about" class="hover:text-primary transition-colors">About Us</a>
                <a href="#services" class="hover:text-primary transition-colors">Services</a>
                <a href="#faq" class="hover:text-primary transition-colors">FAQ</a>
                <a href="#contact" class="hover:text-primary transition-colors">Contact Us</a>
           </div>

         
    <!-- edited by lans -->
          <?php if ($isLoggedIn): ?>
    <?php
    // Check for unread notifications
    $unreadNotificationCount = 0;
    if (isset($_SESSION['user']['id'])) {
        require_once 'config.php'; // Ensure config is loaded for $pdo
        $userId = $_SESSION['user']['id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'unread'");
        $stmt->execute([$userId]);
        $unreadNotificationCount = $stmt->fetchColumn();
    }
    ?>
    <div class="flex items-center space-x-4 relative"> <!-- Removed 'group' from here -->
        <span class="text-gray-800 font-semibold">Hi, <?php echo htmlspecialchars($username); ?> </span>

        <!-- Book Button (always visible) -->
        <a href="customer_booking.php" class="bg-primary text-white px-6 py-2 rounded-full hover-lift">Book Appointment</a>

        <!-- Settings Dropdown -->
        <div class="relative group"> <!-- Added 'group' here -->
            <button class="flex items-center bg-gray-200 text-gray-800 px-4 py-2 rounded-full hover-lift">
                <span class="mr-1">Settings</span>
                <i class="fas fa-caret-down"></i>
            </button>

            <!-- Dropdown content -->
            <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all z-50">
            <a href="customer_appointments.php" class="block px-4 py-2 text-gray-800 hover:bg-red-100">Appointments</a>
            <a href="customer_archive.php" class="block px-4 py-2 text-gray-800 hover:bg-red-100">Archive</a>
            <a href="customer_notifications.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                Notifications
                <?php if ($unreadNotificationCount > 0): ?>
                    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-left: 5px;"></span>
                <?php endif; ?>
            </a>
            <a href="customer_billing.php" class="block px-4 py-2 text-gray-800 hover:bg-red-100">Payments</a>
            <a href="customer_profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profile Settings</a>
            <a href="customer_view_quote.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Quotations</a>
            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-100">Logout</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <a href="login.php" class="bg-primary text-white px-6 py-2 rounded-full hover-lift flex items-center justify-center">Login</a>
<?php endif; ?>

        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-bg text-white pt-24 pb-16">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 fade-in"><br>Visit Us For All Your Car Needs</h1>
            <p class="text-xl mb-8 fade-in">Expert mechanics, quality parts, and reliable service you can trust</p>
            <div class="space-x-4 fade-in">
                <button onclick="scrollToSection('contact')" class="bg-primary text-white px-8 py-3 rounded-full hover-lift font-semibold">Any Questions?</button>
                <button onclick="scrollToSection('services')" class="border-2 border-white text-white px-8 py-3 rounded-full hover:bg-white hover:text-gray-800 transition-all">Our Services</button>
            </div>
        </div>
    </section>

    <!-- About Us -->
    <section id="about" class="py-16">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">About KCS Auto Repair Shop</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">KCS Auto Repair Shop was established February 2010 and sits on a 800 sq mtr area located at 
                    #56-A Kanlaon Street Brgy Sta Teresita, Quezon City with its perimeter well secured by concrete walls.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center fade-in">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Professional Management</h3>
                    <p class="text-gray-600">Ensures efficient operations and exceptional service</p>
                </div>
                <div class="text-center fade-in">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-tools text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Expert Mechanics</h3>
                    <p class="text-gray-600">Certified professionals with years of experience</p>
                </div>
                <div class="text-center fade-in">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Fast Service</h3>
                    <p class="text-gray-600">Quick turnaround without compromising quality</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Location Info -->
    <section id="location" class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Visit Our Shop</h2>
                <p class="text-lg text-gray-600">Conveniently located in the heart of Quezon City</p>
            </div>
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="fade-in">
                    <div class="bg-gray-100 h-80 rounded-lg flex items-center justify-center mb-6 relative pt-[66.66%] overflow-hidden">
                        <div class="text-center absolute inset-0">
                            <iframe
                            src="https://tinyurl.com/kcsmap"
                            width="100%" height="100%" style="border-radius:15px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
                <div class="fade-in">
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <i class="fas fa-map-marker-alt text-primary text-xl mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-lg">Address</h3>
                                <p class="text-gray-600">#56-A Kanlaon St., Quezon City</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <i class="fas fa-phone text-primary text-xl mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-lg">Phone</h3>
                                <p class="text-gray-600">(02) 8123-4567</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <i class="fas fa-clock text-primary text-xl mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-lg">Operating Hours</h3>
                                <p class="text-gray-600">Mon-Sat: 8:00 AM - 6:00 PM<br>Sunday: Closed</p>
                            </div>
                        </div>
                        <div class="flex space-x-4 pt-4">
                            <a href="https://www.facebook.com/kcsautorepairshop" class="text-primary hover:text-red-700 text-2xl"><i class="fab fa-facebook"></i></a>
                            <a href="https://www.instagram.com/" class="text-primary hover:text-red-700 text-2xl"><i class="fab fa-instagram"></i></a>
                            <a href="https://x.com/" class="text-primary hover:text-red-700 text-2xl"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section id="services" class="py-16"> <!-- reduced from py-16 -->
        <div class="container mx-auto px-6">
            <div class="text-center mb-4 fade-in"> <!-- reduced from mb-12 -->
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Our Services</h2>
                <p class="text-lg text-gray-600">Comprehensive automotive repair and maintenance</p>
            </div>
            
            <!-- Interactive Car -->
            <div class="relative max-w-4xl mx-auto mb-4 fade-in"> <!-- reduced from mb-6 -->
                <div class="rounded-lg p-5 relative">
                    <!-- Car Image -->
                    <video loop autoplay muted playsinline class="w-full h-auto object-contain blend-multiply"><source src="https://tinyurl.com/cloudinary-carloop" type="video/mp4"></video>
                    
                    <!-- Hotspots -->
                    <div class="car-hotspot" style="top: 40%; left: 15%;" onclick="showService('engine')">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="car-hotspot" style="top: 60%; left: 85%;" onclick="showService('tires')">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="car-hotspot" style="top: 24%; left: 48%;" onclick="showService('ac')">
                        <i class="fas fa-snowflake"></i>
                    </div>
                    <div class="car-hotspot" style="top: 35%; left: 80%;" onclick="showService('electrical')">
                        <i class="fas fa-bolt"></i>
                    </div>
                </div>
            </div>

            <!-- Service Cards -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="service-card bg-white rounded-lg shadow-lg p-6 hover-lift fade-in" data-service="engine">
                    <div class="text-primary text-3xl mb-4"><i class="fas fa-cog"></i></div>
                    <h3 class="text-xl font-semibold mb-3">Engine Services</h3>
                    <ul class="text-gray-600 space-y-1">
                        <li>• Engine Diagnostics</li>
                        <li>• Oil Changes</li>
                        <li>• Belt Replacement</li>
                        <li>• Tune-ups</li>
                    </ul>
                </div>
                <div class="service-card bg-white rounded-lg shadow-lg p-6 hover-lift fade-in" data-service="tires">
                    <div class="text-primary text-3xl mb-4"><i class="fas fa-circle"></i></div>
                    <h3 class="text-xl font-semibold mb-3">Tire Services</h3>
                    <ul class="text-gray-600 space-y-1">
                        <li>• Tire Installation</li>
                        <li>• Wheel Alignment</li>
                        <li>• Balancing</li>
                        <li>• Wheel Bearings</li>hp -Im
                    </ul>
                </div>
                <div class="service-card bg-white rounded-lg shadow-lg p-6 hover-lift fade-in" data-service="ac">
                    <div class="text-primary text-3xl mb-4"><i class="fas fa-snowflake"></i></div>
                    <h3 class="text-xl font-semibold mb-3">AC Services</h3>
                    <ul class="text-gray-600 space-y-1">
                        <li>• AC Repair</li>
                        <li>• Refrigerant Recharge</li>
                        <li>• Filter Replacement</li>
                        <li>• System Diagnostics</li>
                    </ul>
                </div>
                <div class="service-card bg-white rounded-lg shadow-lg p-6 hover-lift fade-in" data-service="electrical">
                    <div class="text-primary text-3xl mb-4"><i class="fas fa-bolt"></i></div>
                    <h3 class="text-xl font-semibold mb-3">Electrical</h3>
                    <ul class="text-gray-600 space-y-1">
                        <li>• Battery Testing</li>
                        <li>• Charging System</li>
                        <li>• Wiring Issues</li>
                        <li>• Starter Problems</li>
                    </ul>
                </div>
            </div>
            <div class="text-center mt-12 fade-in">
                <p class="text-gray-600 mb-4">Need a specific service?</p>
                <a href="services_page.php" class="bg-primary text-white px-8 py-3 rounded-full hover-lift font-semibold inline-block">Other Services</a>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>
                <p class="text-lg text-gray-600">Get answers to common questions about our services</p>
            </div>
            <div class="max-w-3xl mx-auto space-y-4">
                <div class="faq-item fade-in">
                    <button class="w-full text-left p-6 flex justify-between items-center text-white bg-gray-800 hover:bg-red-600 transition-colors rounded-lg shadow-md" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-lg">What are your operating hours?</span>
                        <i class="fas fa-plus text-white transform transition-transform"></i>
                    </button>
                    <div class="accordion-content bg-white rounded-lg shadow-md mt-2 px-6 py-6">
                        <p class="text-gray-800">Our shop is open during the following hours: Monday to Saturday from 8:00 AM to 6:00 PM. Closed on Sundays.</p>
                    </div>
                </div>
                <div class="faq-item fade-in">
                    <button class="w-full text-left p-6 flex justify-between items-center text-white bg-gray-800 hover:bg-red-600 transition-colors rounded-lg shadow-md" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-lg">Where is the shop located?</span>
                        <i class="fas fa-plus text-white transform transition-transform"></i>
                    </button>
                    <div class="accordion-content bg-white rounded-lg shadow-md mt-2 px-6 py-6">
                        <p class="text-gray-800">We are located at 56A Kanlaon St, Quezon City, 1114 Metro Manila. You can search us on Google Maps.</p>
                    </div>
                </div>
                <div class="faq-item fade-in">
                    <button class="w-full text-left p-6 flex justify-between items-center text-white bg-gray-800 hover:bg-red-600 transition-colors rounded-lg shadow-md" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-lg">When is the best time to visit?</span>
                        <i class="fas fa-plus text-white transform transition-transform"></i>
                    </button>
                    <div class="accordion-content bg-white rounded-lg shadow-md mt-2 px-6 py-6">
                        <p class="text-gray-800">To avoid wait times or scheduling conflicts, the best time to come in is when you have a confirmed appointment. You can easily book one through our website.</p>
                    </div>
                </div>
                <div class="faq-item fade-in">
                    <button class="w-full text-left p-6 flex justify-between items-center text-white bg-gray-800 hover:bg-red-600 transition-colors rounded-lg shadow-md" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-lg">Why choose KCS Auto Repair Shop?</span>
                        <i class="fas fa-plus text-white transform transition-transform"></i>
                    </button>
                    <div class="accordion-content bg-white rounded-lg shadow-md mt-2 px-6 py-6">
                        <p class="text-gray-800">Choosing KCS means you'll receive reliable and professional automotive services. Our skilled mechanics are trained in various areas, ensuring your vehicle is in good hands.</p>
                    </div>
                </div>
                <div class="faq-item fade-in">
                    <button class="w-full text-left p-6 flex justify-between items-center text-white bg-gray-800 hover:bg-red-600 transition-colors rounded-lg shadow-md" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-lg">How do I book an appointment?</span>
                        <i class="fas fa-plus text-white transform transition-transform"></i>
                    </button>
                    <div class="accordion-content bg-white rounded-lg shadow-md mt-2 px-6 py-6">
                        <p class="text-gray-800">You can book an appointment by creating an account on our website. If you already have one, simply log in to access the <a href='customer_booking.php'><b>booking page</b></a>.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-8 fade-in">
                <p class="text-gray-600 mb-4">Didn't find your answer?</p>
                <button onclick="window.chatbase.open()" class="text-primary hover:text-red-700 font-semibold">Ask us a question →</button>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="py-16">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Contact Us</h2>
                <p class="text-lg text-gray-600">Get in touch for appointments or questions</p>
            </div>
            <div class="grid md:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div class="fade-in">
                    <form id="contactForm" class="space-y-6">
                        <div>
                            <input type="text" id="name" name="name" placeholder="Your Name" class="form-input w-full p-4 border border-gray-300 rounded-lg focus:outline-none" required>
                            <div class="error-message text-red-500 text-sm mt-1 hidden">Please enter your name</div>
                        </div>
                        <div>
                            <input type="email" id="email" name="email" placeholder="Your Email" class="form-input w-full p-4 border border-gray-300 rounded-lg focus:outline-none" required>
                            <div class="error-message text-red-500 text-sm mt-1 hidden">Please enter a valid email</div>
                        </div>
                        <div>
                            <input type="tel" id="phone" name="phone" placeholder="Phone Number" class="form-input w-full p-4 border border-gray-300 rounded-lg focus:outline-none" required>
                            <div class="error-message text-red-500 text-sm mt-1 hidden">Please enter a valid phone number</div>
                        </div>
                        <div>
                            <textarea id="message" name="message" placeholder="Your Message" rows="5" class="form-input w-full p-4 border border-gray-300 rounded-lg focus:outline-none" required></textarea>
                            <div class="error-message text-red-500 text-sm mt-1 hidden">Please enter your message</div>
                        </div>
                        <button type="submit" class="w-full bg-primary text-white py-4 rounded-lg hover-lift font-semibold flex items-center justify-center">
                            <span class="submit-text">Send Message</span>
                            <div class="loader hidden ml-2"></div>
                        </button>
                        <div class="success-message hidden text-green-600 text-center font-semibold">
                            ✅ Message sent successfully! We'll get back to you soon.
                        </div>
                    </form>
                </div>
                
                <!-- Contact Info -->
                <div class="fade-in">
                    <div class="space-y-8">
                        <div>
                            <h3 class="text-2xl font-semibold mb-4">Customer Support</h3>
                            <p class="text-gray-600 mb-6">Our friendly team is here to help with any questions or concerns.</p>
                            <div class="space-y-4">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-phone text-primary"></i>
                                    <span>285594396</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <span>kcsautorepair09@gmail.com</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-clock text-primary"></i>
                                    <span>Mon-Sat: 8AM-6PM</span>
                                </div>
                            </div>
                        </div>
                        <button onclick="scrollToSection('location')" class="bg-gray-800 text-white px-8 py-3 rounded-lg hover-lift flex items-center">
                            Visit Our Shop
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4"><span class="text-primary">KCS</span> Auto Repair Shop</h3>
                    <p class="text-gray-300 mb-4">Professional automotive repair and maintenance services with over 15 years of experience. Your trusted partner for reliable vehicle care.</p>
                    <div class="flex space-x-4">
                        <a href="https://www.facebook.com/kcsautorepairshop" class="text-primary hover:text-red-400"><i class="fab fa-facebook text-xl"></i></a>
                        <a href="https://www.instagram.com/" class="text-primary hover:text-red-400"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="https://x.com/" class="text-primary hover:text-red-400"><i class="fab fa-twitter text-xl"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#about" class="hover:text-primary">About Us</a></li>
                        <li><a href="#services" class="hover:text-primary">Services</a></li>
                        <li><a href="#faq" class="hover:text-primary">FAQ</a></li>
                        <li><a href="#contact" class="hover:text-primary">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Services</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li>Engine Repair</li>
                        <li>Tire Services</li>
                        <li>AC Repair</li>
                        <li>Electrical Systems</li>
                        <li>Oil Changes</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 KCS Auto Repair Shop. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling
        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({ behavior: 'smooth' });
        }

        // Fade in animation on scroll
        function handleScrollAnimations() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('visible');
                }
            });
        }

        // FAQ toggle
        function toggleFAQ(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');
            
            // Close all other FAQs
            document.querySelectorAll('.accordion-content').forEach(item => {
                if (item !== content) {
                    item.classList.remove('active');
                    item.previousElementSibling.querySelector('i').classList.remove('fa-minus');
                    item.previousElementSibling.querySelector('i').classList.add('fa-plus');
                }
            });
            
            // Toggle current FAQ
            content.classList.toggle('active');
            icon.classList.toggle('fa-plus');
            icon.classList.toggle('fa-minus');
        }

        // Service highlighting
        function showService(serviceType) {
            // Remove highlight from all cards
            document.querySelectorAll('.service-card').forEach(card => {
                card.classList.remove('ring-4', 'ring-red-200');
            });
            
            // Highlight matching service card
            const serviceCard = document.querySelector(`[data-service="${serviceType}"]`);
            if (serviceCard) {
                serviceCard.classList.add('ring-4', 'ring-red-200');
                serviceCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Remove highlight after 3 seconds
                setTimeout(() => {
                    serviceCard.classList.remove('ring-4', 'ring-red-200');
                }, 3000);
            }
        }

        // Initialize animations on page load
        window.addEventListener('load', handleScrollAnimations);
        window.addEventListener('scroll', handleScrollAnimations);
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // All FAQs start closed by default
        });
    </script>
    <script src="contact_form.js"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;
    if(b){var d=b.createElement('script');
    d.innerHTML="window.__CF$cv$params={r:'95c090f4857b045b',t:'MTc1MTk4ODAzMy4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";
    b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');
    a.height=1;a.width=1;
    a.style.position='absolute';
    a.style.top=0;
    a.style.left=0;
    a.style.border='none';
    a.style.visibility='hidden';
    document.body.appendChild(a);
    if('loading'!==document.readyState)c();
    else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);
    else{var e=document.onreadystatechange||function(){};
    document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();
    </script>

        <!-- chatbot -->

<script>
  window.chatbaseConfig = {
    chatbotId: "c17ifi381ko1ox5xwpo8gjkw70v7vq75"
  };
</script>
<script>
(function(){
  if (!window.chatbase || window.chatbase("getState") !== "initialized") {
    window.chatbase = (...args) => {
      if (!window.chatbase.q) {
        window.chatbase.q = [];
      }
      window.chatbase.q.push(args);
    };
    window.chatbase = new Proxy(window.chatbase, {
      get(target, prop) {
        if (prop === "q") return target.q;
        return (...args) => target(prop, ...args);
      }
    });
  }
  const onLoad = function() {
    const script = document.createElement("script");
    script.src = "https://www.chatbase.co/embed.min.js";
    script.id = "OB4v_sMcYe5mBwFSZ5EPH";
    script.domain = "www.chatbase.co";
    document.body.appendChild(script);
  };
  if (document.readyState === "complete") {
    onLoad();
  } else {
    window.addEventListener("load", onLoad);
  }
})();
</script>


</body>
</html>