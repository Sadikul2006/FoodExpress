<?php
session_start();
include 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$name = $phone = $street = $city = $address_type = "";
$edit_id = 0;
$error = "";

// if(isset($_GET['default_id'])) {
//     $default_id = intval($_GET['default_id']);
    
//     $stmt = $conn->prepare("UPDATE address SET is_default = 0 WHERE user_id = ?");
//     $stmt->bind_param("i", $user_id);
//     $stmt->execute();
//     $stmt->close();
    
//     $stmt = $conn->prepare("UPDATE address SET is_default = 1 WHERE id = ? AND user_id = ?");
//     $stmt->bind_param("ii", $default_id, $user_id);
// 	$stmt->execute();
//     $stmt->close();
//     header("Location: user_info.php");
//     exit();
// }

// ---------- Edit mode data load ----------
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM address WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $name         = htmlspecialchars($row['name']);
        $phone        = htmlspecialchars($row['phone']);
        $street       = htmlspecialchars($row['street']);
        $city         = htmlspecialchars($row['city']);
        $address_type = htmlspecialchars($row['address_type']);
    } else {
        $_SESSION['error'] = "Address not found!";
        header("Location: user_info.php");
        exit();
    }
    $stmt->close();
}

// ---------- Add / Update ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name         = trim($_POST['name']);
    $phone        = trim($_POST['phone']);
    $street       = trim($_POST['street']);
    $city         = trim($_POST['city']);
    $address_type = isset($_POST['address_type']) ? trim($_POST['address_type']) : "";
    $edit_id      = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    
    // Validation
    if (empty($name) || empty($phone) || empty($street) || empty($city) || empty($address_type)) {
        $error = "All fields are required!";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $error = "Please enter a valid 10-digit phone number!";
    } else {
        if ($edit_id > 0) {
            // ------- Update Query -------
            $stmt = $conn->prepare("UPDATE address SET name=?, phone=?, street=?, city=?, address_type=? WHERE id=? AND user_id=?");
            $stmt->bind_param("sssssii", $name, $phone, $street, $city, $address_type, $edit_id, $user_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Address updated successfully!";
                $stmt->close();
                header("Location: user_info.php");
                exit();
            } else {
                $error = "Failed to update address: " . $conn->error;
            }
        } else {
            // ------- Insert Query -------
            $check = $conn->prepare("SELECT COUNT(*) as cnt FROM address WHERE user_id = ?");
            $check->bind_param("i", $user_id);
            $check->execute();
            $res = $check->get_result()->fetch_assoc();
            $check->close();

            $is_default = ($res['cnt'] == 0) ? 1 : 0;

            $stmt = $conn->prepare("INSERT INTO address (user_id, name, phone, street, city, address_type, is_default) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssi", $user_id, $name, $phone, $street, $city, $address_type, $is_default);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Address added successfully!";
                $stmt->close();
                header("Location: user_info.php");
                exit();
            } else {
                $error = "Failed to add address: " . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_id > 0 ? 'Edit' : 'Add'; ?> Address</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="app_style/address.css">
</head>

<body>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <i class="fa-solid fa-circle-check"></i>
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <div class="arrow">
            <a href="user_info.php"><i id="arroBtn" class="fa-solid fa-arrow-left"></i></a>
            <h2><?php echo $edit_id > 0 ? 'Edit' : 'Add'; ?> Delivery Address</h2>
        </div>
        <form action="" method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">

            <div class="form-group input-icon">
                <label for="user_name">Full Name</label>
                <input type="text" id="user_name" name="name" value="<?php echo $name; ?>" required placeholder="Enter your full name">
                <i class="fas fa-user"></i>
            </div>

            <div class="form-group input-icon">
                <label for="phone">Mobile Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>" required placeholder="Enter 10-digit mobile number">
                <i class="fas fa-mobile-alt"></i>
                <small id="phoneError" style="color: red; display: none;">Please enter a valid 10-digit phone number</small>
            </div>

            <div class="form-group input-icon">
                <label for="street">Complete Address</label>
                <input type="text" id="street" name="street" value="<?php echo $street; ?>" required placeholder="House no, Building, Street, Area">
                <i class="fas fa-map-marked-alt"></i>
            </div>

            <div class="form-group input-icon">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?php echo $city; ?>" required placeholder="Enter your city">
                <i class="fas fa-city"></i>
            </div>

            <div class="form-group">
                <label>Address Type</label>
                <div class="address-type-options">
                    <div class="address-type-option">
                        <input type="radio" id="home" name="address_type" value="Home" <?php echo ($address_type == "Home") ? 'checked' : ''; ?> required>
                        <label for="home">
                            <span><i class="fas fa-home"></i> Home</span>
                        </label>
                    </div>
                    <div class="address-type-option">
                        <input type="radio" id="work" name="address_type" value="Work" <?php echo ($address_type == "Work") ? 'checked' : ''; ?> required>
                        <label for="work">
                            <span><i class="fas fa-briefcase"></i> Work</span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit">
                <i class="fas fa-check-circle"></i> Save Address
            </button>
        </form>
    </div>

    <script>
        function validateForm() {
            const phone = document.getElementById('phone').value;
            const phoneError = document.getElementById('phoneError');
            const phonePattern = /^[0-9]{10}$/;
            
            if (!phonePattern.test(phone)) {
                phoneError.style.display = 'block';
                return false;
            } else {
                phoneError.style.display = 'none';
                return true;
            }
        }
        
        // Live validation for phone field
        document.getElementById('phone').addEventListener('input', function() {
            const phone = this.value;
            const phoneError = document.getElementById('phoneError');
            const phonePattern = /^[0-9]{10}$/;
            
            if (phone && !phonePattern.test(phone)) {
                phoneError.style.display = 'block';
            } else {
                phoneError.style.display = 'none';
            }
        });
    </script>
</body>

</html>