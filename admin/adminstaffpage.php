<?php
require 'config.php';

function executeQuery($conn, $sql, $params = array()) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    return $stmt;
}

$staffQuery = "SELECT staff_id, first_name, last_name, email, phone, role, hire_date, salary, is_active FROM Staff ORDER BY staff_id";
$staffsResult = executeQuery($conn, $staffQuery);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Crumby Cafe</title>
    <link rel="stylesheet" href="admindashboard.css" />
</head>

<body>
    <nav>
        <div class="sidebar">
            <div class="sidebar-header">Admin Dashboard</div>
            <ul class="sidebar-menu">
                <li><a href="admindashboard.php">Dashboard</a></li>
                <li><a href="adminstaffpage.php">Staff</a></li>
                <li><a href="adminproduct.php">Product</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Staff</h1>
            <button class="logout-btn" onclick="window.location.href='index.php'">Logout</button>
        </div>

        <div class="stats-container">
            <div class="card">
                <h2>Staff info</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Hire Date</th>
                            <th>Salary</th>
                            <th>Is Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($staff = sqlsrv_fetch_array($staffsResult, SQLSRV_FETCH_ASSOC)): ?>
                            <tr>
                                <td>#<?php echo $staff['staff_id']; ?></td>
                                <td><?php echo htmlspecialchars($staff['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($staff['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                <td><?php echo htmlspecialchars($staff['phone']); ?></td>
                                <td><?php echo htmlspecialchars($staff['role']); ?></td>
                                <td>
                                    <?php 
                                        if ($staff['hire_date'] instanceof DateTime) {
                                            echo $staff['hire_date']->format('Y-m-d');
                                        } else {
                                            echo $staff['hire_date'];
                                        }
                                    ?>
                                </td>
                                <td><?php echo number_format($staff['salary'], 2); ?></td>
                                <td><?php echo $staff['is_active'] ? 'Yes' : 'No'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
