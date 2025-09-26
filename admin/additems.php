<?php 
session_start();
include 'database_connection.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Item</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        #menu-form {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #menu-form h2 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 10px;
        }

        #menu-form h2::after {
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

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: var(--light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
            background-color: var(--white);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        #image-preview {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            min-height: 150px;
            background-color: var(--light);
            transition: all 0.3s ease;
            text-align: center;
            color: var(--gray);
        }

        #image-preview:hover {
            border-color: var(--primary);
        }

        #image-preview img {
            max-width: 50%;
            max-height: 100px;
            border-radius: 3px;
            margin-bottom: 5px;
            display: none;
        }

        #image-preview .file-info {
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            border: none;
        }

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        /* Custom file input */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .file-input-label {
            display: block;
            padding: 12px 15px;
            background-color: var(--light);
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background-color: #e9ecef;
            border-color: var(--primary);
        }

        .file-input-label i {
            margin-right: 8px;
            color: var(--primary);
        }

        /* Custom select arrow */
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 15px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #menu-form {
                padding: 20px;
            }

            #menu-form h2 {
                font-size: 1.5rem;
            }

            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        .editor-header {
            background: var(--primary);
            color: var(--white);
            padding: 20px;
            text-align: center;
        }

        .editor-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .editor-container {
            width: 100%;
            max-width: 500px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>

<body>
    <div class="editor-container">
        <div class="editor-header">
            <h1>Add New Item</h1>
        </div>
        <form id="menu-form" action="action_additems.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="menu-id">

            <div class="form-group">
                <label for="menu-name" class="form-label">Item Name</label>
                <input type="text" name="name" id="menu-name" class="form-control" placeholder="Enter item name" required>
            </div>

            <div class="form-group">
                <label for="menu-description" class="form-label">Description</label>
                <textarea id="menu-description" name="description" class="form-control form-textarea" placeholder="Brief description of the item"></textarea>
            </div>

            <div class="form-group">
                <label for="menu-price" class="form-label">Price (₹)</label>
                <input type="number" id="menu-price" name="price" class="form-control" min="0" step="0.01" placeholder="0.00" required>
            </div>

            <div class="form-group">
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

            <div class="form-group">
                <label for="menu-status" class="form-label">Status</label>
                <select id="menu-status" name="status" class="form-control" required>
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
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

                // Change border color when image is loaded
                previewDiv.style.borderColor = '#28a745';
                setTimeout(() => {
                    previewDiv.style.borderColor = '#e0e0e0';
                }, 1000);
            };
            reader.readAsDataURL(file);
        });

        // Cancel button functionality with animation
        document.getElementById('cancel-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel? All changes will be lost.')) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirecting...';
                setTimeout(() => {
                    window.location.href = 'menu.php';
                }, 500);
            }
        });

        // Form submission feedback
        document.getElementById('menu-form').addEventListener('submit', function(e) {
            const saveBtn = document.getElementById('save-btn');
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            saveBtn.disabled = true;

            // In a real app, you would handle the form submission with AJAX here
            // For demonstration, we'll just show a success message after 1.5s
            setTimeout(() => {
                alert('Item saved successfully!');
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Item';
                saveBtn.disabled = false;
            }, 1500);
        });
    </script>
</body>

</html>