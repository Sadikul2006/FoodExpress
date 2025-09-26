<?php
session_start();
include 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login_register.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT name, email, phone, image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_name = htmlspecialchars($row['name'] ?? '');
    $user_email = htmlspecialchars($row['email'] ?? '');
    $user_phone = htmlspecialchars($row['phone'] ?? '');
    $profile_image = !empty($row['image']) ? htmlspecialchars($row['image']) : 'images/user_profile.jpg';
}
$stmt->close();

// profile image uploded
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image']['name']);
    $uploadFileDir = 'admin/uploads/';
    $dest_path = $uploadFileDir . $fileName;

    $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($fileExt, $allowedExt)) {
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Database update
            $stmt = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $dest_path, $user_id);
            $stmt->execute();
            $stmt->close();

            // Update session
            $_SESSION['image'] = $dest_path;
        } else {
            echo "File move failed.";
        }
    } else {
        echo "Only JPG, JPEG, PNG, GIF allowed.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - FoodExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="app_style/user_info.css">
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="title">Account Settings</h1>
            <button class="icon-btn">
                <i class="fas fa-bell"></i>
            </button>
        </div>
    </div>

    <div class="container">

        <!-- Profile Section -->
        <div class="profile-section">
            <img id="profileImg" src="<?php echo $profile_image ?: 'default.png'; ?>" alt="Profile" class="profile-img">
            <h2 class="profile-name" id="user_name"><?php echo htmlspecialchars($user_name); ?></h2>
            <p class="profile-email"><?php echo htmlspecialchars($user_email); ?></p>

            <form id="uploadForm" enctype="multipart/form-data" style="margin-top: 10px;">
                <input type="file" id="fileInput" name="image" accept="image/*" style="display: none;" required>

                <button type="button" id="editBtn"
                    style="background: #ff7e5f; color: white; border: none; padding: 8px 20px; border-radius: 20px; font-size: 14px; cursor: pointer;">
                    Edit Profile
                </button>
            </form>
        </div>


        <!-- Account Settings -->
        <div class="settings-group">
            <div class="settings-item" id="personal-info-item">
                <div class="icon-container">
                    <i class="fas fa-user settings-icon"></i>
                </div>
                <div class="settings-content">
                    <h3 class="settings-title">Personal Information</h3>
                    <p class="settings-desc">Update your name and phone number</p>
                </div>
                <i class="fas fa-chevron-right arrow-icon" id="personal-info-arrow"></i>
            </div>

            <!-- Personal Info Dropdown -->
            <div class="personal-info-dropdown" id="personal-info-dropdown">
                <div class="info-section">
                    <form class="info-form" id="personalInfo">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <p><?php echo $user_email; ?></p>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Full Name</label>
                            <input type="text" id="name" name="user_name" value="<?php echo $user_name; ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="user_phone" value="<?php echo $user_phone; ?>">
                        </div>
                        <input type="hidden" name="update_profile" value="1">
                        <button type="submit" id="save" class="save-btn">Save Changes</button>
                    </form>
                </div>
            </div>

            <div class="settings-item" id="addresses-item">
                <div class="icon-container">
                    <i class="fas fa-map-marker-alt settings-icon"></i>
                </div>
                <div class="settings-content">
                    <h3 class="settings-title">Addresses</h3>
                    <p class="settings-desc">Manage your delivery addresses</p>
                </div>
                <i class="fas fa-chevron-right arrow-icon" id="address-arrow"></i>
            </div>

            <!-- Address Dropdown -->
            <div class="address-dropdown" id="address-dropdown">
                <div class="address-section">
                    <div class="address-cards" id="address-cards">
                        <?php
                        $sql_1 = "SELECT * FROM address WHERE user_id = ?";
                        $stmt_1 = $conn->prepare($sql_1);
                        $stmt_1->bind_param("i", $user_id);
                        $stmt_1->execute();
                        $result_1 = $stmt_1->get_result();

                        if ($result_1 && $result_1->num_rows > 0) {
                            while ($row = $result_1->fetch_assoc()) {
                                $defaultClass = ($row['is_default'] == 1) ? "default" : "";
                                $defaultText  = ($row['is_default'] == 1) ? "<span class='address-type'>Default</span>" : "";

                                echo '
                                <div class="address-card ' . $defaultClass . '">
                                    ' . $defaultText . '
                                    <h3>' . $row['address_type'] . '</h3>
                                    <p>' . $row['name'] . '</p>
                                    <p>' . $row['street'] . '</p>
                                    <p>' . $row['city'] . '</p>
                                    <p>' . $row['phone'] . '</p>
                                    <div class="address-actions">
                                        <a href="address.php?edit_id=' . $row['id'] . '" class="action-btn btn-edit">Edit</a>
                                        <button class="action-btn btn-delete" data-id="' . $row['id'] . '">Delete</button>';
                                if ($row['is_default'] == 0) {
                                    echo '<button class="action-btn btn-set-default" data-id="' . $row['id'] . '">Set Default</button>';
                                }
                                echo
                                '</div>
                                </div>';
                            }
                        }
                        $stmt_1->close();
                        $conn->close();
                        ?>

                        <a href="address.php">
                            <div class="add-address">
                                <i class="fas fa-plus"></i>
                                <span>Add New Address</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="settings-item">
                <div class="icon-container">
                    <i class="fas fa-lock settings-icon"></i>
                </div>
                <div class="settings-content">
                    <h3 class="settings-title">Password & Security</h3>
                    <p class="settings-desc">Change password and security settings</p>
                </div>
                <i class="fas fa-chevron-right arrow-icon"></i>
            </div>

            <!-- App Preferences -->
            <div class="settings-group">
                <div class="settings-item">
                    <div class="icon-container">
                        <i class="fas fa-bell settings-icon"></i>
                    </div>
                    <div class="settings-content">
                        <h3 class="settings-title">Notifications</h3>
                        <p class="settings-desc">Manage your notifications</p>
                    </div>
                    <i class="fas fa-chevron-right arrow-icon"></i>
                </div>

                <div class="settings-item">
                    <div class="icon-container">
                        <i class="fas fa-money-bill-wave settings-icon"></i>
                    </div>
                    <div class="settings-content">
                        <h3 class="settings-title">Payment Methods</h3>
                        <p class="settings-desc">Add or remove payment methods</p>
                    </div>
                    <i class="fas fa-chevron-right arrow-icon"></i>
                </div>
            </div>

            <!-- More Settings -->
            <!-- <div class="settings-group"> -->
                <div class="settings-item">
                    <div class="icon-container">
                        <i class="fas fa-moon settings-icon"></i>
                    </div>
                    <div class="settings-content">
                        <h3 class="settings-title">Dark Mode</h3>
                        <p class="settings-desc">Switch to dark theme</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="settings-item">
                    <div class="icon-container">
                        <i class="fas fa-shield-alt settings-icon"></i>
                    </div>
                    <div class="settings-content">
                        <h3 class="settings-title">Privacy Policy</h3>
                        <p class="settings-desc">How we handle your data</p>
                    </div>
                    <i class="fas fa-chevron-right arrow-icon"></i>
                </div>

                <div class="settings-item">
                    <div class="icon-container">
                        <i class="fas fa-question-circle settings-icon"></i>
                    </div>
                    <div class="settings-content">
                        <h3 class="settings-title">Help & Support</h3>
                        <p class="settings-desc">FAQs and contact support</p>
                    </div>
                    <i class="fas fa-chevron-right arrow-icon"></i>
                </div>
            </div>

            <!-- Logout Button -->
            <a href="user_login_register.php?logout">
                <button class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </a>

            <!-- Footer -->
            <div class="footer">
                <p>FoodExpress v2.3.1</p>
            </div>
        <!-- </div> -->

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            // profile image Uplode ----->
            $(document).ready(function() {
                $("#editBtn").click(function() {
                    $("#fileInput").click();
                });

                $("#fileInput").change(function() {
                    if (this.files.length > 0) {
                        var formData = new FormData($("#uploadForm")[0]);

                        $.ajax({
                            url: "fetch_user_info.php",
                            type: "POST",
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                let data = JSON.parse(response);
                                if (data.status === "success") {
                                    $("#profileImg").attr("src", data.new_image + "?t=" + new Date().getTime());
                                } else {
                                    alert(data.message);
                                }
                            },
                            error: function() {
                                alert("Upload failed, try again!.");
                            }
                        });
                    }
                });
            });


            // Personal Info change using ajax
            $(document).ready(function() {
                $("#save").click(function(e) {
                    e.preventDefault();

                    $.ajax({
                        url: "fetch_user_info.php",
                        type: "POST",
                        data: {
                            name: $("#name").val(),
                            phone: $("#phone").val(),
                            update_profile: '1'
                        },
                        success: function(response) {
                            console.log(response);
                            $("#user_name").text($("#name").val());
                        },
                        error: function() {
                            alert("Error updating profile.");
                        }
                    });
                });
            });




            $(document).on("click", ".btn-delete", function(e) {
                e.preventDefault();

                let addressId = $(this).data("id");

                $.ajax({
                    url: "fetch_address.php",
                    type: "POST",
                    data: {
                        delete_id: addressId
                    },
                    success: function(response) {
                        if (response.trim() === "success") {
                            // reload list
                            $.ajax({
                                url: "fetch_address.php",
                                method: "POST",
                                data: {
                                    display: true
                                },
                                success: function(data) {
                                    $("#address-cards").html(data);
                                }
                            });
                        } else {
                            alert("Delete failed!");
                        }
                    },
                    error: function() {
                        alert("Server error!");
                    }
                });
            });






            // $(document).ready(function() {
            $(document).on("click", ".btn-set-default", function(e) {
                e.preventDefault();

                let addressId = $(this).data("id");

                $.ajax({
                    url: "fetch_address.php",
                    type: "POST",
                    data: {
                        address_id: addressId
                    },
                    success: function(response) {
                        if (response.trim() === "success") {
                            // Reload address list
                            $.ajax({
                                url: "fetch_address.php",
                                method: "POST",
                                data: {
                                    display: true
                                },
                                success: function(data) {
                                    $("#address-cards").html(data);
                                }
                            });
                        } else {
                            alert("Something went wrong!");
                        }
                    },
                    error: function() {
                        alert("Error connecting to server!");
                    }
                });
            });
        </script>










        <script>
            // Personal Info dropdown functionality
            const personalInfoItem = document.getElementById('personal-info-item');
            const personalInfoDropdown = document.getElementById('personal-info-dropdown');
            const personalInfoArrow = document.getElementById('personal-info-arrow');

            personalInfoItem.addEventListener('click', function() {
                personalInfoDropdown.classList.toggle('active');

                if (personalInfoDropdown.classList.contains('active')) {
                    personalInfoArrow.classList.remove('fa-chevron-right');
                    personalInfoArrow.classList.add('fa-chevron-down');
                } else {
                    personalInfoArrow.classList.remove('fa-chevron-down');
                    personalInfoArrow.classList.add('fa-chevron-right');
                }
            });


            // Address dropdown functionality
            const addressesItem = document.getElementById('addresses-item');
            const addressDropdown = document.getElementById('address-dropdown');
            const addressArrow = document.getElementById('address-arrow');

            addressesItem.addEventListener('click', function() {
                addressDropdown.classList.toggle('active');

                // arrow change
                if (addressArrow.classList.contains('fa-chevron-right')) {
                    addressArrow.classList.remove('fa-chevron-right');
                    addressArrow.classList.add('fa-chevron-down');
                } else {
                    addressArrow.classList.remove('fa-chevron-down');
                    addressArrow.classList.add('fa-chevron-right');
                }
            });





            // // Simple toggle functionality
            // document.querySelectorAll('.settings-item').forEach(item => {
            //     item.addEventListener('click', function() {
            //         // Skip if the clicked element is the toggle switch
            //         if (!event.target.closest('.toggle-switch')) {
            //             alert('Opening: ' + this.querySelector('.settings-title').textContent);
            //         }
            //     });
            // });

            // Toggle switch functionality
            const toggleSwitch = document.querySelector('.toggle-switch input');
            toggleSwitch.addEventListener('change', function() {
                if (this.checked) {
                    document.body.style.backgroundColor = '#222';
                    document.body.style.color = '#fff';
                    document.querySelectorAll('.settings-group, .profile-section, .logout-btn').forEach(el => {
                        el.style.backgroundColor = '#333';
                        el.style.color = '#fff';
                    });
                } else {
                    document.body.style.backgroundColor = '#f8f9fa';
                    document.body.style.color = '#333';
                    document.querySelectorAll('.settings-group, .profile-section, .logout-btn').forEach(el => {
                        el.style.backgroundColor = '#fff';
                        el.style.color = '#333';
                    });
                }
            });

            // // Logout button
            // document.querySelector('.logout-btn').addEventListener('click', function() {
            //     if (confirm('Are you sure you want to logout?')) {
            //         alert('Logging out...');
            //     }
            // });
        </script>
</body>

</html>