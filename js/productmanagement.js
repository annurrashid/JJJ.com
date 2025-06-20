document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();

        // Set values
        document.getElementById('editProductId').value = this.dataset.id;
        document.getElementById('editProductName').value = this.dataset.name;
        document.getElementById('editProductDescription').value = this.dataset.desc;
        document.getElementById('editProductPrice').value = this.dataset.price;
        document.getElementById('editProductCategory').value = this.dataset.category;
        document.getElementById('editProductStatus').value = this.dataset.status;

        // Set image
        const image = this.dataset.image ? 'product_image/' + this.dataset.image : '';
        document.getElementById('currentImagePreview').src = image;

        // Show modal
        document.getElementById('editModal').style.display = 'flex';
    });
});

function openEditModal() {
  document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}


// Optional: close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

document.getElementById('productImage').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
    document.getElementById('fileInputText').textContent = fileName;
});

// For the edit modal 
document.addEventListener('DOMContentLoaded', function() {
    const editFileInput = document.getElementById('editProductImage');
    if (editFileInput) {
        editFileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            document.getElementById('editFileInputText').textContent = fileName;
        });
    }
});

document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'var(--red)';
            isValid = false;
        } else {
            field.style.borderColor = 'var(--gray)';
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill all required fields');
    }
});

// File input display
document.getElementById('productImage').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose file';
    document.getElementById('fileLabel').textContent = fileName;
});

// Form validation
document.getElementById('productForm').addEventListener('submit', function(e) {
    let formValid = true;
    const requiredFields = this.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value) {
            field.classList.add('is-invalid');
            formValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!formValid) {
        e.preventDefault();
        // Scroll to first invalid field
        const firstInvalid = this.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

// Real-time validation
document.querySelectorAll('#productForm [required]').forEach(field => {
    field.addEventListener('input', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
        }
    });
});