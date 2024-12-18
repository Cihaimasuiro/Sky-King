<?php require_once '../includes/header.php'; ?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Transportation</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransportationModal">
            Add Transportation
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Class</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Carrier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transportationList as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['model']); ?></td>
                                <td><?php echo htmlspecialchars($item['class']); ?></td>
                                <td><?php echo htmlspecialchars($item['type']); ?></td>
                                <td><?php echo htmlspecialchars($item['capacity']); ?></td>
                                <td><?php echo htmlspecialchars($item['carrier_name']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-btn" 
                                            data-id="<?php echo $item['id']; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editTransportationModal">
                                        Edit
                                    </button>
                                    <form action="transportation.php" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Transportation Modal -->
<div class="modal fade" id="addTransportationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Transportation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="transportation.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="model" class="form-label">Model</label>
                        <input type="text" class="form-control" id="model" name="model" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="class" class="form-label">Class</label>
                        <select class="form-select" id="class" name="class" required>
                            <option value="Economy">Economy</option>
                            <option value="Business">Business</option>
                            <option value="First">First Class</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="Airplane">Airplane</option>
                            <option value="Train">Train</option>
                            <option value="Bus">Bus</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="capacity" class="form-label">Capacity</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="carrier_id" class="form-label">Carrier</label>
                        <select class="form-select" id="carrier_id" name="carrier_id" required>
                            <?php
                            $query = "SELECT id, name FROM carriers ORDER BY name";
                            $result = mysqli_query($conn, $query);
                            while ($carrier = mysqli_fetch_assoc($result)) {
                                echo '<option value="' . $carrier['id'] . '">' . htmlspecialchars($carrier['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Transportation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Transportation Modal -->
<div class="modal fade" id="editTransportationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Transportation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="transportation.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <!-- Same form fields as Add Modal -->
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_model" class="form-label">Model</label>
                        <input type="text" class="form-control" id="edit_model" name="model" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_class" class="form-label">Class</label>
                        <select class="form-select" id="edit_class" name="class" required>
                            <option value="Economy">Economy</option>
                            <option value="Business">Business</option>
                            <option value="First">First Class</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">Type</label>
                        <select class="form-select" id="edit_type" name="type" required>
                            <option value="Airplane">Airplane</option>
                            <option value="Train">Train</option>
                            <option value="Bus">Bus</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_capacity" class="form-label">Capacity</label>
                        <input type="number" class="form-control" id="edit_capacity" name="capacity" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_carrier_id" class="form-label">Carrier</label>
                        <select class="form-select" id="edit_carrier_id" name="carrier_id" required>
                            <?php
                            mysqli_data_seek($result, 0);
                            while ($carrier = mysqli_fetch_assoc($result)) {
                                echo '<option value="' . $carrier['id'] . '">' . htmlspecialchars($carrier['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Transportation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript to populate edit modal
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr');
        const id = this.dataset.id;
        
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = row.cells[0].textContent;
        document.getElementById('edit_model').value = row.cells[1].textContent;
        document.getElementById('edit_class').value = row.cells[2].textContent;
        document.getElementById('edit_type').value = row.cells[3].textContent;
        document.getElementById('edit_capacity').value = row.cells[4].textContent;
        
        // Find and select the carrier in the dropdown
        const carrierName = row.cells[5].textContent;
        const carrierSelect = document.getElementById('edit_carrier_id');
        Array.from(carrierSelect.options).forEach(option => {
            if (option.textContent === carrierName) {
                option.selected = true;
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
