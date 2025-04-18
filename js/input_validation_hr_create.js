// Frontend validation script
function validateForm(event) {
    // To prevent the form from submitting if there are errors
    event.preventDefault();

    // Clear all previous error messages
    document.querySelectorAll('.error').forEach(error => error.textContent = '');

    const errors = {};
    const firstName = document.getElementById('fname').value.trim();
    const lastName = document.getElementById('lname').value.trim();
    const role = document.getElementById('role_id').value;
    const department = document.getElementById('department_id').value;
    const dob = document.getElementById('dob').value.trim();
    const contact = document.getElementById('contact_info').value.trim();
    const emergencyContact = document.getElementById('emergency_contact').value.trim();
    const joinedDate = document.getElementById('joined_date').value.trim();

    // Validating the first name format
    if (!/^[A-Za-z\s]+$/.test(firstName)) {
        errors.fname = "First name must only contain letters";
    }

    // Validating the last name format
    if (!/^[A-Za-z\s]+$/.test(lastName)) {
        errors.lname = "Last name must only contain letters";
    }

    // Validating the role selected
    if (!role) {
        errors.role_id = "Role must be selected";
    }

    // Validating the department selected
    if (!department) {
        errors.department_id = "Department must be selected";
    }

    // Validate age
    const today = new Date();
    const dobDate = new Date(dob);
    const age = today.getFullYear() - dobDate.getFullYear();
    const monthDifference = today.getMonth() - dobDate.getMonth();

    if (
        dobDate > today || // Future date
        age < 18 || // Less than 18 years
        (age === 18 && monthDifference < 0) || // 18 but month hasn't passed
        (age === 18 && monthDifference === 0 && today.getDate() < dobDate.getDate()) // 18 but day hasn't passed
    ) {
        errors.dob = "Age must be at least 18 years old";
    }

    // Validate contact number
    if (!/^01[0-9]{1}-[0-9]{7,8}$/.test(contact)) {
        errors.contact_info = "Invalid contact number format. Use 01X-XXXXXXX";
    }

    // Validate emergency contact number
    if (!/^01[0-9]{1}-[0-9]{7,8}$/.test(emergencyContact)) {
        errors.emergency_contact = "Invalid emergency contact number format. Use 01X-XXXXXXX";
    }

    // Validate the joined date
    const minJoinDate = new Date(dobDate.getFullYear() + 18, dobDate.getMonth(), dobDate.getDate());
    const joinedDateFormat = new Date(joinedDate);

    if (joinedDateFormat > today) {
        errors.joined_date = "Join date cannot be in the future";
    } else if (joinedDateFormat < minJoinDate) {
        errors.joined_date = "Join date must be after employee turns 18";
    }

    // Display errors
    // The errors object is populated with messages for invalid fields.
    // The errors object contains key-value pairs where:
    // Key: The name of the field that has an error (e.g., fname, email, dob, etc.).
    // Value: The corresponding error message (e.g., "First name must only contain letters.").
    Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(`error-${field}`);
        if (errorElement) {
            errorElement.textContent = errors[field];
        }
    });

    // If no errors, submit the form
    if (Object.keys(errors).length === 0) {
        document.querySelector('form').submit();
        return true;
    }
    return false;
}