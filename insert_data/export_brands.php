<?php
session_start();
include "../server/db_connect.php";
include "../audit-log/audit-log.php";

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to export as CSV
function exportCSV($conn) {
    $times = date("U");  // gives the int of number of seconds
    $formatted_time = date("Y-m-d H-i-s", $times); //
    $filename = "$formatted_time brands_export.csv";
    $output = fopen("php://output", "w");

    // Send headers for file download
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");

    // Write the column headers
    fputcsv($output, ["brand_name"]);

    // Fetch and write the rows
    $stmt = $conn->query("SELECT brand_name FROM brand");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Function to export as Excel-compatible HTML
function exportExcel($conn) {
    $times = date("U");  // gives the int of number of seconds
    $formatted_time = date("Y-m-d H-i-s", $times); //
    $filename = "$formatted_time brands_export.xls";

    // Send headers for file download
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=$filename");

    // Start the table
    echo "<table border='1'>";
    echo "<tr><th>Brand ID</th><th>Brand Name</th></tr>";

    // Fetch and write the rows
    $stmt = $conn->query("SELECT * FROM brand");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["export_brands"])) {
        $exportType = $_POST["export_brands"];
        if ($exportType === "csv") {
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $staff_id = $_SESSION["staff_id"];
            $action = "All brands were exported via CSV";

            logAction($conn, $staff_id, $action);
            exportCSV($conn);
        } elseif ($exportType === "excel") {
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $staff_id = $_SESSION["staff_id"];
            $action = "All brands were exported via Excel";

            logAction($conn, $staff_id, $action);
            exportExcel($conn);
        } else {
            echo "Invalid export type.";
        }
    }
}
?>
