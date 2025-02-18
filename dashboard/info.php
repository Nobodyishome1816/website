<?php

session_start();

// Check for valid session and cookie
if (!isset($_SESSION['ssnlogin']) || !isset($_COOKIE['cookies_and_cream'])) {
    header("Location: ../index.html");
    exit();
}

include "../server/db_connect.php";

// Check if `takes_id` is provided
if (isset($_POST['takes_id'])) {
    $takes_id = $_POST['takes_id'];

    // Fetch the current notes from the database
    $sql = "SELECT notes FROM takes WHERE takes_id = :takes_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':takes_id', $takes_id, PDO::PARAM_INT);
    $stmt->execute();
    $notes = $stmt->fetch(PDO::FETCH_ASSOC)['notes'] ?? '';
} else {
    die("Invalid request.");
}

// Handle form submission to update notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updated_notes'])) {
    $updated_notes = $_POST['updated_notes'];

    $update_sql = "UPDATE takes SET notes = :notes WHERE takes_id = :takes_id";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindParam(':notes', $updated_notes, PDO::PARAM_STR);
    $update_stmt->bindParam(':takes_id', $takes_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        $message = "Notes updated successfully!";
        $notes = $updated_notes; // Update notes for display
    } else {
        $message = "Error updating notes. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hours Tracking - Edit Info</title>
    <link rel="stylesheet" href="../assets/style/style.css">
</head>
<body>
<div class="full_page_styling">
<div>
        <ul class="nav_bar">
            <div class="nav_left">
                <li class="navbar_li"><a href="../dashboard/dashboard.php">Home</a></li>
                <li class="navbar_li"><a href="../insert_data/insert_data_home.php">Insert Data</a></li>
                <li class="navbar_li"><a href="../bigtable/bigtable.php">Student Medication</a></li>
<!--                <li class="navbar_li"><a href="../administer/administer_form.php">Administer Medication</a></li>-->
                <li class="navbar_li"><a href="../log/log_form.php">Create Notes</a></li>
                <li class="navbar_li"><a href="../whole_school/whole_school_table.php">Whole School Medication</a></li>
                <li class="navbar_li"><a href="../student_profile/student_profile.php">Student Profile</a></li>
                <li class="navbar_li"><a href="../edit_details/student_table.php">Student Management</a></li>
                <li class="navbar_li"><a href="../log-new-med/log_new_med.php">Add New Med</a></li>
            </div>
            <div class="nav_left">
                <li class="navbar_li"><a href="../logout.php">Logout</a></li>
            </div>
        </ul>
    </div>
    <h1>Edit Notes</h1>

    <!-- Display any success/error messages -->
    <?php if (isset($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Form to Edit Notes -->
    <form action="info.php" method="post">
        <input type="hidden" name="takes_id" value="<?php echo htmlspecialchars($takes_id); ?>">
        <div class='text-element'>Provide and notes about the student</div>
        <div class='text-element-faded'>This is not required</div>
        <textarea class="text_area" name="updated_notes"><?php echo htmlspecialchars($notes); ?></textarea>
        <br><br>
        <button class="submit" type="submit">Save Changes</button>
    </form>

    <br>
    <a class="back_link" href="dashboard.php">< <u>Back</u></a>
</div>
</body>
</html>
