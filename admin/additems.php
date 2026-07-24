<?php
session_start();
include '../config/database_connection.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Item</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="style/additems.css">
</head>

<body>
    <div class="editor-container">
        <div class="editor-header">
            <a href="menu.php"><i class="fa-solid fa-arrow-left"></i></a>
            <h2>Add New Item</h2>
        </div>
        <form id="menu-form" action="action_additems.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="menu-id">

            <div class="form-group">
                <label for="menu-name" class="form-label">Item Name</label>
                <input type="text" name="name" id="menu-name" class="form-control" placeholder="Enter item name" required>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label for="menu-price" class="form-label">Price (₹)</label>
                    <input type="number" id="menu-price" name="price" class="form-control" min="0" step="0.01" placeholder="0" required>
                </div>
                <div class="form-group half">
                    <label for="menu-discount" class="form-label">Discount (%)</label>
                    <input type="number" id="menu-discount" name="discount" class="form-control" min="0" max="100" step="1" placeholder="0">
                </div>
            </div>

            <div class="price-display">
                <span class="original-price">₹<span id="original-price">0</span></span>
                <span>→</span>
                <span class="final-price">₹<span id="final-price">0</span></span>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label for="menu-category" class="form-label">Category</label>
                    <select id="menu-category" name="category" class="form-control" required>
                        <option value="" hidden disabled selected>Select Category</option>
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
                                echo '<option value="' . $category . '">' . $category . '</option>';
                            }
                        }
                        $stmt_1->close();
                        ?>
                    </select>
                </div>

                <div class="form-group half">
                    <label for="menu-status" class="form-label">Status</label>
                    <select id="menu-status" name="status" class="form-control" required>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="menu-description" class="form-label">Description</label>
                <textarea id="menu-description" name="description" class="form-control form-textarea" placeholder="Brief description of the item"></textarea>
            </div>

            <div class="form-group">
                <label for="menu-image" class="form-label">Item Image</label>
                <div class="file-input-wrapper">
                    <label for="menu-image" class="file-input-label">
                        <i class="fas fa-cloud-upload-alt"></i> Choose an image
                    </label>
                    <input type="file" id="menu-image" name="image" class="form-control" accept="image/*" required>
                </div>
                <div id="image-preview">
                    <img id="preview-image" src="" alt="Preview">
                    <div class="file-info" id="file-info"></div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-danger" id="cancel-btn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-success" id="save-btn">
                    <i class="fas fa-save"></i> Save Item
                </button>
            </div>
        </form>

    </div>

    <script>
        // Price calculation and display
        function calculatePrices() {
            const price = parseFloat(document.getElementById('menu-price').value) || 0;
            const discount = parseFloat(document.getElementById('menu-discount').value) || 0;

            const discountAmount = price * (discount / 100);
            const finalPrice = price - discountAmount;

            document.getElementById('original-price').textContent = price.toFixed(0);
            document.getElementById('final-price').textContent = finalPrice.toFixed(0);
        }

        document.getElementById('menu-price').addEventListener('input', calculatePrices);
        document.getElementById('menu-discount').addEventListener('input', calculatePrices);

        calculatePrices();

        // Enhanced image preview functionality
        document.getElementById('menu-image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewImage = document.getElementById('preview-image');
            const fileInfo = document.getElementById('file-info');
            const previewDiv = document.getElementById('image-preview');

            if (!file) {
                previewImage.style.display = 'none';
                fileInfo.style.display = 'none';
                previewDiv.innerHTML = '<span>No image selected</span>';
                return;
            }

            // Validate image type
            if (!file.type.match('image.*')) {
                alert('Please select an image file (JPEG, PNG, etc.)');
                this.value = '';
                previewImage.style.display = 'none';
                fileInfo.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                previewImage.src = event.target.result;
                previewImage.style.display = 'block';

                fileInfo.textContent = `${file.name} (${(file.size/1024).toFixed(2)} KB)`;
                fileInfo.style.display = 'block';

                previewDiv.style.borderColor = '#28a745';
                setTimeout(() => {
                    previewDiv.style.borderColor = '#e0e0e0';
                }, 1000);
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('cancel-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel? All changes will be lost.')) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirecting...';
                setTimeout(() => {
                    window.location.href = 'menu.php';
                }, 500);
            }
        });

        document.getElementById('menu-form').addEventListener('submit', function(e) {
            const saveBtn = document.getElementById('save-btn');
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            saveBtn.disabled = true;

            setTimeout(() => {
                alert('Item saved successfully!');
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Item';
                saveBtn.disabled = false;
            }, 1500);
        });
    </script>
</body>

</html>