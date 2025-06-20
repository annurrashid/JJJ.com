// Modal open/close logic
        function openModal() {
            document.getElementById("addStaffModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("addStaffModal").style.display = "none";
        }
        
        function openEditModal(id, name, salary, phone, email, position, password, status) {
            document.getElementById("editStaffID").value = id;
            document.getElementById("editStaffName").value = name;
            document.getElementById("editStaffSalary").value = salary;
            document.getElementById("editStaffPhonenum").value = phone;
            document.getElementById("editStaffEmail").value = email;
            document.getElementById("editStaffPosition").value = position;
            document.getElementById("editStaffPassword").value = password;
            document.getElementById("editStaffStatus").value = status;
            document.getElementById("editStaffModal").style.display = "flex";
        }
        
        function closeEditModal() {
            document.getElementById("editStaffModal").style.display = "none";
        }