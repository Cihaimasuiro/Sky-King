class CrudManager {
    constructor(options) {
        this.tableId = options.tableId;
        this.tableName = options.tableName;
        this.primaryKey = options.primaryKey;
        this.allowedFields = options.allowedFields;
        this.modalId = options.modalId;
        this.formId = options.formId;
        this.initializeDataTable();
        this.initializeEventListeners();
    }

    initializeDataTable() {
        this.dataTable = $(`#${this.tableId}`).DataTable({
            pageLength: 10,
            order: [[0, "desc"]],
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        });
    }

    initializeEventListeners() {
        // Add button click
        $(`#${this.formId} .save-btn`).click(() => this.handleSave());

        // Edit button click
        $(`#${this.tableId} tbody`).on('click', '.edit-btn', (e) => {
            const row = $(e.target).closest('tr');
            this.handleEdit(row);
        });

        // Delete button click
        $(`#${this.tableId} tbody`).on('click', '.delete-btn', (e) => {
            const row = $(e.target).closest('tr');
            this.handleDelete(row);
        });
    }

    handleSave() {
        const formData = new FormData($(`#${this.formId}`)[0]);
        formData.append('table', this.tableName);
        formData.append('action', 'create');
        formData.append('primaryKey', this.primaryKey);
        formData.append('allowedFields', JSON.stringify(this.allowedFields));

        $.ajax({
            url: 'config/crud_handler.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Record added successfully',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $(`#${this.modalId}`).modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', response.error || 'Failed to add record', 'error');
                }
            },
            error: () => {
                Swal.fire('Error!', 'Failed to add record', 'error');
            }
        });
    }

    handleEdit(row) {
        const id = row.data('id');
        const formData = new FormData();
        
        this.allowedFields.forEach(field => {
            formData.append(field, row.find(`[data-field="${field}"]`).text());
        });

        formData.append('id', id);
        formData.append('table', this.tableName);
        formData.append('action', 'update');
        formData.append('primaryKey', this.primaryKey);
        formData.append('allowedFields', JSON.stringify(this.allowedFields));

        $.ajax({
            url: 'config/crud_handler.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Record updated successfully',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire('Error!', response.error || 'Failed to update record', 'error');
                }
            },
            error: () => {
                Swal.fire('Error!', 'Failed to update record', 'error');
            }
        });
    }

    handleDelete(row) {
        const id = row.data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('table', this.tableName);
                formData.append('action', 'delete');
                formData.append('primaryKey', this.primaryKey);
                formData.append('allowedFields', JSON.stringify(this.allowedFields));

                $.ajax({
                    url: 'config/crud_handler.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        if (response.success) {
                            row.remove();
                            Swal.fire('Deleted!', 'Record has been deleted.', 'success');
                        } else {
                            Swal.fire('Error!', response.error || 'Failed to delete record', 'error');
                        }
                    },
                    error: () => {
                        Swal.fire('Error!', 'Failed to delete record', 'error');
                    }
                });
            }
        });
    }
}
