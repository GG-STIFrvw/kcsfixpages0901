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
       <link rel="stylesheet" href="css/service.css">

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
            background-image: url('assets/cover.jpeg');
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
    </style>
    
</head>
<body class="bg-white text-gray-800">
    <!-- Header -->
    <header class="fixed top-0 w-full bg-white shadow-lg z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-gray-800">
                <a href="index.php">
                    <span class="text-primary">KCS</span> Auto Repair Shop
                </a>
            </div>
            <div class="hidden md:flex space-x-8">
                <a href="index.php#about" class="hover:text-primary transition-colors">About Us</a>
                <a href="#services" class="hover:text-primary transition-colors">Services</a>
                <a href="index.php#faq" class="hover:text-primary transition-colors">FAQ</a>
                <a href="index.php#contact" class="hover:text-primary transition-colors">Contact Us</a>
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
    <div class="flex items-center space-x-4">
        <span class="text-gray-800 font-semibold">Hi, <?php echo htmlspecialchars($username); ?> </span>

        <!-- Book Button (always visible) -->
        <a href="book.php" class="bg-primary text-white px-6 py-2 rounded-full hover-lift">Book Appointment</a>

        <!-- Settings Dropdown -->
        <div class="relative group">
            <button class="flex items-center bg-gray-200 text-gray-800 px-4 py-2 rounded-full hover-lift">
                <span class="mr-1">Settings</span>
                <i class="fas fa-caret-down"></i>
            </button>

            <!-- Dropdown content -->
            <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all z-50">
            <a href="my_appointments.php" class="block px-4 py-2 text-gray-800 hover:bg-red-100">Appointments</a>
            <a href="archive.php" class="block px-4 py-2 text-gray-800 hover:bg-red-100">Archive</a>
            <a href="booking_status_display.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                Notifications
                <?php if ($unreadNotificationCount > 0): ?>
                    <span style="display: inline-block; width: 8px; height: 8px; background-color: red; border-radius: 50%; margin-left: 5px;"></span>
                <?php endif; ?>
            </a>
            <a href="Billing_Cust.php" class="block px-4 py-2 text-gray-800 hover:bg-red-100">Payments</a>
            <a href="customer_profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profile Settings</a>
            <a href="view.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Quotations</a>
            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-100">Logout</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <a href="login.php" class="bg-primary text-white px-6 py-2 rounded-full hover-lift flex items-center justify-center">Login</a>
<?php endif; ?>

        </nav>
    </header>
            <br>
            <br>
    <!-- Services -->
    
 <section class="services-offer">
    <div class="container">
      <h2>Auto Repair You Can Trust</h2>
      <p>Our certified mechanics deliver quality repairs with honest advice. We use advanced diagnostics to keep your vehicle running smoothly.</p>
    </div>
  </section>

  <div class="service-container">
    <div class="service-image">
      <img id="service-img" src="" alt="Service Image"/>
    </div>
    <div class="service-content">
      <h2 id="service-title"></h2>
      <video id="service-video" controls></video>
      <p id="service-description"></p>
    </div>
  </div>

  <div class="controls">
    <button onclick="prevService()">Previous</button>
    <button onclick="nextService()">Next</button>
  </div>

 <section class="services-list">
  <h2>Services Offered</h2>
  <ul>
    <li>Airconditioning System</li>
    <li>Battery</li>
    <li>Belts</li>
    <li>Body Repair/Painting Job</li>
    <li>Brake Lines</li>
    <li>Brake Shoes</li>
    <li>Brakes Fluid</li>
    <li>Brakes Pads</li>
    <li>Charging System</li>
    <li>Clutch System</li>
    <li>Cooling System</li>
    <li>CV Joints & Boots</li>
    <li>Differential Oil</li>
    <li>Electrical Supply System</li>
    <li>Engine Change Oil</li>
    <li>Exhaust System</li>
    <li>Filters</li>
    <li>Fuel Injectors</li>
    <li>Fuel Supply System</li>
    <li>Guages and Meters</li>
    <li>Horn</li>
    <li>Hoses</li>
    <li>Ignition System</li>
    <li>Insurance Claim</li>
    <li>Lightning & Signaling System</li>
    <li>Overhaul Engine</li>
    <li>Power Steering Fluid</li>
    <li>Sensors</li>
    <li>Shocks / Struts</li>
    <li>Starting System</li>
    <li>Steering System</li>
    <li>Suspension</li>
    <li>Switches</li>
    <li>Timing/Chain Belt</li>
    <li>Tires</li>
    <li>Transmission</li>
    <li>Transmission Oil</li>
    <li>Tune-up</li>
    <li>Wheel Alignment</li>
    <li>Wheel Bearings</li>
    <li>Wipers & Washer Fluid Level</li>
    <li>Wiring Harnesses</li>
  </ul>
</section>


<section class="reviews-section">
        <div class="section-header">
            <h2 class="section-title" style="color: black;">What Our Customers Say</h2>
            <p class="section-subtitle"style="color: black; ">Don't just take our word for it. Here's what real customers are saying about their experience with us.</p>
        </div>

        <div class="reviews-grid">
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-avatar">JE</div>
                    <div class="reviewer-info">
                        <h3>J A ELA</h3>
                        <div class="reviewer-role">Customer</div>
                    </div>
                </div>
                <div class="stars">
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                </div>
                                <p class="review-text">Excellent Service
                I would recommend this to other client
                They are super friendly and approachable
                They will help fix your car.</p>
              
            </div>

            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-avatar">PR</div>
                    <div class="reviewer-info">
                        <h3>P Rosete</h3>
                        <div class="reviewer-role">Customer</div>
                    </div>
                </div>
                <div class="stars">
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                </div>
                <p class="review-text">Great car and truck service. Mechanics are friendly and helpful.</p>
             
            </div>

            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-avatar">JS</div>
                    <div class="reviewer-info">
                        <h3>J Y Soon</h3>
                        <div class="reviewer-role">Customer</div>
                    </div>
                </div>
                <div class="stars">
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                </div>
                <p class="review-text"> I register my O.R./C.R. here every year. Fast service, and easy to talk to. Arlene is the one we talk to every time we register. They are very kind and quick to respond.</p>
             
            </div>
   
    </section>


</section>
                 <h1 class="text-brands">Car brands we repair and many more!</h1>
      <section class="brands">
    <img src="https://1000logos.net/wp-content/uploads/2021/04/Toyota-logo.png" alt="Toyota" />
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRpR6ZZq-yYJza5nLNnVy5MabIL_chcOH0V9g&s" alt="Honda" />
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQktU3CA6v637GwRdzn5hrX0vxNDXeREToNGjeA4WEeI90wcMXIF2gDAXJEMNjcoFYlYTM&usqp=CAU" alt="Ford" />
    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Mitsubishi_motors_new_logo.svg/960px-Mitsubishi_motors_new_logo.svg.png" alt="BMW" />
  </section>


  <script>
    class Service {
      constructor(title, image, video, description) {
        this.title = title;
        this.image = image;
        this.video = video;
        this.description = description;
      }
    }

    const services = [
      new Service(
        "Keep Your Vehicle Running at Its Best",
        "https://tinyurl.com/car-gears",
        "https://tinyurl.com/gear-vid",
        "At KCS Auto Repair, we identify engine issues with accuracy and care, using advanced diagnostics to ensure reliable performance, smoother rides, and the confidence that your vehicle operates at its best every single time."
      ),
      new Service(
        "Expert Tire Services You Can Rely On",
        "https://tinyurl.com/car-wheel",
        "https://tinyurl.com/wheel-vid",
        "Backed by skilled technicians, KCS Auto Repair ensures your tires are carefully inspected, expertly repaired, and properly maintained for safety, reliability, and consistent performance whenever you're on the road."
      ),
      new Service(
        "Comprehensive Engine Diagnostic Care",
        "https://tinyurl.com/car-defog",
        "https://tinyurl.com/engine-vid",
        "With over a decade of proven expertise, KCS Auto Repair delivers high-quality engine repairs through trusted workmanship, skilled technicians, and reliable service that ensures your vehicle performs at its peak every day."
      )
    ];


    let currentIndex = 0;

    function renderService(index) {
      const elements = ['service-title', 'service-img', 'service-video', 'service-description'];
      
      // adds fade transition
      elements.forEach(id => {
        document.getElementById(id).classList.add('fade-transition');
      });

      setTimeout(() => {
        document.getElementById("service-title").innerText = services[index].title;
        document.getElementById("service-img").src = services[index].image;
        document.getElementById("service-video").src = services[index].video;
        document.getElementById("service-description").innerText = services[index].description;

        // Remove fade transition
        elements.forEach(id => {
          document.getElementById(id).classList.remove('fade-transition');
        });
      }, 250);
    }

    function nextService() {
      currentIndex = (currentIndex + 1) % services.length;
      renderService(currentIndex);
    }

    function prevService() {
      currentIndex = (currentIndex - 1 + services.length) % services.length;
      renderService(currentIndex);
    }

    // Initialize
    renderService(currentIndex);
  </script>

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
                        <li><a href="index.php#about" class="hover:text-primary">About Us</a></li>
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



<script>
    const carousel = document.getElementById('service-carousel');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const totalSlides = carousel.children.length;
    let index = 0;

    function updateCarousel() {
        carousel.style.transform = `translateX(${-index * 100}%)`;
    }

    nextBtn.addEventListener('click', () => {
        index = (index + 1) % totalSlides;
        updateCarousel();
    });

    prevBtn.addEventListener('click', () => {
        index = (index - 1 + totalSlides) % totalSlides;
        updateCarousel();
    });
</script>

</body>
</html>