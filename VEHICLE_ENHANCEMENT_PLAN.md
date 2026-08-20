# Vehicle Enhancement Plan

## Overview
Add comprehensive fields to vehicles based on the Digital Twin, Dynamic Metrics, Financial & Lifecycle, Warranty & Compliance, and Telematics requirements.

---

## Step 1: Create Enums

Create PHP enum classes for standardized values:

### 1.1 FuelType Enum
Location: `app/Enums/FuelType.php`
Options: Gasoline, Diesel, FlexFuel, HEV, PHEV, BEV

### 1.2 TransmissionType Enum  
Location: `app/Enums/TransmissionType.php`
Options: Manual, Automatic, CVT, DualClutch

### 1.3 DrivetrainType Enum
Location: `app/Enums/DrivetrainType.php`
Options: FWD, RWD, AWD, 4WD

### 1.4 OwnershipStatus Enum
Location: `app/Enums/OwnershipStatus.php`
Options: Owned, Leased, Financed, CustomerOwned

### 1.5 VehicleStatus Enum
Location: `app/Enums/VehicleStatus.php`
Options: Active, InShop, Decommissioned, Sold

### 1.6 OdometerSource Enum
Location: `app/Enums/OdometerSource.php`
Options: ManualEntry, OBD_Dongle, Driver_App

---

## Step 2: Database Migrations

### 2.1  Owned, Leased, Financed, CustomerOwned
- `lease_end_date` (DATE, nullable)
- `lease_mileage_limit` (INT, nullable)
- `current_value` (DECIMAL) - Estimated current value

### 2.2 Create vehicle_models table
Location: `database/migrations/YYYY_MM_DD_create_vehicle_models_table.php`
- model_id (PK)
- make (VARCHAR)
- model_name (VARCHAR)
- engine_code (VARCHAR)
- fuel_type (ENUM)
- transmission_type (ENUM)
- oil_capacity_liters (DECIMAL)

### 2.3 Create odometer_logs table
Location: `database/migrations/YYYY_MM_DD_create_odometer_logs_table.php`
- log_id (PK)
- vehicle_id (FK)
- reading_date (DATETIME)
- odometer_value (INT)
- engine_hours (INT, nullable)
- source (ENUM)

### 2.4 Create warranty_policies table
Location: `database/migrations/YYYY_MM_DD_create_warranty_policies_table.php`
- policy_id (PK)
- vehicle_id (FK)
- provider_name (VARCHAR)
- coverage_type (ENUM) - BumperToBumper, Powertrain, PartsSpecific
- start_date (DATE)
- end_date (DATE)
- max_mileage (INT)

### 2.5 Create compliance_records table
Location: `database/migrations/YYYY_MM_DD_create_compliance_records_table.php`
- record_id (PK)
- vehicle_id (FK)
- type (ENUM) - Inspection, Emissions, Insurance, Permit
- expiry_date (DATE)
- notes (TEXT)

### 2.6 Create dtc_logs table (Diagnostic Trouble Codes)
Location: `database/migrations/YYYY_MM_DD_create_dtc_logs_table.php`
- log_id (PK)
- vehicle_id (FK)
- code (VARCHAR) - e.g., P0301
- description (TEXT)
- logged_at (DATETIME)
- cleared_at (DATETIME, nullable)

### 2.7 Create fuel_logs table
Location: `database/migrations/YYYY_MM_DD_create_fuel_logs_table.php`
- log_id (PK)
- vehicle_id (FK)
- date (DATE)
- liters_gallons (DECIMAL)
- cost (DECIMAL)
- odometer_reading (INT)
- source (ENUM)

---

## Step 3: Update Vehicle Model

File: `app/Domains/Vehicles/Models/Vehicle.php`

### 3.1 Update $fillable array
Add new fields to fillable

### 3.2 Update $casts array
Add proper casting for new fields

### 3.3 Add Relationships
- odometerLogs() - HasMany
- warrantyPolicies() - HasMany
- complianceRecords() - HasMany
- dtcLogs() - HasMany
- fuelLogs() - HasMany
- vehicleModel() - BelongsTo (through vehicle_models table)

### 3.4 Add Helper Methods
- latestOdometerReading()
- averageAnnualMileage()
- isOverdueForService()
- getActiveWarranties()

---

## Step 4: Create New Models

### 4.1 VehicleModel
Location: `app/Domains/Vehicles/Models/VehicleModel.php`
- Standard Eloquent model with relationships

### 4.2 OdometerLog
Location: `app/Domains/Vehicles/Models/OdometerLog.php`
- BelongsTo Vehicle

### 4.3 WarrantyPolicy
Location: `app/Domains/Vehicles/Models/WarrantyPolicy.php`
- BelongsTo Vehicle

### 4.4 ComplianceRecord
Location: `app/Domains/Vehicles/Models/ComplianceRecord.php`
- BelongsTo Vehicle

### 4.5 DtcLog
Location: `app/Domains/Vehicles/Models/DtcLog.php`
- BelongsTo Vehicle

### 4.6 FuelLog
Location: `app/Domains/Vehicles/Models/FuelLog.php`
- BelongsTo Vehicle

---

## Step 5: Update CreateVehiclesComponent

File: `app/Domains/Vehicles/Livewire/Vehicles/CreateVehiclesComponent.php`

### 5.1 Add Public Properties
- engine_code
- engine_displacement
- drivetrain_type
- transmission_code
- in_service_date
- acquisition_date
- acquisition_cost
- ownership_status
- lease_end_date
- lease_mileage_limit
- current_value
- status

### 5.2 Update Validation Rules
Add rules for new fields

### 5.3 Update Save Method
Include new fields in creation

### 5.4 Add NHTSA VIN Decoding (Optional/Future)
Add method to decode VIN via NHTSA API

---

## Step 6: Update Create Vehicle View

File: `resources/views/livewire/vehicles/create-vehicles-component.blade.php`

### 6.1 Add New Form Sections
Organize into expandable sections:

#### Section: Digital Twin
- VIN (standardize to 17-char validation)
- Engine Code
- Engine Displacement
- Transmission Type
- Transmission Code
- Drivetrain Type
- Fuel Type (expanded options)

#### Section: Dynamic Metrics
- Current Odometer
- Engine Hours

#### Section: Financial & Lifecycle
- In-Service Date
- Acquisition Date
- Acquisition Cost
- Ownership Status
- Lease End Date (conditional)
- Lease Mileage Limit (conditional)
- Current Value

#### Section: Status
- Vehicle Status

---

## Step 7: Edit Vehicle Component & View

Update `EditVehiclesComponent.php` and `edit-vehicles-component.blade.php` with same new fields

---

## Implementation Order (Priority)

1. **Create Enums** - Foundation for migrations
2. **Database Migrations** - Update vehicles + create new tables
3. **Update Vehicle Model** - Add fillable, casts, relationships
4. **Create New Models** - VehicleModel, OdometerLog, etc.
5. **Update CreateVehiclesComponent** - Add properties and validation
6. **Update View** - Add form fields organized by section
7. **Update Edit Components** - For consistency

---

## Follow-up Steps After Implementation

1. Run migrations: `php artisan migrate`
2. Test vehicle creation with new fields
3. Update vehicle list view to show new fields
4. Update vehicle detail view to show all related data
5. Add NHTSA VIN decoding integration (future)
6. Add OBD-II dongle integration (future)

---

## Files to be Modified/Created

### New Files:
- `app/Enums/FuelType.php`
- `app/Enums/TransmissionType.php`
- `app/Enums/DrivetrainType.php`
- `app/Enums/OwnershipStatus.php`
- `app/Enums/VehicleStatus.php`
- `app/Enums/OdometerSource.php`
- `app/Domains/Vehicles/Models/VehicleModel.php`
- `app/Domains/Vehicles/Models/OdometerLog.php`
- `app/Domains/Vehicles/Models/WarrantyPolicy.php`
- `app/Domains/Vehicles/Models/ComplianceRecord.php`
- `app/Domains/Vehicles/Models/DtcLog.php`
- `app/Domains/Vehicles/Models/FuelLog.php`

### Modified Files:
- `app/Domains/Vehicles/Models/Vehicle.php`
- `app/Domains/Vehicles/Livewire/Vehicles/CreateVehiclesComponent.php`
- `resources/views/livewire/vehicles/create-vehicles-component.blade.php`
- `app/Domains/Vehicles/Livewire/Vehicles/EditVehiclesComponent.php`
- `resources/views/livewire/vehicles/edit-vehicles-component.blade.php`

### New Migrations:
- `database/migrations/YYYY_MM_DD_update_vehicles_table.php`
- `database/migrations/YYYY_MM_DD_create_vehicle_models_table.php`
- `database/migrations/YYYY_MM_DD_create_odometer_logs_table.php`
- `database/migrations/YYYY_MM_DD_create_warranty_policies_table.php`
- `database/migrations/YYYY_MM_DD_create_compliance_records_table.php`
- `database/migrations/YYYY_MM_DD_create_dtc_logs_table.php`
- `database/migrations/YYYY_MM_DD_create_fuel_logs_table.php`
