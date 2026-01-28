# Project Management System - Setup Instructions

## Quick Start Guide

Follow these steps to set up and use the project management system:

### 1. Run Database Migrations

Open a terminal in your project directory and run:

```bash
php artisan migrate
```

This will create the following tables:
- `projects`
- `project_budget_items`
- `project_approvals`
- `project_item_availability`

### 2. Seed Permissions and Roles

Run the permissions seeder to create roles and permissions:

```bash
php artisan db:seed --class=ProjectPermissionsSeeder
```

This creates the following roles:
- **CTO** - Can approve projects
- **Director** - Can approve projects
- **Planning Team** - Can create and manage project budgets
- **Implementation Team** - Can check item availability
- **Stores Team** - Can check item availability

### 3. Assign Roles to Users

1. Login to the system
2. Navigate to **Access Control** → **User Access**
3. Assign appropriate roles to your users:
   - Assign "CTO" role to your Chief Technology Officer
   - Assign "Director" role to your Director
   - Assign "Planning Team" role to users who will create and plan projects
   - Assign "Implementation Team" role to users who will implement projects
   - Assign "Stores Team" role to users who manage inventory

### 4. Start Creating Projects

#### As a Planning Team Member:

1. Go to **Projects** → **New Project**
2. Enter project details:
   - Project name (required)
   - Description
   - Start and end dates
   - Priority level
   - Associated client (if any)
   - Objectives and deliverables

3. Click "Create Project & Add Budget Items"

4. Add budget items:
   - Item name
   - Category (Equipment, Materials, Labor, etc.)
   - Quantity and unit
   - Unit cost
   - Justification

5. Click "Submit for Approval" when all items are added

#### As CTO or Director:

1. Go to **Projects**
2. Find projects with "Pending Approval" status
3. Click "Approve" to review
4. Review all budget items and total cost
5. Add comments (optional)
6. Click "Approve" or "Reject"

#### As Implementation/Stores Team:

1. Go to **Projects**
2. Find projects with "Approved" status
3. Click "Check Items"
4. For each budget item:
   - Click "Check"
   - Enter available quantity
   - Select availability status
   - Add notes if needed
   - Set expected date if not immediately available
   - Click "Save Availability"

## File Structure

### Models Created:
- `app/Models/Project.php`
- `app/Models/ProjectBudgetItem.php`
- `app/Models/ProjectApproval.php`
- `app/Models/ProjectItemAvailability.php`

### Livewire Components:
- `app/Livewire/Projects/ProjectList.php`
- `app/Livewire/Projects/CreateProject.php`
- `app/Livewire/Projects/ProjectBudget.php`
- `app/Livewire/Projects/ProjectApprovals.php`
- `app/Livewire/Projects/ProjectItemAvailability.php`

### Views:
- `resources/views/livewire/projects/project-list.blade.php`
- `resources/views/livewire/projects/create-project.blade.php`
- `resources/views/livewire/projects/project-budget.blade.php`
- `resources/views/livewire/projects/project-approvals.blade.php`
- `resources/views/livewire/projects/project-item-availability.blade.php`

### Migrations:
- `database/migrations/2025_11_19_000001_create_projects_table.php`
- `database/migrations/2025_11_19_000002_create_project_budget_items_table.php`
- `database/migrations/2025_11_19_000003_create_project_approvals_table.php`
- `database/migrations/2025_11_19_000004_create_project_item_availability_table.php`

### Seeders:
- `database/seeders/ProjectPermissionsSeeder.php`

## Navigation

The project management system is accessible from the sidebar under "Projects" with the following sub-menu items:
- All Projects
- New Project

## Troubleshooting

### Issue: "Permission denied" error
**Solution:** Make sure you've run the permissions seeder and assigned the appropriate role to your user.

### Issue: Can't see the Projects menu
**Solution:** Ensure your user has the `view_projects` permission. Check User Access in the system.

### Issue: Budget items total not calculating
**Solution:** This is handled automatically by the model. Make sure quantity and unit_cost are numeric values.

### Issue: Project not moving to "In Progress" after checking all items
**Solution:** Ensure all items have availability status set to "available". The system automatically updates the status when all items are fully available.

## Important Notes

1. **Both CTO and Director must approve** - A project needs approval from both roles before it can proceed to implementation.

2. **Auto-generated Project Codes** - Project codes are automatically generated in the format `PRJ-XXXXX`. You don't need to enter them manually.

3. **Total Cost Calculation** - Total costs for budget items are automatically calculated when you enter quantity and unit cost.

4. **Soft Deletes** - Deleted projects are soft-deleted and can be recovered if needed.

5. **Availability Status** - The system automatically determines if quantity is sufficient:
   - If available >= required: Status set to "Available"
   - If 0 < available < required: Status set to "Partial"

## Next Steps

After setup, you can:
1. Create your first test project
2. Add sample budget items
3. Test the approval workflow
4. Test the availability checking

For detailed usage instructions, refer to `PROJECT-MANAGEMENT-GUIDE.md`.

## Support

If you encounter any issues during setup, check:
1. Database connection is working
2. All migrations have run successfully
3. Permissions seeder has been executed
4. Users have been assigned appropriate roles

For additional help, contact your system administrator.
