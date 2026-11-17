# TEST PLAN
## Online Kids Birthday Party Planning System

### Student Information
- Name: UWIKUNZE JUDITH
- Registration Number: 224007430

## Test Cases

| Test No. | Feature | Test Description | Expected Result |
|---|---|---|---|
| 1 | Registration | Register with valid details | Account created successfully |
| 2 | Registration | Register with an invalid email | Error message displayed |
| 3 | Login | Login with correct email and password | User enters dashboard |
| 4 | Login | Login with incorrect password | Error message displayed |
| 5 | Create Party | Enter valid party details | Party saved successfully |
| 6 | Create Party | Submit empty party information | Validation error displayed |
| 7 | Update Party | Edit existing party details | Party updated successfully |
| 8 | Delete Party | Delete an existing party | Party removed successfully |
| 9 | Create Vendor | Add vendor with valid details | Vendor saved successfully |
| 10 | Update Vendor | Edit vendor information | Vendor updated successfully |
| 11 | Delete Vendor | Delete a vendor without bookings | Vendor deleted successfully |
| 12 | Create Booking | Select a party and vendor | Booking created successfully |
| 13 | Delete Booking | Delete an existing booking | Booking removed successfully |
| 14 | Vendor Search | Search using vendor name | Matching vendors displayed |
| 15 | Security | Submit a form without a valid CSRF token | Request rejected |

## Test Summary

The system is tested using valid and invalid inputs.
Both successful operations and failure cases are included.