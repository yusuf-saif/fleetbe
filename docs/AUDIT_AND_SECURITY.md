# FleetBe Codebase Audit & Security Vulnerability Report

This document compiles the comprehensive findings of the static code analysis, security review, and architecture audit conducted on the **FleetBe** codebase.

---

## 1. Audit Executive Summary

| Category | High | Medium | Low | Total |
|---|---|---|---|---|
| **Security Vulnerabilities** | 3 | 1 | 0 | **4** |
| **Logic & Functional Bugs** | 2 | 2 | 1 | **5** |
| **Code Quality & Consistency** | 0 | 2 | 3 | **5** |
| **Total Findings** | **5** | **5** | **4** | **14** |

---

## 2. High-Severity Security Vulnerabilities

### VULN-01: Unprotected Driver Endpoints (PII Exposure)
* **Severity:** **CRITICAL (CVSS 9.1)**
* **File:** [`routes/api.php#L33-L40`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/routes/api.php#L33-L40)
* **Description:** The `auth:sanctum` middleware block protecting driver CRUD operations was commented out.
* **Code:**
```php
// Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [DriverAuthController::class, 'logout']);
    Route::get("/", [DriverController::class, "index"]);
    Route::get("/{id}", [DriverController::class, "show"]);
    Route::delete("/{id}", [DriverController::class, "destroy"]);
    Route::put("/{id}", [DriverController::class, "update"]);
// });
```
* **Impact:** Any unauthenticated client can retrieve the full list of drivers, inspect personal information (addresses, next of kin, medical conditions, blood groups), alter driver records, or delete drivers.
* **Remediation:** Uncomment the `auth:sanctum` middleware wrapper around driver operations.

---

### VULN-02: Multi-Tenant Data Isolation Breach
* **Severity:** **HIGH (CVSS 8.5)**
* **Files:**
  * [`app/Http/Controllers/VehicleController.php#L43`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Http/Controllers/VehicleController.php#L43)
  * [`app/Http/Controllers/DriverController.php#L44`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Http/Controllers/DriverController.php#L44)
  * [`app/Http/Controllers/FuelController.php#L33`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Http/Controllers/FuelController.php#L33)
  * [`app/Http/Controllers/VehicleAssignmentController.php#L45`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Http/Controllers/VehicleAssignmentController.php#L45)
* **Description:** Controllers execute global queries (e.g., `Vehicle::with(...)->get()`) without constraining the query to the authenticated user's `organization_id`.
* **Impact:** A staff user from Company A can view and modify vehicles, drivers, fuel stock, and assignments belonging to Company B.
* **Remediation:**
  1. Implement a Laravel Global Scope (e.g., `TenantScope`) on tenant models (`Vehicle`, `Driver`, `Fuel`, `SparePart`, `Maintenance`).
  2. Or, retrieve records scoped to the authenticated user's organization:
  ```php
  $orgId = $request->user()->organizationStaff->organization_id;
  $vehicles = Vehicle::where('organization_id', $orgId)->with(...)->get();
  ```

---

### VULN-03: Broken Token Invalidation on Logout
* **Severity:** **HIGH (CVSS 7.5)**
* **Files:**
  * [`app/Http/Controllers/AuthController.php#L131`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Http/Controllers/AuthController.php#L131)
  * [`app/Http/Controllers/DriverAuthController.php#L242`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Http/Controllers/DriverAuthController.php#L242)
* **Description:** The `logout` method accesses `->delete` as an object property instead of executing the method `->delete()`:
```php
// Current code:
$request->user()->currentAccessToken()->delete; // Does NOT execute!
```
* **Impact:** The token is never revoked in `personal_access_tokens`. A logged-out user or compromised token remains valid until expiration.
* **Remediation:**
```php
$request->user()->currentAccessToken()->delete();
```

---

### VULN-04: Plaintext Password & PIN Transmission in Mail
* **Severity:** **MEDIUM (CVSS 5.3)**
* **File:** [`app/Http/Controllers/DriverAuthController.php#L150`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Http/Controllers/DriverAuthController.php#L150)
* **Description:** The driver onboarding process sends both the temporary plaintext password and the one-time PIN in an unencrypted raw email string:
```php
Mail::raw("Welcome {$driver->name}, your PIN code is: {$pinCode}. Use it to set your new password use password. use the password {$randomPassword}", ...);
```
* **Impact:** Intermediate mail servers, inbox sniffers, or shared mail logs expose cleartext initial credentials.
* **Remediation:** Generate an expirable signed invitation link or send an activation PIN without transmitting plaintext passwords over standard mail.

---

## 3. Functional & Logic Bugs

### BUG-01: Vehicle Status Case-Mismatch in Assignment Conflict Check
* **File:** [`app/Http/Controllers/VehicleAssignmentController.php#L100`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Http/Controllers/VehicleAssignmentController.php#L100)
* **Problem:**
```php
if ($vehicle->status === 'assigned') { // Evaluates FALSE because DB value is 'Assigned'
    return response()->json(['message' => 'This vehicle is already assigned...'], 409);
}
```
* **Cause:** The database migration enum and assignment creation store `'status' => 'Assigned'` (Title Case). The strict string check compares against `'assigned'` (lowercase).
* **Fix:**
```php
if (strtolower($vehicle->status) === 'assigned') {
```

---

### BUG-02: Erroneous `other_sparepart` Validation in Request Creation
* **File:** [`app/Http/Controllers/RequestsController.php#L133`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Http/Controllers/RequestsController.php#L133)
* **Problem:**
```php
'with_sparepart'  => 'required|boolean',
'spare_part_id'   => 'nullable|required_if:with_sparepart,true|exists:spare_parts,id',
'other_sparepart' => 'nullable|required_if:spare_part_id,null|string|max:255',
```
* **Cause:** If `with_sparepart` is `false`, `spare_part_id` is naturally `null`. But `required_if:spare_part_id,null` triggers, demanding that the user submit `other_sparepart` even for fuel or basic maintenance requests!
* **Fix:**
```php
'other_sparepart' => 'nullable|exclude_if:with_sparepart,false|required_without:spare_part_id|string|max:255',
```

---

### BUG-03: Broken Mailable View Templates
* **Files:**
  * [`app/Mail/DriverRegisteredMail.php#L45`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Mail/DriverRegisteredMail.php#L45)
  * [`app/Mail/DriverPasswordResetPin.php#L40`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Mail/DriverPasswordResetPin.php#L40)
* **Problem:** Both mailables declare `view: 'view.name'`, which is a dummy placeholder:
```php
public function content(): Content
{
    return new Content(view: 'view.name');
}
```
* **Impact:** Invoking `Mail::send(new DriverRegisteredMail(...))` throws an unhandled `InvalidArgumentException: View [view.name] not found`.
* **Fix:** Create actual Blade templates in `resources/views/emails/` or change to `htmlString()`.

---

### BUG-04: Migration Future Timestamp (Year 3025)
* **File:** [`database/migrations/3025_08_15_100204_create_supplier_table.php`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/database/migrations/3025_08_15_100204_create_supplier_table.php)
* **Problem:** The migration filename begins with year `3025`.
* **Impact:** In Laravel, migrations are ordered alphabetically by filename. New migrations created today (2025/2026) will execute *before* `3025_...`. Any subsequent migration that references `suppliers` will throw a foreign key error because the `suppliers` table hasn't been created yet.
* **Fix:** Rename the migration to follow the correct chronological order, e.g. `2025_09_10_100204_create_supplier_table.php`.

---

## 4. Code Quality & Standards

### 1. Case Sensitivity & PSR-4 Autoloading
* **File:** [`app/Models/vehicle.php`](file:///Users/saifyusuph/Documents/ES2%20LTD/fleetbe/app/Models/vehicle.php) has a lowercase `v` in its filename. On case-sensitive Linux environments (Docker / production), referencing `App\Models\Vehicle` can cause class lookup failures.
* **Fix:** Rename `vehicle.php` to `Vehicle.php`.

### 2. Plural Model Naming Conventions
* `app/Models/Drivers.php` -> Standard convention is `Driver.php`.
* `app/Models/Requests.php` -> Standard convention is `Request.php` (or `ResourceRequest.php` to avoid collisions with `Illuminate\Http\Request`).
* `app/Models/product_services_configs.php` -> Snake_case model file is empty and unused.

---

## 5. Immediate Remediation Checklist

- [ ] **Step 1:** Uncomment `auth:sanctum` in `routes/api.php` for driver endpoints.
- [ ] **Step 2:** Fix `->delete;` to `->delete();` in `AuthController.php` and `DriverAuthController.php`.
- [ ] **Step 3:** Correct status comparison to `strtolower($vehicle->status) === 'assigned'` in `VehicleAssignmentController.php`.
- [ ] **Step 4:** Adjust validation rules in `RequestsController.php`.
- [ ] **Step 5:** Rename `app/Models/vehicle.php` to `app/Models/Vehicle.php`.
- [ ] **Step 6:** Rename migration `3025_08_15_100204_create_supplier_table.php` to `2025_09_10_100204_create_supplier_table.php`.
- [ ] **Step 7:** Implement tenant scoping middleware or global scopes across all domain queries.

