<?php
$page_title = "Car Details";
require_once __DIR__ . '/includes/header.php'; // header.php سيحتاج أيضًا لتضمين CSS الخاص بـ Mapbox

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Invalid car ID.'];
    header("Location: " . APP_URL . "cars.php");
    exit();
}
$car_id = (int)$_GET['id'];
$current_user_id = $_SESSION['user_id'] ?? null;

try {
    $stmt = $pdo->prepare("SELECT c.*, COALESCE(c.average_rating, 0) as calculated_avg_rating, COALESCE(c.total_reviews, 0) as calculated_total_reviews 
                           FROM cars c 
                           WHERE c.id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();

    if (!$car) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Car not found.'];
        header("Location: " . APP_URL . "cars.php");
        exit();
    }
    
    $stmt_reviews = $pdo->prepare("
        SELECT rev.*, u.name as reviewer_name 
        FROM reviews rev 
        JOIN users u ON rev.user_id = u.id 
        WHERE rev.car_id = ? 
        ORDER BY rev.created_at DESC
    ");
    $stmt_reviews->execute([$car_id]);
    $reviews = $stmt_reviews->fetchAll();

    $user_can_review = false;
    $rental_id_for_review = null;
    if ($current_user_id) {
        $stmt_check_review_eligibility = $pdo->prepare("
            SELECT ren.id AS eligible_rental_id 
            FROM rentals ren
            LEFT JOIN reviews r ON ren.id = r.rental_id AND r.user_id = :current_user_id_for_review_check
            WHERE ren.user_id = :current_user_id_for_rental AND ren.car_id = :car_id AND ren.status = 'completed' AND r.id IS NULL
            ORDER BY ren.end_date DESC 
            LIMIT 1
        ");
        $stmt_check_review_eligibility->execute([
            ':current_user_id_for_review_check' => $current_user_id,
            ':current_user_id_for_rental' => $current_user_id,
            ':car_id' => $car_id
        ]);
        $eligible_rental = $stmt_check_review_eligibility->fetch();
        if ($eligible_rental) {
            $user_can_review = true;
            $rental_id_for_review = $eligible_rental['eligible_rental_id'];
        }
    }

} catch (PDOException $e) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error fetching car details or reviews: ' . $e->getMessage()];
    header("Location: " . APP_URL . "cars.php");
    exit();
}

$min_date = date('Y-m-d');
$min_end_date = date('Y-m-d', strtotime('+1 day'));
$average_rating = $car['calculated_avg_rating'] ? round($car['calculated_avg_rating'], 1) : 'N/A';
$total_reviews_display = $car['calculated_total_reviews'] ?? 0;

$pickup_city = DEFAULT_PICKUP_CITY;
$pickup_lng = DEFAULT_PICKUP_LNG; // Mapbox uses Lng, Lat order
$pickup_lat = DEFAULT_PICKUP_LAT;
$openweathermap_api_key = OPENWEATHERMAP_API_KEY;
$mapbox_access_token = MAPBOX_ACCESS_TOKEN; // جلب مفتاح Mapbox

?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>cars.php">Cars</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']['text']); unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['review_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['review_message']['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['review_message']['text']); unset($_SESSION['review_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['booking_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['booking_error']); unset($_SESSION['booking_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <img src="<?php echo APP_URL . 'assets/images/cars/' . (!empty($car['image']) ? htmlspecialchars($car['image']) : 'default-car.png'); ?>" class="img-fluid rounded shadow-sm" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>">
        
            <div class="mt-4 card shadow-sm location-weather-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Pickup Location & Weather</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6><i class="bi bi-pin-map me-1"></i>Location: <?php echo htmlspecialchars($pickup_city); ?></h6>
                            <div id="mapbox-map" style="height: 250px; width: 100%; border-radius: 0.25rem;"></div>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-thermometer-half me-1"></i>Current Weather</h6>
                            <div id="weather-info">
                                <p class="text-muted">Loading weather data...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
             <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title h2 mb-2"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h1>
                    <p class="card-text text-muted mb-2">Year: <?php echo htmlspecialchars($car['year']); ?></p>
                    <div class="d-flex align-items-center mb-3">
                        <?php if ($average_rating !== 'N/A'): ?>
                            <div class="star-rating-display me-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?php echo ($i <= floor($average_rating)) ? 'bi-star-fill text-warning' : (($i - 0.5 <= $average_rating) ? 'bi-star-half text-warning' : 'bi-star text-warning'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-muted small">(<?php echo htmlspecialchars($average_rating); ?> from <?php echo $total_reviews_display; ?> reviews)</span>
                        <?php else: ?>
                            <span class="text-muted small">No reviews yet.</span>
                        <?php endif; ?>
                    </div>

                    <p class="card-text h4 text-primary mb-3">
                        $<?php echo htmlspecialchars(number_format($car['price_per_day'], 2)); ?> <small class="text-muted fs-6">/ day</small>
                    </p>
                    <p class="card-text">
                        <?php if ($car['is_available'] && $car['quantity'] > 0): ?>
                            <span class="badge bg-success">Available (<?php echo htmlspecialchars($car['quantity']); ?> in stock)</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php endif; ?>
                    </p>
                    <hr>
                    <p class="card-text small"><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
                    
                    <?php if ($car['is_available'] && $car['quantity'] > 0): ?>
                        <hr>
                        <h5 class="mb-3">Rent This Car</h5>
                        <form action="<?php echo APP_URL; ?>process_booking.php" method="POST" id="rentalForm">
                            <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                            <input type="hidden" name="price_per_day" value="<?php echo $car['price_per_day']; ?>">
                            
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" min="<?php echo $min_date; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" min="<?php echo $min_end_date; ?>" required>
                            </div>
                            <div class="mb-3">
                                <p class="small">Total Days: <span id="total_days" class="fw-bold">0</span></p>
                                <p class="small">Estimated Price: $<span id="estimated_price" class="fw-bold">0.00</span></p>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button type="submit" class="btn btn-primary w-100 btn-lg">Book Now</button>
                            <?php else: ?>
                                <p class="text-center">
                                    <a href="<?php echo APP_URL; ?>login.php?redirect=<?php echo urlencode(APP_URL . 'car_details.php?id=' . $car['id']); ?>" class="btn btn-warning w-100 btn-lg">Login to Book</a>
                                </p>
                                <p class="text-center mt-2 small text-muted">New user? <a href="<?php echo APP_URL; ?>register.php">Register here</a>.</p>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                         <a href="<?php echo APP_URL; ?>cars.php" class="btn btn-secondary w-100 mt-3">Back to Car List</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <div class="reviews-section">
        <h3 class="mb-4">Customer Reviews & Ratings</h3>
        <?php if ($user_can_review && $rental_id_for_review !== null): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5>Write a Review (Rental ID: #<?php echo $rental_id_for_review; ?>)</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo APP_URL; ?>submit_review.php" method="POST">
                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                        <input type="hidden" name="rental_id" value="<?php echo $rental_id_for_review; ?>">
                        <div class="mb-3">
                            <label for="rating" class="form-label">Your Rating (1-5 Stars)</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <option value="">Select a rating</option>
                                <option value="5">★★★★★ (Excellent)</option>
                                <option value="4">★★★★☆ (Very Good)</option>
                                <option value="3">★★★☆☆ (Good)</option>
                                <option value="2">★★☆☆☆ (Fair)</option>
                                <option value="1">★☆☆☆☆ (Poor)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Your Comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="4" placeholder="Share your experience with this car..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Submit Review</button>
                    </form>
                </div>
            </div>
        <?php elseif ($current_user_id): ?>
             <p class="text-muted">You can write a review for this car after completing a rental, or you may have already reviewed all eligible rentals for this car.</p>
        <?php else: ?>
            <p class="text-muted">Please <a href="<?php echo APP_URL; ?>login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">login</a> to write a review after completing a rental.</p>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <p class="text-muted">Be the first to review this car!</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-subtitle mb-2 text-muted">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?php echo ($i <= $review['rating']) ? 'bi-star-fill text-warning' : 'bi-star text-warning'; ?>"></i>
                                <?php endfor; ?>
                                <span class="ms-2 fw-bold"><?php echo htmlspecialchars($review['reviewer_name']); ?></span>
                            </h6>
                            <small class="text-muted"><?php echo htmlspecialchars(date('M d, Y', strtotime($review['created_at']))); ?></small>
                        </div>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        <div class="review-actions small mt-2">
                            <button class="btn btn-sm btn-outline-success me-2 like-btn" data-review-id="<?php echo $review['id']; ?>">
                                <i class="bi bi-hand-thumbs-up"></i> Like <span class="like-count">(<?php echo htmlspecialchars($review['likes_count'] ?? 0); ?>)</span>
                            </button>
                            <button class="btn btn-sm btn-outline-danger dislike-btn" data-review-id="<?php echo $review['id']; ?>">
                                <i class="bi bi-hand-thumbs-down"></i> Dislike <span class="dislike-count">(<?php echo htmlspecialchars($review['dislikes_count'] ?? 0); ?>)</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const totalDaysSpan = document.getElementById('total_days');
    const estimatedPriceSpan = document.getElementById('estimated_price');
    const pricePerDay = parseFloat(<?php echo isset($car['price_per_day']) ? $car['price_per_day'] : 0; ?>);

    function calculateRental() {
        if (!startDateInput || !endDateInput || !totalDaysSpan || !estimatedPriceSpan) return;
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDateInput.value && endDateInput.value && endDate > startDate) {
            const timeDiff = endDate.getTime() - startDate.getTime();
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            if (dayDiff > 0) {
                totalDaysSpan.textContent = dayDiff;
                estimatedPriceSpan.textContent = (dayDiff * pricePerDay).toFixed(2);
            } else {
                totalDaysSpan.textContent = '0';
                estimatedPriceSpan.textContent = '0.00';
            }
        } else {
            totalDaysSpan.textContent = '0';
            estimatedPriceSpan.textContent = '0.00';
        }
    }
    
    if(startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            if (this.value) {
                let nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                endDateInput.min = nextDay.toISOString().split('T')[0];
                if (endDateInput.value && new Date(endDateInput.value) <= new Date(this.value)) {
                    endDateInput.value = ''; 
                }
            } else {
                 endDateInput.min = '<?php echo $min_end_date; ?>';
            }
            calculateRental();
        });
        endDateInput.addEventListener('change', calculateRental);
        calculateRental();
    }

    async function handleVote(reviewId, voteType, buttonElement) {
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        if (!isLoggedIn) {
            window.location.href = '<?php echo APP_URL . "login.php?redirect=" . urlencode($_SERVER["REQUEST_URI"]); ?>';
            return;
        }
        try {
            const response = await fetch('<?php echo APP_URL; ?>process_review_vote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ review_id: reviewId, vote_type: voteType })
            });
            const result = await response.json();
            if (result.success) {
                const reviewCardBody = buttonElement.closest('.card-body');
                const likeButton = reviewCardBody.querySelector('.like-btn');
                const dislikeButton = reviewCardBody.querySelector('.dislike-btn');
                likeButton.querySelector('.like-count').textContent = `(${result.likes_count})`;
                dislikeButton.querySelector('.dislike-count').textContent = `(${result.dislikes_count})`;
                likeButton.classList.remove('btn-success', 'text-white', 'active');
                likeButton.classList.add('btn-outline-success');
                dislikeButton.classList.remove('btn-danger', 'text-white', 'active');
                dislikeButton.classList.add('btn-outline-danger');
                if (result.user_vote === 'like') {
                    likeButton.classList.remove('btn-outline-success');
                    likeButton.classList.add('btn-success', 'text-white', 'active');
                } else if (result.user_vote === 'dislike') {
                    dislikeButton.classList.remove('btn-outline-danger');
                    dislikeButton.classList.add('btn-danger', 'text-white', 'active');
                }
            } else {
                alert(result.message || 'An error occurred while voting.');
            }
        } catch (error) {
            console.error('Error processing vote:', error);
            alert('An error occurred. Please check console for details.');
        }
    }
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function() { handleVote(this.dataset.reviewId, 'like', this); });
    });
    document.querySelectorAll('.dislike-btn').forEach(button => {
        button.addEventListener('click', function() { handleVote(this.dataset.reviewId, 'dislike', this); });
    });
    async function fetchUserVoteStatus(reviewId) {
        const currentUserId = <?php echo $_SESSION['user_id'] ?? 'null'; ?>;
        if (!currentUserId) return null;
        try {
            const response = await fetch(`<?php echo APP_URL; ?>get_user_vote_status.php?review_id=${reviewId}`);
            const data = await response.json();
            if (data.success) return data.vote_type;
        } catch (error) { console.error("Error fetching user vote status for review " + reviewId + ":", error); }
        return null;
    }
    document.querySelectorAll('.review-actions').forEach(async (actionsDiv) => {
        const likeButton = actionsDiv.querySelector('.like-btn');
        const dislikeButton = actionsDiv.querySelector('.dislike-btn');
        if (!likeButton) return; 
        const reviewId = likeButton.dataset.reviewId;
        const userVote = await fetchUserVoteStatus(reviewId);
        if (userVote === 'like') {
            likeButton.classList.remove('btn-outline-success');
            likeButton.classList.add('btn-success', 'text-white', 'active');
        } else if (userVote === 'dislike') {
            dislikeButton.classList.remove('btn-outline-danger');
            dislikeButton.classList.add('btn-danger', 'text-white', 'active');
        }
    });

    function fetchWeatherData() {
        const weatherInfoDiv = document.getElementById('weather-info');
        if (!weatherInfoDiv) return;
        const lat = <?php echo $pickup_lat; ?>;
        const lon = <?php echo $pickup_lng; ?>;
        const apiKey = '<?php echo $openweathermap_api_key; ?>';
        if (!apiKey || apiKey === 'YOUR_OPENWEATHERMAP_API_KEY') {
            weatherInfoDiv.innerHTML = `<p class="text-warning small">OpenWeatherMap API key not configured.</p>`;
            return;
        }
        const url = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.cod === 200) {
                    weatherInfoDiv.innerHTML = `
                        <p class="lead mb-1">${data.name}</p>
                        <p class="mb-1"><img src="https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png" alt="${data.weather[0].description}" class="weather-icon"> <span class="h5">${Math.round(data.main.temp)}°C</span>, <span class="weather-description">${data.weather[0].description}</span></p>
                        <p class="mb-0 small text-muted">Humidity: ${data.main.humidity}%, Wind: ${data.wind.speed.toFixed(1)} m/s</p>
                    `;
                } else {
                    weatherInfoDiv.innerHTML = `<p class="text-danger small">Weather: ${data.message || 'Unavailable'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching weather data:', error);
                weatherInfoDiv.innerHTML = '<p class="text-danger small">Error loading weather data.</p>';
            });
    }
    fetchWeatherData();

    function initMapbox() {
        const mapDiv = document.getElementById('mapbox-map');
        const accessToken = '<?php echo $mapbox_access_token; ?>';

        if (!mapDiv) return;
        if (!accessToken || accessToken === 'pk.YOUR_MAPBOX_PUBLIC_ACCESS_TOKEN_HERE') {
            mapDiv.innerHTML = '<p class="text-center text-warning p-3 small bg-light rounded">Mapbox Access Token not configured.</p>';
            mapDiv.style.display = 'flex';
            mapDiv.style.alignItems = 'center';
            mapDiv.style.justifyContent = 'center';
            return;
        }

        mapboxgl.accessToken = accessToken;
        const map = new mapboxgl.Map({
            container: 'mapbox-map',
            style: 'mapbox://styles/mapbox/streets-v12', 
            center: [<?php echo $pickup_lng; ?>, <?php echo $pickup_lat; ?>], 
            zoom: 13 
        });

        map.addControl(new mapboxgl.NavigationControl(), 'top-right');

        new mapboxgl.Marker()
            .setLngLat([<?php echo $pickup_lng; ?>, <?php echo $pickup_lat; ?>])
            .setPopup(new mapboxgl.Popup().setText('<?php echo htmlspecialchars($pickup_city); ?> Pickup Location'))
            .addTo(map);
        
        map.on('load', function () {
            map.resize(); 
        });
    }
    initMapbox();
});
</script>