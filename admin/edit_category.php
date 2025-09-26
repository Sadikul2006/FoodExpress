<?php
session_start();
include 'database_connection.php';

$restaurant_id = $_SESSION['admin_id'];

if (isset($_GET['id'])) {
    $categoryId = htmlspecialchars(intval($_GET['id']));
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND restaurant_id = ?");
    $stmt->bind_param("ii", $categoryId, $restaurant_id);
    $stmt->execute();
    $stmt->close();
    header("Location: edit_category.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $addNewCategory = htmlspecialchars(trim($_POST['addNewCategory']));

    if (!empty($addNewCategory)) {
        $stmt = $conn->prepare("INSERT INTO categories (restaurant_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $restaurant_id, $addNewCategory);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: edit_category.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Category Editor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style/edit_category.css">
</head>

<body>
    <div class="editor-container">
        <div class="editor-header">
            <h1><i class="fas fa-tags"></i> Food Categories</h1>
            <a href="menu.php"><i id="close" class="fa-solid fa-xmark"></i></a>
        </div>

        <div class="editor-body">
            <form action="" method="POST">
                <div class="input-group">
                    <input type="text" name="addNewCategory" id="categoryInput" placeholder="Add new category..." required>
                    <button class="addBtn" type="submit"><i class="fas fa-plus-circle" id="addCategoryBtn"></i></button>
                </div>
            </form>

            <?php
            $sql_1 = "SELECT * FROM categories WHERE restaurant_id = ?";
            $stmt_1 = $conn->prepare($sql_1);
            $stmt_1->bind_param("i", $restaurant_id);
            $stmt_1->execute();
            $result_1 = $stmt_1->get_result();

            if ($result_1 && $result_1->num_rows > 0) {
                while ($row = $result_1->fetch_assoc()) {
                    echo '
                    <div class="category-item">
                        <span class="category-name">' . htmlspecialchars($row['name']) . '</span>
                        <a href="?id=' . $row['id'] . '" onclick="return confirm(\'Are you sure?\')">
                            <button class="delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </a>
                    </div>';
                }
            } else {
                echo '
                <div class="category-list" id="categoryList">
                    <div class="empty-state" id="emptyState">
                        <i class="fas fa-tag"></i>
                        <p>No categories yet. Add some using the input above.</p>
                    </div>
                </div>';
            }

            $stmt_1->close();
            $conn->close();
            ?>
        </div>
    </div>
</body>

</html>