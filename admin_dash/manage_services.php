<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Database connection
require_once '../connect.php';

// Get user name from database
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name FROM user WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);
$name = $user['first_name'];

// Add CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all services
$sql = "SELECT id, name, description, image_path, price FROM service ORDER BY name";
$services = mysqli_query($conn, $sql);

if (!$services) {
    die("Error fetching services: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Services - Admin Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Belleza&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="admin.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary" style="font-family: 'Belleza', sans-serif;">Admin Panel</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <i class="fas fa-user-shield fa-2x"></i>
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0"><?php echo htmlspecialchars($name); ?></h6>
                        <span>Admin</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="../index.php" class="nav-item nav-link"><i class="fa fa-home me-2"></i>Bellezza</a>
                    <a href="admin.php" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="manage_users.php" class="nav-item nav-link"><i class="fa fa-users me-2"></i>Manage Users</a>
                    <a href="manage_appointments.php" class="nav-item nav-link"><i class="fa fa-calendar-check me-2"></i>Appointments</a>
                    <a href="manage_services.php" class="nav-item nav-link active"><i class="fa fa-cut me-2"></i>Services</a>
                    <a href="manage_staff.php" class="nav-item nav-link"><i class="fa fa-user-tie me-2"></i>Staff</a>
                    <a href="view_feedback.php" class="nav-item nav-link"><i class="fa fa-comments me-2"></i>View Feedback</a>
                    <a href="reports.php" class="nav-item nav-link"><i class="fa fa-chart-bar me-2"></i>Reports</a>
                    <a href="settings.php" class="nav-item nav-link"><i class="fa fa-cog me-2"></i>Settings</a>
                    <a href="../logout.php" class="nav-item nav-link"><i class="fa fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield fa-2x me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex"><?php echo htmlspecialchars($name); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="../profile.php" class="dropdown-item">My Profile</a>
                            <a href="settings.php" class="dropdown-item">Settings</a>
                            <a href="../logout.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Services Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="mb-0">Manage Services</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                            <i class="fa fa-plus me-2"></i>Add New Service
                        </button>
                    </div>
                    
                    <!-- Hair Services -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Hair Services</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $hair_sql = "SELECT id, name, description, image_path, price, category FROM service WHERE category = 'hair' ORDER BY name";
                                    $hair_services = mysqli_query($conn, $hair_sql);
                                    while ($service = mysqli_fetch_assoc($hair_services)): 
                                    ?>
                                    <tr>
                                        <td><?php echo isset($service['name']) ? htmlspecialchars($service['name']) : 'N/A'; ?></td>
                                        <td><span class="badge bg-primary">Hair Service</span></td>
                                        <td><?php echo isset($service['description']) ? htmlspecialchars($service['description']) : 'N/A'; ?></td>
                                        <td><?php echo isset($service['price']) ? htmlspecialchars($service['price']) : '0.00'; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary" onclick="editService(<?php echo $service['id']; ?>)" 
                                                        title="Edit Service" data-bs-toggle="tooltip">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteService(<?php echo $service['id']; ?>)" 
                                                        title="Delete Service" data-bs-toggle="tooltip">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Skin Services -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Skin Services</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $skin_sql = "SELECT id, name, description, image_path, price, category FROM service WHERE category = 'skin' ORDER BY name";
                                    $skin_services = mysqli_query($conn, $skin_sql);
                                    while ($service = mysqli_fetch_assoc($skin_services)): 
                                    ?>
                                    <tr>
                                        <td><?php echo isset($service['name']) ? htmlspecialchars($service['name']) : 'N/A'; ?></td>
                                        <td><span class="badge bg-success">Skin Service</span></td>
                                        <td><?php echo isset($service['description']) ? htmlspecialchars($service['description']) : 'N/A'; ?></td>
                                        <td><?php echo isset($service['price']) ? htmlspecialchars($service['price']) : '0.00'; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary" onclick="editService(<?php echo $service['id']; ?>)" 
                                                        title="Edit Service" data-bs-toggle="tooltip">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteService(<?php echo $service['id']; ?>)" 
                                                        title="Delete Service" data-bs-toggle="tooltip">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Makeup Services -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Makeup Services</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $makeup_sql = "SELECT id, name, description, image_path, price, category FROM service WHERE category = 'makeup' ORDER BY name";
                                    $makeup_services = mysqli_query($conn, $makeup_sql);
                                    while ($service = mysqli_fetch_assoc($makeup_services)): 
                                    ?>
                                    <tr>
                                        <td><?php echo isset($service['name']) ? htmlspecialchars($service['name']) : 'N/A'; ?></td>
                                        <td><span class="badge bg-info">Makeup Service</span></td>
                                        <td><?php echo isset($service['description']) ? htmlspecialchars($service['description']) : 'N/A'; ?></td>
                                        <td><?php echo isset($service['price']) ? htmlspecialchars($service['price']) : '0.00'; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary" onclick="editService(<?php echo $service['id']; ?>)" 
                                                        title="Edit Service" data-bs-toggle="tooltip">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteService(<?php echo $service['id']; ?>)" 
                                                        title="Delete Service" data-bs-toggle="tooltip">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Services End -->
        </div>
        <!-- Content End -->
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="add_service.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="serviceName" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="serviceName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="serviceDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="serviceDescription" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="serviceImage" class="form-label">Service Image</label>
                            <input type="file" class="form-control" id="serviceImage" name="image" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="servicePrice" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="servicePrice" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="serviceCategory" class="form-label">Category</label>
                            <select class="form-control" id="serviceCategory" name="category" required>
                                <option value="hair">Hair Service</option>
                                <option value="skin">Skin Service</option>
                                <option value="makeup">Makeup Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div class="modal fade" id="editServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="edit_service.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="service_id" id="editServiceId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editServiceName" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="editServiceName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editServiceDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editServiceDescription" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editServiceImage" class="form-label">Service Image</label>
                            <input type="file" class="form-control" id="editServiceImage" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                        <div class="mb-3">
                            <label for="editServicePrice" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="editServicePrice" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="editServiceCategory" class="form-label">Category</label>
                            <select class="form-control" id="editServiceCategory" name="category" required>
                                <option value="hair">Hair Service</option>
                                <option value="skin">Skin Service</option>
                                <option value="makeup">Makeup Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Service Modal -->
    <div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="delete_service.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="service_id" id="deleteServiceId">
                    <div class="modal-body">
                        <p>Are you sure you want to delete this service? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
    // Define editService globally
    function editService(serviceId) {
        if (!serviceId) return;
        
        // Get the button that was clicked
        const button = event.target.closest('button');
        if (!button) return;
        
        // Show loading state
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        button.disabled = true;

        // Make the AJAX request
        $.ajax({
            url: 'get_service.php',
            type: 'POST',
            dataType: 'json',
            data: {
                service_id: serviceId,
                csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
            },
            success: function(response) {
                if (response && response.id) {
                    // Populate the modal with service details
                    $('#editServiceId').val(response.id);
                    $('#editServiceName').val(response.name);
                    $('#editServiceDescription').val(response.description);
                    $('#editServicePrice').val(response.price);
                    $('#editServiceCategory').val(response.category);
                    
                    // Show the modal
                    new bootstrap.Modal(document.getElementById('editServiceModal')).show();
                } else {
                    alert('Failed to load service details. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                alert('Error loading service details. Please try again.');
                console.error('AJAX error:', error);
            },
            complete: function() {
                // Restore button state
                button.innerHTML = originalContent;
                button.disabled = false;
            }
        });
    }

    // Update the form submission handler
    $('#editServiceModal form').on('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = $(form).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
        
        $.ajax({
            url: form.action,
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    if (response.success) {
                        // Show success message before reload
                        alert('Service updated successfully!');
                        window.location.reload();
                    } else {
                        // Show error message
                        alert(response.error || 'Failed to update service');
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                } catch (e) {
                    alert('Error processing response');
                    submitBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                alert('Error updating service: ' + error);
                console.error('Update Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Define deleteService globally
    function deleteService(serviceId) {
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteServiceModal'));
        $('#deleteServiceId').val(serviceId);
        deleteModal.show();
    }

    // Document ready functions
    $(document).ready(function(){
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html>