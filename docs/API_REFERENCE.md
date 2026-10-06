# FleetBe REST API Reference

Comprehensive reference guide for all endpoints exposed by the **FleetBe** v1 API.

* **Base URL:** `http://localhost:8080/api/v1`
* **Default Headers:**
  * `Accept: application/json`
  * `Content-Type: application/json`
* **Authentication Header:**
  * `Authorization: Bearer <token>`
* **Interactive UI:** Available locally at `/docs` (Scribe) or OpenAPI reference via Scalar.

---

## 1. System Health & Status

### GET `/v1/status`
Checks API operational status.
* **Authentication:** None (Public)
* **Response (200 OK):**
```json
{
  "message": "Fleet API is running successfully"
}
```

---

## 2. User & Corporate Staff Authentication (`/v1/auth`)

### POST `/v1/auth/register`
Creates a new corporate user account.
* **Authentication:** None (Public)
* **Request Body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@company.com",
  "password": "SecretPassword123!",
  "password_confirmation": "SecretPassword123!"
}
```
* **Response (201 Created):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@company.com"
  }
}
```

---

### POST `/v1/auth/login`
Authenticates a corporate staff user and issues a Bearer token.
* **Authentication:** None (Public)
* **Request Body:**
```json
{
  "email": "jane@company.com",
  "password": "SecretPassword123!"
}
```
* **Response (200 OK):**
```json
{
  "message": "Login successful",
  "token": "1|ZpRzVvq4UIq2p89Xyz...",
  "user": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@company.com",
    "organization_staff": {
      "id": 5,
      "staff_position": "Fleet Operations Manager",
      "staff_status": "Active",
      "staff_level": "Senior",
      "organization": {
        "id": 2,
        "organization_name": "Fleet Corp Ltd"
      }
    }
  }
}
```

---

### POST `/v1/auth/logout`
Revokes the current Bearer access token.
* **Authentication:** `Bearer <token>` (`auth:sanctum`)
* **Response (200 OK):**
```json
{
  "message": "Logged out successfully"
}
```

---

### POST `/v1/auth/forgot-password`
Dispatches a password reset email link.
* **Authentication:** None (Public)
* **Request Body:**
```json
{
  "email": "jane@company.com"
}
```
* **Response (200 OK):**
```json
{
  "message": "Password reset link sent"
}
```

---

### POST `/v1/auth/reset-password`
Resets the user's password using the token sent via email.
* **Authentication:** None (Public)
* **Request Body:**
```json
{
  "token": "token_from_email",
  "email": "jane@company.com",
  "password": "NewSecretPassword123!",
  "password_confirmation": "NewSecretPassword123!"
}
```
* **Response (200 OK):**
```json
{
  "message": "Password reset successful"
}
```

---

## 3. Driver Management & Authentication (`/v1/driver`)

### POST `/v1/driver/register`
Enrolls a new driver into an organization. Automatically sets `must_change_password = true` and generates a 6-digit PIN.
* **Authentication:** `Bearer <token>` (`auth:sanctum`)
* **Request Body:**
```json
{
  "organization_id": 1,
  "name": "Musa Ibrahim",
  "email": "musa@driver.company.com",
  "phone_number": "+2348011223344",
  "driver_license": "DL-99283-AB",
  "license_expiry_date": "2027-04-15",
  "date_of_birth": "1988-11-20",
  "blood_group": "O+",
  "genotype": "AA",
  "allergies": ["Penicillin", "Dust"],
  "medical_challenge": ["Hypertension"],
  "eye_condition": ["Short-sightedness"],
  "residential_address": "14 Commercial Road, Lagos",
  "home_address": "4 Village Way, Kano",
  "state": "Lagos",
  "lga": "Ikeja",
  "town": "Alausa",
  "nationality": "Nigerian",
  "state_of_origin": "Kano",
  "lga_of_origin": "Nassarawa",
  "town_of_origin": "Dakata",
  "next_kin_name": "Amina Ibrahim",
  "next_kin_relationship": "Spouse",
  "next_kin_phone": "+2348099887766",
  "next_kin_email": "amina@example.com",
  "next_kin_residential_address": "14 Commercial Road, Lagos"
}
```
* **Response (201 Created):**
```json
{
  "message": "Driver registered successfully. A PIN has been sent to the email and phone.",
  "driver_id": 4
}
```

---

### POST `/v1/driver/login`
Authenticates a driver via the `driver` guard and issues a Sanctum token.
* **Authentication:** None (Public)
* **Request Body:**
```json
{
  "email": "musa@driver.company.com",
  "password": "initial_password_or_temporary"
}
```
* **Response (200 OK):**
```json
{
  "message": "Login successful",
  "token": "2|Xy98Z...",
  "driver": {
    "id": 4,
    "name": "Musa Ibrahim",
    "email": "musa@driver.company.com",
    "must_change_password": true
  }
}
```

---

### POST `/v1/driver/change-password`
Enables a newly registered or reset driver to establish a new password using their 6-digit PIN.
* **Authentication:** None (Public)
* **Request Body:**
```json
{
  "email": "musa@driver.company.com",
  "pin": "482910",
  "new_password": "NewDriverPassword123!",
  "new_password_confirmation": "NewDriverPassword123!"
}
```
* **Response (200 OK):**
```json
{
  "message": "Password changed successfully. You can now log in."
}
```

---

### GET `/v1/driver`
Retrieves a paginated list of drivers.
* **Query Parameters:** `page` (int), `per_page` (int, default 10)
* **Response (200 OK):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 4,
      "name": "Musa Ibrahim",
      "email": "musa@driver.company.com",
      "phone_number": "+2348011223344",
      "driver_license": "DL-99283-AB",
      "blood_group": "O+"
    }
  ],
  "total": 1
}
```

---

### GET `/v1/driver/{id}`
Retrieves complete profile details for a driver.

---

### PUT `/v1/driver/{id}`
Updates editable attributes of a driver record.
* **Request Body:** (Any subset of driver profile fields)
* **Response (200 OK):**
```json
{
  "message": "Driver updated successfully",
  "data": { "id": 4, "name": "Musa Ibrahim Updated" }
}
```

---

### DELETE `/v1/driver/{id}`
Removes a driver from the database.
* **Response (200 OK):**
```json
{
  "message": "Driver deleted successfully"
}
```

---

## 4. Vehicle Catalog (`/v1/vehicles`)

### GET `/v1/vehicles`
Lists all vehicles with eager-loaded `organization`, `driver`, and `assignedUser`.
* **Authentication:** `Bearer <token>` (`auth:sanctum`)
* **Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Toyota Hilux 4x4",
      "type": "Truck",
      "plate_number": "ABJ-892-XY",
      "chasis_number": "MROER32J928371",
      "asset_number": "AST-VH-001",
      "vehicle_security_number": "SEC-88219",
      "manufacturer": "Toyota",
      "condition": "In Good Condition",
      "status": "Assigned",
      "fuel_capacity": "80.00",
      "fuel_type": ["diesel"],
      "driver": { "id": 4, "name": "Musa Ibrahim" },
      "organization": { "id": 1, "organization_name": "Fleet Corp Ltd" }
    }
  ]
}
```

---

### POST `/v1/vehicles`
Registers a new vehicle in the fleet catalog.
* **Authentication:** `Bearer <token>`
* **Request Body:**
```json
{
  "organization_id": 1,
  "name": "Toyota Hilux 4x4",
  "type": "Truck",
  "plate_number": "ABJ-892-XY",
  "chasis_number": "MROER32J928371",
  "asset_number": "AST-VH-001",
  "vehicle_security_number": "SEC-88219",
  "manufacturer": "Toyota",
  "condition": "In Good Condition",
  "status": "Unassigned",
  "fuel_capacity": 80.0,
  "date_purchased": "2024-01-15",
  "manufactured_year": 2023,
  "fuel_type": ["diesel"],
  "driver_id": null,
  "user_assigned_id": null
}
```
* **Response (201 Created):** Returns created vehicle object.

---

### GET `/v1/vehicles/{id}` | PUT `/v1/vehicles/{id}` | DELETE `/v1/vehicles/{id}`
Standard CRUD operations for single vehicles.

---

## 5. Vehicle Assignments (`/v1/vehicle-assignments`)

### GET `/v1/vehicle-assignments`
Lists all assignments with optional query filters.
* **Query Parameters:** `driver_id` (int), `vehicle_id` (int)

---

### POST `/v1/vehicle-assignments`
Assigns a vehicle to a driver. Automatically sets vehicle status to `'Assigned'`.
* **Request Body:**
```json
{
  "vehicle_id": 1,
  "driver_id": 4,
  "starting_odometer": 14250.50,
  "assigned_by_id": 1,
  "assigned_at": "2026-10-06 08:00:00"
}
```
* **Response (201 Created):**
```json
{
  "id": 1,
  "vehicle_id": 1,
  "driver_id": 4,
  "starting_odometer": "14250.50",
  "assigned_at": "2026-10-06T08:00:00.000000Z",
  "released_at": null,
  "vehicle": { "id": 1, "plate_number": "ABJ-892-XY", "status": "Assigned" }
}
```

---

### GET `/v1/vehicle-assignments/current`
Returns all assignments that are currently active (`released_at` is null or in the future).

---

### POST `/v1/vehicle-assignments/{assignment}/release`
Releases an active assignment. Updates `released_at` and sets vehicle status back to `'Unassigned'`.
* **Request Body:**
```json
{
  "released_at": "2026-10-06 18:00:00"
}
```
* **Response (200 OK):** Returns updated assignment with released vehicle.

---

## 6. Inventory & Maintenance

### Fuel (`/v1/fuel`)
* `GET /v1/fuel`: List fuels with storages.
* `POST /v1/fuel`: Create fuel record (`title`, `fuel_type: petrol|diesel|gas`, `reserve_level`, `unit`, `organization_id`).

### Spare Parts (`/v1/sparepart`)
* `GET /v1/sparepart`: List spare parts with pagination (`?organization_id=1`).
* `POST /v1/sparepart`: Create spare part (`title`, `size`, `description`, `reserve_quantity`, `unit`, `organization_id`).

### Maintenance (`/v1/maintenance`)
* `GET /v1/maintenance`: List scheduled maintenance templates (`?organization_id=1`).
* `POST /v1/maintenance`: Create maintenance procedure (`title`, `type`, `frequency`, `organization_id`).

### Suppliers (`/v1/suppliers`)
* `GET /v1/suppliers`: List suppliers (`?with_fuel=1`, `?with_spare_parts=1`, `?with_maintenance=1`).
* `POST /v1/suppliers`: Register vendor (`supplier_name`, `location`, `contact_person_name`, `contact_person_email`, `contact_person_phone`).

---

## 7. Resource & Service Requests (`/v1/requests`)

### GET `/v1/requests`
Filters requests by parameter.
* **Query Parameters:**
  * `vehicle_id` (int)
  * `driver_id` (int)
  * `requestable_type` (string: `'fuel'`, `'spare_part'`, `'maintenance'`)
  * `status` (string: `'pending'`, `'approved'`, `'rejected'`, `'in_progress'`, `'completed'`)
  * `current` (boolean)

---

### POST `/v1/requests`
Submits a ticket for fuel dispensing, spare parts replacement, or maintenance repair.
* **Request Body (Maintenance with Spare Part):**
```json
{
  "vehicle_id": 1,
  "driver_id": 4,
  "requestable_type": "maintenance",
  "requestable_id": 2,
  "maintenance_type": "corrective",
  "description": "Front brake pads worn out and squealing.",
  "with_sparepart": true,
  "spare_part_id": 3,
  "other_sparepart": null
}
```
* **Request Body (Fuel Dispense):**
```json
{
  "vehicle_id": 1,
  "driver_id": 4,
  "requestable_type": "fuel",
  "requestable_id": 1,
  "quantity_requested": 65.0,
  "vehicle_odometer": 14500.0,
  "current_fuel_level": 15.0,
  "with_sparepart": false
}
```
* **Response (201 Created):** Returns created request ticket with polymorphic relations loaded.

---

### POST `/v1/requests/{id}/approve`
Approves a pending request and specifies the authorized quantity.
* **Request Body:**
```json
{
  "quantity_approved": 65.0
}
```
* **Response (200 OK):**
```json
{
  "id": 1,
  "status": "approved",
  "quantity_approved": 65.0
}
```

---

### POST `/v1/requests/{id}/reject`
Rejects a request and writes the rejection reason into `description`.
* **Request Body:**
```json
{
  "reason": "Odometer discrepancy detected with last logged assignment."
}
```
* **Response (200 OK):** Returns request with `status: "rejected"`.

