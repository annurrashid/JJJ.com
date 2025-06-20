<?php
include 'db.php';

$taskId = intval($_GET['task_id']);
$assignedResult = $conn->query("SELECT Staff_ID FROM staff_tasks WHERE Task_ID = $taskId");
$assignedStaff = [];

while ($row = $assignedResult->fetch_assoc()) {
    $assignedStaff[] = $row['Staff_ID'];
}

$staffResult = $conn->query("SELECT * FROM staff WHERE Staff_Status = 'active' ORDER BY Staff_Name");

while ($staff = $staffResult->fetch_assoc()) {
    $staffId = $staff['Staff_ID'];
    $checked = in_array($staffId, $assignedStaff) ? 'checked' : '';
    echo "<div>
        <input type='checkbox' name='assignedStaff[]' value='$staffId' id='edit_staff_$staffId' $checked>
        <label for='edit_staff_$staffId'>" . htmlspecialchars($staff['Staff_Name']) . "</label>
    </div>";
}
?>
