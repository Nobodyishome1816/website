<?php

    // Start session to use session variables if needed
    session_start();

    // Include database connection
    include '../server/db_connect.php';

    // Get information from the form on the HTML page
    $taken_dose = $_POST['dose'];
    $staff_code = $_POST['staff_code'];
    $time = $_POST['time'];
    $date = $_POST['date'];
    $sid = $_POST['sid'];

    try {

        $sql = "SELECT takes_id from takes where student_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt -> bindParam(1,$sid);

        $stmt->execute();

        $result = $stmt->fetch();

        $tid = $result['takes_id'];

        // Combine the date and time into a single string
        $date_time_str = $date . ' ' . $time;

        // Convert the combined date and time string to a Unix timestamp (epoch time)
        $date_time_epoch = strtotime($date_time_str);

        // Prepare SQL statement to insert information into the 'administer' table
        $sql = "INSERT INTO administer (staff_code, date_time, dose_given, takes_id) VALUES (?, ?, ?,?)";
        $stmt = $conn->prepare($sql);

        // Bind parameters to prevent SQL injection
        $stmt->bindParam(1, $staff_code);
        $stmt->bindParam(2, $date_time_epoch); // Use the Unix timestamp here
        $stmt->bindParam(3, $taken_dose);
        $stmt->bindParam(4, $tid);

        // Execute the statement
        if($stmt->execute()) {
            echo "Data successfully inserted!";
            echo "";
        } else {
            echo "Error inserting data.";
            echo "";
        }

        // Dose subtracted from takes table

        // Prepare sql statement
        $sql = "SELECT current_dose FROM takes WHERE takes_id = ?";
        $stmt = $conn->prepare($sql);

        // Bind parameters to prevent SQL injection
        $stmt->bindParam(1,$tid);

        $stmt->execute();

        $result = $stmt->fetch();

        $new_dose = $result['current_dose'] - $taken_dose;

        // Update sql table
        $sql = "UPDATE takes SET current_dose = ? WHERE takes_id = ?";
        $stmt = $conn->prepare($sql);

        // Bind parameters to prevent SQL injection
        $stmt->bindParam(1,$new_dose);
        $stmt->bindParam(2,$tid);

        // Execute the statement
        if($stmt->execute()){
            echo "Data successfully updated!";
        }else{
            echo "Error updating data.";
        };


    } catch (PDOException $e) {
        // Handle any errors
        echo "Error: " . $e->getMessage();
    }

?>