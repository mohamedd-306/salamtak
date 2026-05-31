# Form Validation Rules

## Login Screen

### National ID
- ✅ Required field
- ✅ Must be exactly 14 digits
- ✅ Must contain only numbers (0-9)
- ❌ No letters or special characters allowed

### Password
- ✅ Required field
- ✅ Minimum 6 characters
- ✅ Automatically trimmed (no leading/trailing spaces)

---

## Signup Screen

### National ID
- ✅ Required field
- ✅ Must be exactly 14 digits
- ✅ Must contain only numbers (0-9)
- ❌ No letters or special characters allowed

### Full Name
- ✅ Required field
- ✅ Minimum 3 characters
- ✅ Can only contain letters (a-z, A-Z) and spaces
- ❌ No numbers or special characters allowed

### Address
- ✅ Required field
- ✅ Minimum 5 characters
- ✅ Can contain any characters

### Email
- ✅ Required field
- ✅ Must be a valid email format (example@domain.com)
- ✅ Validates using regex pattern: `^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$`
- ❌ Invalid formats will be rejected

### Phone Number
- ✅ Required field
- ✅ Must contain only numbers (0-9)
- ✅ Must be between 10-15 digits
- ❌ No letters, spaces, or special characters allowed

### Password
- ✅ Required field
- ✅ Minimum 6 characters
- ✅ Maximum 50 characters
- ✅ Automatically trimmed (no leading/trailing spaces)

### Confirm Password
- ✅ Required field
- ✅ Must match the Password field exactly
- ✅ Validated on form submission

---

## Examples

### Valid Inputs

**National ID:** `12345678901234` ✅  
**Name:** `John Doe` ✅  
**Address:** `123 Main Street, Cairo` ✅  
**Email:** `john.doe@example.com` ✅  
**Phone:** `01234567890` ✅  
**Password:** `mypassword123` ✅

### Invalid Inputs

**National ID:** `123456` ❌ (too short)  
**National ID:** `12345678901abc` ❌ (contains letters)  
**Name:** `John123` ❌ (contains numbers)  
**Email:** `johndoe@` ❌ (invalid format)  
**Email:** `johndoe.com` ❌ (missing @)  
**Phone:** `012-345-6789` ❌ (contains dashes)  
**Phone:** `01234` ❌ (too short)  
**Password:** `pass` ❌ (too short)

---

## Error Messages

All validation errors are displayed in real-time as the user fills out the form. The form cannot be submitted until all fields pass validation.
