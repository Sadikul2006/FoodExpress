<?php
session_start();
include "database_connection.php";

$user_id = intval($_SESSION['user_id']);


// delete address---->
if (isset($_POST['delete_id']) && isset($_SESSION['user_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $user_id = intval($_SESSION['user_id']);

    $stmt = $conn->prepare("DELETE FROM address WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    exit;
}



if (isset($_POST['address_id']) && isset($_SESSION['user_id'])) {
    $address_id = intval($_POST['address_id']);
    $user_id = intval($_SESSION['user_id']);

    // address default reset
    $sql1 = "UPDATE address SET is_default = 0 WHERE user_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();

    // default set
    $sql2 = "UPDATE address SET is_default = 1 WHERE id = ? AND user_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ii", $address_id, $user_id);
    $stmt2->execute();

    if ($stmt2->affected_rows > 0) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt1->close();
    $stmt2->close();
    $conn->close();
}


if (isset($_POST['display'])) {
    $sql = "SELECT * FROM address WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
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
    } else {
        echo "<p>No addresses found.</p>";
    }
    echo '
        <a href="address.php">
            <div class="add-address">
                <i class="fas fa-plus"></i>
                <span>Add New Address</span>
            </div>
        </a>';

    $stmt->close();
    $conn->close();
}

?>
