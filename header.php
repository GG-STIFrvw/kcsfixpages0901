<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KCS Auto Repair Shop</title>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#d63031',
                    }
                },
                fontFamily: {
                    sans: ['Poppins', 'sans-serif'],
                },
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body class="bg-white text-gray-800">

    <!-- Header -->
    <header class="fixed top-0 w-full bg-white shadow-lg z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-gray-800">
                <span class="text-primary">KCS</span> Auto Repair Shop
            </div>
            <a href="index.php" class="bg-primary text-white px-6 py-2 rounded-full hover-lift flex items-center justify-center">Back</a>
        </nav>
    </header>
