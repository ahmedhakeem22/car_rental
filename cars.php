<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require 'config/db.php';

// Fetch cars from database
$cars = $conn->query("SELECT * FROM cars")->fetchAll();

// Handle image upload if this is an admin form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_car'])) {
    $target_dir = "assets/images/cars/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image = 'default-car.jpg'; // Default image
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is an actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            // Generate unique filename
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $new_filename;
            }
        }
    }
    
    // Insert car with image filename into database
    $stmt = $conn->prepare("INSERT INTO cars (brand, model, price_per_day, quantity, description, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['brand'],
        $_POST['model'],
        $_POST['price'],
        $_POST['quantity'],
        $_POST['description'],
        $image
    ]);
    
    header("Location: cars.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars - Car Rental</title>
    <style>
        /* Modern styling matching your screenshot */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        
        header {
            margin-bottom: 30px;
        }
        
        nav {
            display: flex;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 30px;
        }
        
        nav a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 18px;
        }
        
        nav a:hover {
            color: #0066cc;
        }
        
        h1 {
            color: #222;
            margin-bottom: 30px;
        }
        
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .car-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .car-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .car-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
            background-color: #f5f5f5; /* Fallback if image doesn't load */
        }
        
        .car-model {
            font-size: 20px;
            margin: 0 0 10px 0;
            color: #222;
        }
        
        .car-price {
            font-size: 18px;
            color: #0066cc;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .car-stock {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .rent-btn {
            background: #0066cc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background 0.3s;
        }
        
        .rent-btn:hover {
            background: #0052a3;
        }
        
        .rent-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
        
        .out-of-stock {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            z-index: 2;
        }
        
        .low-stock {
            color: #e67e22;
            font-weight: bold;
            font-size: 14px;
        }
        
        .car-card.out-of-stock-card {
            opacity: 0.7;
        }
        
        .car-card.out-of-stock-card img {
            filter: grayscale(80%);
        }
        
        /* Admin form styles */
        .admin-form {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input, 
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .submit-btn {
            background: #0066cc;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <a href="cars.php">Cars</a>
        </nav>
        <h1>Available Cars</h1>
    </header>

    <main>
        <?php if (isset($_SESSION['admin_logged_in'])): ?>
        <div class="admin-form">
            <h2>Add New Car</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_car" value="1">
                
                <div class="form-group">
                    <label for="brand">Brand</label>
                    <input type="text" id="brand" name="brand" required>
                </div>
                
                <div class="form-group">
                    <label for="model">Model</label>
                    <input type="text" id="model" name="model" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Price Per Day ($)</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required>
                </div>
                
                <div class="form-group">
                    <label for="image">Car Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Add Car</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="cars-grid">
            <?php foreach ($cars as $car): 
                $isAvailable = $car['quantity'] > 0;
                $isLowStock = $car['quantity'] > 0 && $car['quantity'] <= 3;
                $imagePath = 'assets/images/cars/' . ($car['image'] ?? 'default-car.jpg');
            ?>
                <div class="car-card <?= !$isAvailable ? 'out-of-stock-card' : '' ?>">
                    <img src="<?= file_exists($imagePath) ? $imagePath : 'assets/images/cars/default-car.jpg' ?>" 
                         alt="<?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?>" 
                         class="car-image"
                         onerror="this.src='assets/images/cars/default-car.jpg'">
                    
                    <h2 class="car-model"><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></h2>
                    <p class="car-price">$<?= number_format($car['price_per_day'], 2) ?>/day</p>
                    <p class="car-stock">Available: <?= $car['quantity'] ?></p>
                    
                    <?php if ($isLowStock): ?>
                        <p class="low-stock">Only <?= $car['quantity'] ?> left!</p>
                    <?php endif; ?>
                    
                    <?php if (!$isAvailable): ?>
                        <div class="out-of-stock">OUT OF STOCK</div>
                    <?php endif; ?>
                    
                    <button class="rent-btn" <?= !$isAvailable ? 'disabled' : '' ?>>
                        Rent Now
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        // Image fallback if src fails to load
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.car-image');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    this.src = 'assets/images/cars/default-car.jpg';
                });
            });
        });
    </script>
</body>
</html>