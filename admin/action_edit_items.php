<?php
include 'database_connection.php';
session_start();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No item ID provided.";
    header("Location: menu.php");
    exit();
}

$id = $_GET['id'];

// Step 1: Fetch existing item data securely
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    $_SESSION['error'] = "Item not found.";
    header("Location: menu.php");
    exit();
}

$item = $result->fetch_assoc();
$stmt->close();

// Step 2: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    // Optional: Handle image update
    $image = $item['image']; // default image
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $image_tmp = $_FILES['image']['tmp_name'];
        $upload_path = "uploads/" . $image_name;

        if (move_uploaded_file($image_tmp, $upload_path)) {
            $image = $upload_path;
        }
    }

    // Step 3: Update item securely
    $update_stmt = $conn->prepare("UPDATE items SET name = ?, category = ?, status = ?, description = ?, price = ?, image = ? WHERE id = ?");
    $update_stmt->bind_param("ssssssi", $name, $category, $status, $description, $price, $image, $id);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Menu item updated successfully.";
        header("Location: menu.php");
        exit();
    } else {
        $_SESSION['error'] = "Update failed: " . $update_stmt->error;
    }

    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff6b6b;
            --secondary: #794afa;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #fd7e14;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .form-container {
            width: 100%;
            max-width: 500px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container h2 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 25px;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 10px;
        }

        .form-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 3px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: var(--light);
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
            background-color: var(--white);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .image-upload {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
        }

        .file-input-label {
            display: block;
            padding: 12px 15px;
            background-color: var(--light);
            border: 1px dashed #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            border-color: var(--primary);
            background-color: rgba(74, 107, 255, 0.05);
        }

        .file-input-label i {
            margin-right: 8px;
            color: var(--primary);
        }

        input[type="file"] {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .current-image {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 10px;
        }

        .current-image img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            margin-top: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .current-image p {
            font-size: 12px;
            color: var(--gray);
            margin-top: 5px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 1rem;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #3a5bef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 107, 255, 0.3);
        }

        /* Custom select arrow */
        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 15px;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .form-container {
                padding: 20px;
            }
            
            .form-container h2 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><i class="fas fa-edit"></i> Edit Menu Item</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Item Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <?php
                    $restaurant_id = $_SESSION['admin_id'];
                    $sql_1 = "SELECT * FROM categories WHERE restaurant_id = ?";
                    $stmt_1 = $conn->prepare($sql_1);
                    $stmt_1->bind_param("i", $restaurant_id);
                    $stmt_1->execute();
                    $result_1 = $stmt_1->get_result();

                    if ($result_1 && $result_1->num_rows > 0) {
                        while ($row = $result_1->fetch_assoc()) {
                            $category = htmlspecialchars($row['name']);
                            echo '<option value="' . $category . '"' . ($item['category'] == $category ? ' selected' : '') . '>' . $category . '</option>';
                        }
                    }
                    $stmt_1->close();
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="available" <?= $item['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="unavailable" <?= $item['status'] == 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?= htmlspecialchars($item['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price (₹)</label>
                <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($item['price']) ?>" required>
            </div>

            <div class="form-group">
                <label>Item Image</label>
                <div class="image-upload">
                    <div class="file-input-wrapper">
                        <label for="image" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i> Choose new image
                        </label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                    <div class="current-image">
                        <p>Current Image:</p>
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="Current item image">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Item
            </button>
        </form>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate image type
            if (!file.type.match('image.*')) {
                alert('Please select an image file (JPEG, PNG, etc.)');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(event) {
                const currentImage = document.querySelector('.current-image img');
                currentImage.src = event.target.result;
                
                // Visual feedback
                currentImage.style.borderColor = '#28a745';
                setTimeout(() => {
                    currentImage.style.borderColor = '#e0e0e0';
                }, 1000);
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>