<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config/database_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$restaurant_id = $_SESSION['admin_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid Item ID.";
    header("Location: menu.php");
    exit();
}

$id = (int)$_GET['id'];

/* Fetch Item */
$stmt = $conn->prepare("
    SELECT
        id,
        restaurant_id,
        name,
        description,
        price,
        discount,
        category,
        status,
        image
    FROM items
    WHERE id = ? AND restaurant_id = ?
");

$stmt->bind_param("ii", $id, $restaurant_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Item not found.";
    header("Location: menu.php");
    exit();
}

$item = $result->fetch_assoc();
$stmt->close();

/* Calculate Final Price */
$final_price = $item['price'] - ($item['price'] * $item['discount'] / 100);

/* Update Item */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $status = trim($_POST['status']);

    $price = floatval($_POST['price']);
    $discount = intval($_POST['discount']);

    if ($discount < 0) $discount = 0;
    if ($discount > 100) $discount = 100;

    $image = $item['image'];

    if (!empty($_FILES['image']['name'])) {

        $allowed = ['jpg','jpeg','png','webp'];

        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed)) {

            $_SESSION['error'] = "Only JPG, JPEG, PNG and WEBP images are allowed.";
            header("Location: edit_category.php?id=".$id);
            exit();

        }

        $image_name = uniqid()."_".basename($_FILES['image']['name']);

        $upload_path = "uploads/".$image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {

            if (!empty($item['image']) && file_exists($item['image'])) {
                unlink($item['image']);
            }

            $image = $upload_path;

        } else {

            $_SESSION['error'] = "Image upload failed.";
            header("Location: edit_category.php?id=".$id);
            exit();

        }

    }

    $stmt = $conn->prepare("
        UPDATE items
        SET
            name = ?,
            category = ?,
            status = ?,
            description = ?,
            price = ?,
            discount = ?,
            image = ?
        WHERE id = ?
        AND restaurant_id = ?
    ");

    $stmt->bind_param(
        "ssssddsii",
        $name,
        $category,
        $status,
        $description,
        $price,
        $discount,
        $image,
        $id,
        $restaurant_id
    );

    if ($stmt->execute()) {

        $_SESSION['success'] = "Menu item updated successfully.";

        header("Location: menu.php");
        exit();

    } else {

        $_SESSION['error'] = "Update failed : ".$stmt->error;

    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style/additems.css">
</head>
<body>
    <div class="editor-container">
        <div class="editor-header">
            <a href="menu.php"><i class="fa-solid fa-arrow-left"></i></a>
            <h2>Edit Item</h2>
        </div>

        <form id="menu-form" action="" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="menu-name" class="form-label">Item Name</label>
                <input type="text" name="name" id="menu-name" class="form-control"
                    value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label for="menu-price" class="form-label">Price (₹)</label>
                    <input type="number" id="menu-price" name="price" class="form-control"
                        min="0" step="0.01"
                        value="<?= htmlspecialchars($item['price']) ?>" required>
                </div>

                <div class="form-group half">
                    <label for="menu-discount" class="form-label">Discount (%)</label>
                    <input type="number" id="menu-discount" name="discount" value="<?= $item['discount'] ?>"
                        class="form-control" min="0" max="100" step="1"
                        placeholder="0">
                </div>
            </div>

            <div class="price-display">
                <span class="original-price">₹<span id="original-price"><?= $item['price'] ?></span></span>
                <span>→</span>
                <span class="final-price">₹<span id="final-price"><?= $final_price ?></span></span>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label for="menu-category" class="form-label">Category</label>
                    <select id="menu-category" name="category" class="form-control" required>
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
                                $selected = ($item['category'] == $category) ? "selected" : "";
                                echo '<option value="'.$category.'" '.$selected.'>'.$category.'</option>';
                            }
                        }
                        $stmt_1->close();
                        ?>
                    </select>
                </div>

                <div class="form-group half">
                    <label for="menu-status" class="form-label">Status</label>
                    <select id="menu-status" name="status" class="form-control" required>
                        <option value="available" <?= $item['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="unavailable" <?= $item['status'] == 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="menu-description" class="form-label">Description</label>
                <textarea id="menu-description" name="description"
                    class="form-control form-textarea"
                    required><?= htmlspecialchars($item['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="menu-image" class="form-label">Item Image</label>

                <div class="file-input-wrapper">
                    <label for="menu-image" class="file-input-label">
                        <i class="fas fa-cloud-upload-alt"></i> Choose new image
                    </label>
                    <input type="file" id="menu-image" name="image"
                        class="form-control" accept="image/*">
                </div>

                <div id="image-preview">
                    <img id="preview-image"
                        src="<?= htmlspecialchars($item['image']) ?>"
                        alt="Current Image">
                </div>
            </div>

            <div class="form-actions">
                <a href="menu.php" class="btn btn-danger" id="cancel-btn">
                    <i class="fas fa-times"></i> Cancel
                </a>

                <button type="submit" class="btn btn-success" id="save-btn">
                    <i class="fas fa-save"></i> Update Item
                </button>
            </div>

        </form>

    </div>

<script>
    // AUTO PRICE UPDATE
    const price = document.getElementById("menu-price");
    const discount = document.getElementById("menu-discount");
    const originalDisplay = document.getElementById("original-price");
    const finalDisplay = document.getElementById("final-price");

    function updatePrices() {
        let p = parseFloat(price.value) || 0;
        let d = parseFloat(discount.value) || 0;

        let final = p - (p * d / 100);

        originalDisplay.textContent = p.toFixed(2);
        finalDisplay.textContent = final.toFixed(2);
    }

    price.addEventListener("input", updatePrices);
    discount.addEventListener("input", updatePrices);

    updatePrices(); // auto load when page opens

    // IMAGE PREVIEW FIXED
    document.getElementById("menu-image").addEventListener("change", function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById("preview-image").src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
</script>

</body>

</html>