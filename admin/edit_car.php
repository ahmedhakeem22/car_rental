<?php
$page_title = "Edit Car";
require_once __DIR__ . '/includes/admin_header.php';

$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$car_id) {
    $_SESSION['car_action_error'] = "Invalid car ID specified for editing.";
    header("Location: " . APP_URL . "admin/manage_cars.php");
    exit();
}

$brand = '';
$model = '';
$year = '';
$price_per_day = '';
$quantity = '';
$description = '';
$is_available = 0;
$current_image = '';
$errors = [];

try {
    $stmt_car = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt_car->execute([$car_id]);
    $car = $stmt_car->fetch();

    if (!$car) {
        $_SESSION['car_action_error'] = "Car with ID {$car_id} not found.";
        header("Location: " . APP_URL . "admin/manage_cars.php");
        exit();
    }
    $brand = $car['brand'];
    $model = $car['model'];
    $year = $car['year'];
    $price_per_day = $car['price_per_day'];
    $quantity = $car['quantity'];
    $description = $car['description'];
    $is_available = $car['is_available'];
    $current_image = $car['image'];

} catch (PDOException $e) {
    $errors['db_fetch'] = "Database error fetching car details: " . $e->getMessage();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand_posted = trim($_POST['brand']);
    $model_posted = trim($_POST['model']);
    $year_posted = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1900, "max_range" => date("Y") + 2]]);
    $price_per_day_posted = filter_input(INPUT_POST, 'price_per_day', FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0.01]]);
    $quantity_posted = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    $description_posted = trim($_POST['description']);
    $is_available_posted = isset($_POST['is_available']) ? 1 : 0;
    $new_image_name = $current_image; 

    if (empty($brand_posted)) $errors['brand'] = "Brand is required.";
    if (empty($model_posted)) $errors['model'] = "Model is required.";
    if ($year_posted === false) $errors['year'] = "Invalid year format or out of range.";
    if ($price_per_day_posted === false) $errors['price_per_day'] = "Invalid price. Must be a positive number.";
    if ($quantity_posted === false) $errors['quantity'] = "Invalid quantity. Must be zero or more.";

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
                $uploaded_image_temp_name = uniqid('carimg_edit_', true) . '.' . $file_extension;
                $target_file = $upload_dir . $uploaded_image_temp_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    if (!empty($current_image) && file_exists($upload_dir . $current_image)) {
                        unlink($upload_dir . $current_image); 
                    }
                    $new_image_name = $uploaded_image_temp_name; 
                } else {
                    $errors['image'] = "Failed to upload new image.";
                }
            }
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['image']['error'] != UPLOAD_ERR_OK) {
        $errors['image'] = "Error uploading image. Code: " . $_FILES['image']['error'];
    }


    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE cars SET brand = :brand, model = :model, year = :year, price_per_day = :price_per_day, quantity = :quantity, description = :description, is_available = :is_available, image = :image WHERE id = :id");
            
            $stmt->bindParam(':brand', $brand_posted);
            $stmt->bindParam(':model', $model_posted);
            $stmt->bindParam(':year', $year_posted, PDO::PARAM_INT);
            $stmt->bindParam(':price_per_day', $price_per_day_posted);
            $stmt->bindParam(':quantity', $quantity_posted, PDO::PARAM_INT);
            $stmt->bindParam(':description', $description_posted);
            $stmt->bindParam(':is_available', $is_available_posted, PDO::PARAM_INT);
            $stmt->bindParam(':image', $new_image_name);
            $stmt->bindParam(':id', $car_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['car_action_success'] = "Car '{$brand_posted} {$model_posted}' (ID: {$car_id}) updated successfully!";
                header("Location: " . APP_URL . "admin/manage_cars.php");
                exit();
            } else {
                $errors['db'] = "Failed to update car in database.";
            }
        } catch (PDOException $e) {
            $errors['db'] = "Database error: " . $e->getMessage();
            error_log("Edit Car DB Error: " . $e->getMessage());
        }
    } else {
        $brand = $brand_posted;
        $model = $model_posted;
        $year = $year_posted !== false ? $year_posted : $car['year'];
        $price_per_day = $price_per_day_posted !== false ? $price_per_day_posted : $car['price_per_day'];
        $quantity = $quantity_posted !== false ? $quantity_posted : $car['quantity'];
        $description = $description_posted;
        $is_available = $is_available_posted;
    }
}
?>

<div class="page-header">
    <h1><?php echo $page_title; ?>: <small class="text-muted"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></small></h1>
     <a href="<?php echo APP_URL; ?>admin/manage_cars.php" class="btn btn-outline-secondary">Back to Car List</a>
</div>

<?php if (!empty($errors['db']) || !empty($errors['db_fetch'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errors['db'] ?? $errors['db_fetch']); ?></div>
<?php endif; ?>


<div class="card shadow-sm">
    <div class="card-header">
        Edit Car Details (ID: <?php echo $car_id; ?>)
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $car_id; ?>" enctype="multipart/form-data">
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
                <label for="image" class="form-label">Change Car Image (Optional)</label>
                <input class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                <small class="form-text text-muted">Max 5MB. JPG, PNG, GIF, WEBP. Leave empty to keep current image.</small>
                <?php if (isset($errors['image'])): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['image']); ?></div><?php endif; ?>
                <?php if (!empty($current_image)): ?>
                    <div class="mt-2">
                        <p class="mb-1"><small>Current Image:</small></p>
                        <img src="<?php echo APP_URL . 'assets/images/cars/' . htmlspecialchars($current_image); ?>" alt="Current car image" style="max-width: 150px; max-height: 100px; border-radius: 4px; border: 1px solid #ddd;">
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1" <?php echo $is_available ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_available">
                    Mark as Available
                </label>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary px-4">Update Car</button>
                <a href="<?php echo APP_URL; ?>admin/manage_cars.php" class="btn btn-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>