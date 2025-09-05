<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if (is_numeric($searchTerm)) {
    $sql = "SELECT * FROM products WHERE id = ?";
} else {
    $sql = "SELECT * FROM products WHERE LOWER(product_name) LIKE LOWER(?)"; 
}

$stmt = $conn->prepare($sql);
if (is_numeric($searchTerm)) {
    $stmt->bind_param("i", $searchTerm); 
} else {
    $searchTermLike = strtolower($searchTerm) . "%"; 
    $stmt->bind_param("s", $searchTermLike);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .search-suggestions {
            position: absolute;
            background-color: white;
            border: 1px solid #ddd;
            max-height: 200px;
            overflow-y: auto;
            width: 265px;
            z-index: 1000;
            display: none;
            margin-top: 40px;
            margin-bottom: 100px;
        }

        .search-suggestions li {
            padding: 10px;
            cursor: pointer;
            list-style-type: none;
            color: black;
        }

        .search-suggestions li:hover {
            background-color: #f0f0f0;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            color: #333;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #1f3b73;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 10px;
            transition: background-color 0.3s ease;
        }

        .navbar a:hover {
            background-color: #15315b;
            border-radius: 5px;
        }

        .navbar form {
            display: flex;
            position: relative;
        }

        .navbar input[type="text"] {
            padding: 8px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            width: 250px;
            margin-right: 10px;
        }

        .navbar button {
            padding: 8px 15px;
            border: none;
            background-color: #009688;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .navbar button:hover {
            background-color: #007d6a;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 15px;
            width: 280px;
            overflow: hidden;
            text-align: center;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #eaeaea;
        }

        .product-card h3 {
            margin: 15px 0 5px 0;
            font-size: 1.2rem;
        }

        .product-card .price {
            font-weight: bold;
            color: #009688;
            margin-bottom: 10px;
        }

        .product-card p {
            margin: 0;
            color: #666;
        }

        .back-link {
            color: #009688;
            text-decoration: underline;
        }

        .search-suggestions {
            position: absolute;
            background-color: white;
            border: 1px solid #ddd;
            max-height: 200px;
            overflow-y: auto;
            width: 265px;
            z-index: 1000;
            display: none;
            margin-top: 40px;
            margin-bottom: 100px;
        }

        .search-suggestions li {
            padding: 10px;
            cursor: pointer;
            list-style-type: none;
            color: black;
        }

        .search-suggestions li:hover {
            background-color: #f0f0f0;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            color: #333;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #1f3b73;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 10px;
            transition: background-color 0.3s ease;
        }

        .navbar a:hover {
            background-color: #15315b;
            border-radius: 5px;
        }

        .navbar form {
            display: flex;
            position: relative;
        }

        .navbar input[type="text"] {
            padding: 8px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            width: 250px;
            margin-right: 10px;
        }

        .navbar button {
            padding: 8px 15px;
            border: none;
            background-color: #009688;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .navbar button:hover {
            background-color: #007d6a;
        }

        .burger-menu {
            font-size: 1.8rem;
            cursor: pointer;
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 150px;
            z-index: 1000;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
        }

        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }

        @media (max-width: 768px) {

            .navbar h1,
            .navbar form,
            .navbar a {
                display: none;
            }

            .burger-menu {
                display: block;
            }
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 15px;
            width: 200px;
            overflow: hidden;
            text-align: center;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #eaeaea;
        }

        .product-card h3 {
            margin: 15px 0 5px 0;
            font-size: 1.2rem;
        }

        .product-card .price {
            font-weight: bold;
            color: #009688;
            margin-bottom: 10px;
        }

        .product-card p {
            margin: 0;
            color: #666;
        }

        .back-link {
            color: #009688;
            text-decoration: underline;
        }
        /* Out of stock styling */
.product-card.out-of-stock {
    filter: grayscale(100%);
    opacity: 0.6;
}
.product-card.out-of-stock:hover {
    transform: none; /* prevent hover lift */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* keep normal shadow */
}

    </style>

    <script>
        async function fetchSearchSuggestions(query) {
            if (query.trim().length > 0) {
                const response = await fetch(`search_suggestions.php?query=${encodeURIComponent(query)}`);
                const suggestions = await response.json();
                showSuggestions(suggestions);
            } else {
                hideSuggestions();
            }
        }

        function showSuggestions(suggestions) {
            const suggestionsContainer = document.querySelector('.search-suggestions');
            suggestionsContainer.innerHTML = ''; 
            if (suggestions.length > 0) {
                suggestions.forEach(suggestion => {
                    const li = document.createElement('li');
                    li.textContent = suggestion;
                    li.addEventListener('click', () => {
                        document.querySelector('input[name="search"]').value = suggestion;
                        hideSuggestions();
                    });
                    suggestionsContainer.appendChild(li);
                });
                suggestionsContainer.style.display = 'block';
            } else {
                hideSuggestions();
            }
        }

        function hideSuggestions() {
            document.querySelector('.search-suggestions').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.querySelector('input[name="search"]');
            searchInput.addEventListener('input', (event) => {
                fetchSearchSuggestions(event.target.value);
            });

            document.addEventListener('click', (event) => {
                if (!searchInput.contains(event.target) && !document.querySelector('.search-suggestions').contains(event.target)) {
                    hideSuggestions();
                }
            });
        });
        async function fetchSearchSuggestions(query) {
            if (query.trim().length > 0) {
                const response = await fetch(`search_suggestions.php?query=${encodeURIComponent(query)}`);
                const suggestions = await response.json();
                showSuggestions(suggestions);
            } else {
                hideSuggestions();
            }
        }

        function showSuggestions(suggestions) {
            const suggestionsContainer = document.querySelector('.search-suggestions');
            suggestionsContainer.innerHTML = ''; 
            if (suggestions.length > 0) {
                suggestions.forEach(suggestion => {
                    const li = document.createElement('li');
                    li.textContent = suggestion;
                    li.addEventListener('click', () => {
                        document.querySelector('input[name="search"]').value = suggestion;
                        hideSuggestions();
                    });
                    suggestionsContainer.appendChild(li);
                });
                suggestionsContainer.style.display = 'block';
            } else {
                hideSuggestions();
            }
        }

        function hideSuggestions() {
            document.querySelector('.search-suggestions').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.querySelector('input[name="search"]');
            searchInput.addEventListener('input', (event) => {
                fetchSearchSuggestions(event.target.value);
            });

            document.addEventListener('click', (event) => {
                if (!searchInput.contains(event.target) && !document.querySelector('.search-suggestions').contains(event.target)) {
                    hideSuggestions();
                }
            });
        });

        function toggleDropdown() {
            const dropdownMenu = document.querySelector(".dropdown-menu");
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        }

        document.addEventListener("click", function(event) {
            const isClickInside = document.querySelector(".burger-menu").contains(event.target);
            const dropdownMenu = document.querySelector(".dropdown-menu");

            if (!isClickInside && dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            }
        });
    </script>
</head>

<body>
    <div class="navbar">
        <a href="userDashboard.php">
            <h1>SwiftShop</h1>
        </a>
        <form>
            <input type="text" name="search" placeholder="Search products...">
            <div class="search-suggestions"></div>
            <button type="submit">Search</button>
        </form>

        <div class="burger-menu" onclick="toggleDropdown()">
            <i class="fas fa-bars"></i>
            <div class="dropdown-menu">
                <a href="Userprofile.php">Profile</a>
                <a href="cart.php">My Cart</a>
                <a href="wishlist.php">Wishlist</a>
                <a href="userPurchase.php">My Purchase</a>
                <a href="userLogout.php">Logout</a>
            </div>
        </div>
    </div>


    <div class="container">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <?php 
                // Limit product name to 8 words
                $words = explode(" ", $product['product_name']);
                $shortName = implode(" ", array_slice($words, 0, 8));
                if (count($words) > 8) {
                    $shortName .= "...";
                }
                $stockClass = ($product['stocks'] == 0) ? 'out-of-stock' : '';
            ?>
            <a class="product-card <?= $stockClass ?>" href="product_detail.php?id=<?= $product['id'] ?>">
        <img src="<?= $product['picture'] ?>" alt="Product Image">
        <h3><?= htmlspecialchars($shortName) ?></h3>
        <p class="price">₱<?= number_format($product['price'], 2) ?></p>
        <p>In Stock: <?= $product['stocks'] ?></p>
    </a>
<?php endforeach; ?>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

    <script defer>
        document.body.appendChild(document.createElement('div')).setAttribute('id', 'chatBubbleRoot');
        window.agx = '671a385430a353347a33b0066PyiGjWgx7/XJ+kgkiNBFw==|hkep9PlY4SHf8R25NAscyeYuVjePtuUabWt+50pGyc8=';
    </script>
    <script defer src="https://storage.googleapis.com/agentx-cdn-01/agentx-chat.js"></script>
</body>

</html>