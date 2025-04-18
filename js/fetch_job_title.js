// Function to update the job titles selection dynamically based on the Role and Department selected
function updateJobTitles() {
    // Retrieve the selected role and department values
    const roleId = document.getElementById('role_id').value; // Retrieves the selected role ID
    const departmentId = document.getElementById('department_id').value; // Retrieves the selected department ID

    // Create an HTTP request (AJAX) to fetch job titles dynamically
    const xhr = new XMLHttpRequest(); // Creates a new XMLHttpRequest object
    xhr.open('POST', 'fetch_job_titles.php', true); // Sets the request method to POST and points to the server script

    // Set the request headers for POST data
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Define the function that handles the response from the server
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // If the request is complete and successful (HTTP 200)
            document.getElementById('job_title_id').innerHTML = xhr.responseText; // Updates the job titles dropdown with the server response
        }
    };

    // Send the role_id and department_id to the server
    // Encodes the data and sends it to the server-side script
    xhr.send(`role_id=${encodeURIComponent(roleId)}&department_id=${encodeURIComponent(departmentId)}`);
}