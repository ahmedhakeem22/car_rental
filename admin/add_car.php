<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';

$current_script_for_check = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['admin_id']) && $current_script_for_check !== 'admin_login.php' && $current_script_for_check !== 'admin_setup.php') {
    $_SESSION['admin_error'] = "Please log in to access this page.";
    header("Location: " . APP_URL . "admin/admin_login.php");
    exit();
}

$page_title = "Add New Car";

$brand = '';
$model = '';
$year = date("Y");
$price_per_day = '';
$quantity = 1;
$description = '';
$is_available = 1;
$image_name = '';
$pickup_location_name_form = '';
$pickup_latitude_form = '';
$pickup_longitude_form = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1900, "max_range" => date("Y") + 2]]);
    $price_per_day = filter_input(INPUT_POST, 'price_per_day', FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0.01]]);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    $description = trim($_POST['description']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    $pickup_location_name_form = trim($_POST['pickup_location_name']);
    $pickup_latitude_form = trim($_POST['pickup_latitude']);
    $pickup_longitude_form = trim($_POST['pickup_longitude']);

    $pickup_latitude_to_db = !empty($pickup_latitude_form) ? filter_var($pickup_latitude_form, FILTER_VALIDATE_FLOAT) : null;
    $pickup_longitude_to_db = !empty($pickup_longitude_form) ? filter_var($pickup_longitude_form, FILTER_VALIDATE_FLOAT) : null;

    if (empty($brand)) $errors['brand'] = "Brand is required.";
    if (empty($model)) $errors['model'] = "Model is required.";
    if ($year === false) $errors['year'] = "Invalid year format or out of range (1900 - " . (date("Y") + 2) . ").";
    if ($price_per_day === false) $errors['price_per_day'] = "Invalid price. Must be a positive number.";
    if ($quantity === false) $errors['quantity'] = "Invalid quantity. Must be zero or more.";

    if (!empty($pickup_latitude_form) && $pickup_latitude_to_db === false) {
        $errors['pickup_latitude'] = "Invalid latitude format. Must be a number (e.g., 24.7136).";
    }
    if (!empty($pickup_longitude_form) && $pickup_longitude_to_db === false) {
        $errors['pickup_longitude'] = "Invalid longitude format. Must be a number (e.g., 46.6753).";
    }
    if (($pickup_latitude_to_db !== null && $pickup_longitude_to_db === null) || ($pickup_latitude_to_db === null && $pickup_longitude_to_db !== null)) {
       $errors['pickup_coords'] = "Both latitude and longitude must be provided if specifying a custom location, or leave both blank for default.";
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/images/cars/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0775, true)) {
                 $errors['image'] = "Failed to create image upload directory.";
            }
        }
        
        if (empty($errors['image'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = mime_content_type($_FILES['image']['tmp_name']);
            $file_size = $_FILES['image']['size'];
            $max_size = 5 * 1024 * 1024;

            if (!in_array($file_type, $allowed_types)) {
                $errors['image'] = "Invalid file type. Only JPG, PNG, GIF, WEBP are allowed.";
            } elseif ($file_size > $max_size) {
                $errors['image'] = "File size exceeds the limit of 5MB.";
            } else {
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $image_name = uniqid('carimg_', true) . '.' . $file_extension;
                $target_file = $upload_dir . $image_name;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $errors['image'] = "Failed to upload image.";
                    $image_name = ''; 
                }
            }
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['image']['error'] != UPLOAD_ERR_OK) {
        $errors['image'] = "Error uploading image. Code: " . $_FILES['image']['error'];
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO cars (brand, model, year, price_per_day, quantity, description, is_available, image, pickup_location_name, pickup_latitude, pickup_longitude) 
                    VALUES (:brand, :model, :year, :price_per_day, :quantity, :description, :is_available, :image, :pickup_location_name, :pickup_latitude, :pickup_longitude)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':brand', $brand);
            $stmt->bindParam(':model', $model);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':price_per_day', $price_per_day);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':is_available', $is_available, PDO::PARAM_INT);
            $stmt->bindParam(':image', $image_name);
            $stmt->bindParam(':pickup_location_name', $pickup_location_name_form, $pickup_location_name_form ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':pickup_latitude', $pickup_latitude_to_db, $pickup_latitude_to_db !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindParam(':pickup_longitude', $pickup_longitude_to_db, $pickup_longitude_to_db !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

            if ($stmt->execute()) {
                $_SESSION['car_action_success'] = "Car '{$brand} {$model}' added successfully!";
                header("Location: " . APP_URL . "admin/manage_cars.php");
                exit();
            } else {
                $errors['db'] = "Failed to add car to database.";
            }
        } catch (PDOException $e) {
            $errors['db'] = "Database error: " . $e->getMessage();
            error_log("Add Car DB Error: " . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="page-header">
    <h1><?php echo $page_title; ?></h1>
    <a href="<?php echo APP_URL; ?>admin/manage_cars.php" class="btn btn-outline-secondary">Back to Car List</a>
</div>

<?php if (!empty($errors['db'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errors['db']); ?></div>
<?php endif; ?>
<?php if (!empty($errors['pickup_coords'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errors['pickup_coords']); ?></div>
<?php endif; ?>


<div class="card shadow-sm">
    <div class="card-header">
        New Car Details
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="brand" class="form-label">Brand <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo isset($errors['brand']) ? 'is-invalid' : ''; ?>" id="brand" name="brand" value="<?php echo htmlspecialchars($brand); ?>" required>
                    <?php if (isset($errors['brand'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['brand']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php echo isset($errors['model']) ? 'is-invalid' : ''; ?>" id="model" name="model" value="<?php echo htmlspecialchars($model); ?>" required>
                     <?php if (isset($errors['model'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['model']); ?></div><?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?php echo isset($errors['year']) ? 'is-invalid' : ''; ?>" id="year" name="year" value="<?php echo htmlspecialchars($year); ?>" min="1900" max="<?php echo date("Y")+2; ?>" required>
                    <?php if (isset($errors['year'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['year']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="price_per_day" class="form-label">Price per Day ($) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?php echo isset($errors['price_per_day']) ? 'is-invalid' : ''; ?>" id="price_per_day" name="price_per_day" value="<?php echo htmlspecialchars($price_per_day); ?>" step="0.01" min="0.01" required>
                    <?php if (isset($errors['price_per_day'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['price_per_day']); ?></div><?php endif; ?>
                </div>
                 <div class="col-md-4 mb-3">
                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>" min="0" required>
                    <?php if (isset($errors['quantity'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['quantity']); ?></div><?php endif; ?>
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Car Image (Optional)</label>
                <input class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                <small class="form-text text-muted">Max 5MB. JPG, PNG, GIF, WEBP.</small>
                <?php if (isset($errors['image'])): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['image']); ?></div><?php endif; ?>
            </div>
             <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1" <?php echo $is_available ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_available">
                    Mark as Available
                </label>
            </div>

            <hr class="my-4">
            <h5 class="mb-3 text-muted">Pickup Location (Optional)</h5>
            <p class="small text-muted mb-3">Click on the map to set a custom pickup location, or leave fields blank to use the default site location. Drag the marker to adjust. Use the search bar on the map to find a location.</p>
            
            <div class="mb-3">
                <div id="pickup-map-container" style="position: relative;">
                    <div id="pickup-map" style="height: 350px; width: 100%; border-radius: 0.375rem; border: 1px solid #ddd;"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="pickup_location_name" class="form-label">Location Name</label>
                    <input type="text" class="form-control" id="pickup_location_name" name="pickup_location_name" value="<?php echo htmlspecialchars($pickup_location_name_form); ?>" placeholder="e.g., Main Office (auto-fills)">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="pickup_latitude" class="form-label">Latitude</label>
                    <input type="text" class="form-control <?php echo isset($errors['pickup_latitude']) ? 'is-invalid' : ''; ?>" id="pickup_latitude" name="pickup_latitude" value="<?php echo htmlspecialchars($pickup_latitude_form); ?>" readonly placeholder="Set by map">
                     <?php if (isset($errors['pickup_latitude'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['pickup_latitude']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="pickup_longitude" class="form-label">Longitude</label>
                    <input type="text" class="form-control <?php echo isset($errors['pickup_longitude']) ? 'is-invalid' : ''; ?>" id="pickup_longitude" name="pickup_longitude" value="<?php echo htmlspecialchars($pickup_longitude_form); ?>" readonly placeholder="Set by map">
                    <?php if (isset($errors['pickup_longitude'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['pickup_longitude']); ?></div><?php endif; ?>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="clearLocationBtn">Clear Custom Location</button>
            
            <?php if (isset($errors['pickup_coords'])): ?>
                <div class="alert alert-danger mt-2 p-2 small"><?php echo htmlspecialchars($errors['pickup_coords']); ?></div>
            <?php endif; ?>

            <hr class="my-4">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary px-4">Add Car</button>
                <a href="<?php echo APP_URL; ?>admin/manage_cars.php" class="btn btn-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$page_specific_js = 'map_picker_script.js'; 
require_once __DIR__ . '/includes/admin_footer.php';
?>