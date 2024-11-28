<link rel="stylesheet" href="../style.css">
<body>
<div class="container">

    <!-- universal nav bar -->
    <div class="navbar">
        <img id="logo" src="../assets/UTCLeeds.svg" alt="UTC Leeds">
        <h1 id="med_tracker">Med Tracker</h1>
        <ul>
            <li><a href="../dashboard/dashboard.php">Home</a></li>
            <li><a href="../insert_data/insert_data.php">Insert Data</a></li>
            <li><a href="../bigtable/bigtable.php">Student Medication</a></li>
            <li><a href="../administer/administer.html">Administer Medication</a></li>
            <li><a href="../whole_school/whole_school.php">Whole School Medication</a></li>
            <li class="logout"><a>Logout</a></li>
        </ul>
    </div>

    <div id="search-bar">
        <form method="GET" action="">
            <input
                type="text"
                name="search"
                placeholder="Search by student name or year"
                value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>"
            >
            <button type="submit">Search</button>
        </form>
    </div>

    <div id="progress-year">
        <form method="GET" action="">
            <label for="year">Select Year Group to Progress:</label>
            <input type="text" id="year" name="year" placeholder="Enter Year (e.g., 11)" required>
            <button type="submit" name="progress">Progress Year Group</button>
        </form>
    </div>

    <?php
    // Include the database connection file
    include "../server/db_connect.php";

    // Check if the progress button is clicked
    if (isset($_GET['progress']) && isset($_GET['year'])) {
        $selected_year = trim($_GET['year']);

        try {
            // Fetch all students in the selected year group
            $sql = "SELECT * FROM students WHERE year = :year";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':year', $selected_year, PDO::PARAM_STR);
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($students) {
                echo "<form method='POST' action=''>";
                echo "<h2>Progress Year Group $selected_year</h2>";
                echo "<table>";
                echo "<tr>";
                foreach (array_keys($students[0]) as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "<th>Progress</th>";
                echo "</tr>";

                foreach ($students as $student) {
                    echo "<tr>";
                    foreach ($student as $key => $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "<td>
                            <input type='checkbox' name='progress_ids[]' value='" . htmlspecialchars($student['student_id']) . "' checked>
                          </td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<button type='submit' name='finalize_progress'>Finalise Progress</button>";
                echo "</form>";
            } else {
                echo "<p>No students found in Year $selected_year.</p>";
            }
        } catch (PDOException $e) {
            die("<p class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>");
        }
    }

    // Handle the final progress submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalize_progress']) && isset($_POST['progress_ids'])) {
        $progress_ids = $_POST['progress_ids'];

        try {
            // Update the year group for selected students
            $update_sql = "UPDATE students SET year = year + 1 WHERE student_id = :student_id";
            $update_stmt = $conn->prepare($update_sql);

            foreach ($progress_ids as $id) {
                $update_stmt->bindParam(':student_id', $id, PDO::PARAM_INT);
                $update_stmt->execute();
            }

            echo "<p class='success'>Year group progression completed successfully.</p>";
        } catch (PDOException $e) {
            die("<p class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>");
        }
    }

    // Default student table display logic
    $results_per_page = 15;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $start_from = ($page - 1) * $results_per_page;
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

    try {
        $total_sql = "SELECT COUNT(*) AS total_records FROM students WHERE CONCAT(first_name, ' ', last_name) LIKE :search OR year LIKE :search";
        $total_stmt = $conn->prepare($total_sql);
        $search_param = '%' . $search_term . '%';
        $total_stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
        $total_stmt->execute();
        $total_records = $total_stmt->fetch(PDO::FETCH_ASSOC)['total_records'];
        $total_pages = ceil($total_records / $results_per_page);

        $sql = "SELECT * FROM students WHERE CONCAT(first_name, ' ', last_name) LIKE :search OR year LIKE :search LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $results_per_page, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $start_from, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<div id='bigt'>";
        if ($results) {
            echo "<table>";
            echo "<tr>";
            foreach (array_keys($results[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "<th>Actions</th>";
            echo "</tr>";

            foreach ($results as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "<td>
                        <form method='GET' action='edit_student.php'>
                            <input type='hidden' name='student_id' value='" . htmlspecialchars($row['student_id']) . "'>
                            <button type='submit'>Edit</button>
                        </form>
                      </td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No records found.";
        }
        echo "</div>";

        echo "<div class='pagination'>";
        if ($page > 1) {
            echo "<a href='?search=" . urlencode($search_term) . "&page=" . ($page - 1) . "'>Previous</a>";
        }
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $page) {
                echo "<span>$i</span>";
            } else {
                echo "<a href='?search=" . urlencode($search_term) . "&page=$i'>$i</a>";
            }
        }
        if ($page < $total_pages) {
            echo "<a href='?search=" . urlencode($search_term) . "&page=" . ($page + 1) . "'>Next</a>";
        }
        echo "</div>";
    } catch (PDOException $e) {
        die("<p class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>");
    }

    ?>
</div>
</body>
