function openModal() {
        document.getElementById("addTaskModal").style.display = "flex";
    }

    function closeModal() {
        document.getElementById('addTaskModal').style.display = 'none';
    }

    function openEditModal() {
        document.getElementById('editTaskModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editTaskModal').style.display = 'none';
    }

    function editTask(task) {
        document.getElementById('editTaskID').value = task.Task_ID;
        document.getElementById('editTaskTitle').value = task.Task_Title;
        document.getElementById('editTaskDescription').value = task.Task_Description;
        document.getElementById('editTaskDeadline').value = new Date(task.Task_Deadline).toISOString().slice(0,16);

        // Fetch staff list via AJAX and check assigned staff
        fetch('get_staff_list.php?task_id=' + task.Task_ID)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editStaffAssignment').innerHTML = html;
                openEditModal();
            });
    }