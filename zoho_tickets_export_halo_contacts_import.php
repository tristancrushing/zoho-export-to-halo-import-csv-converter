<?php
// Script authored by Tristan McGowan (tristan@ipspy.net)

// Check if the form has been submitted and a file is uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    // Process the uploaded file
    processFile($_FILES['fileToUpload']['tmp_name']);
}

/**
 * Function to process the uploaded CSV file
 *
 * @param string $file Path to the uploaded CSV file
 */
function processFile($file) {
    // Set headers to output a CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="converted_contacts.csv"');

    // Open the uploaded file for reading
    $inputHandle = fopen($file, 'r');
    // Open PHP output stream for writing the converted CSV
    $outputHandle = fopen('php://output', 'w');

    // Read the header row from the input file
    $header = fgetcsv($inputHandle);
    // Write the header row to the output file after mapping
    fputcsv($outputHandle, array_keys(mapData(array_flip($header))));

    // Loop through each row of the input file
    while (($row = fgetcsv($inputHandle)) !== FALSE) {
        // Map the data to the new format and write to the output file
        $mappedRow = mapData(array_combine($header, $row));
        fputcsv($outputHandle, $mappedRow);
    }

    // Close the file handles
    fclose($inputHandle);
    fclose($outputHandle);
    exit;
}

/**
 * Function to map data from the Zoho format to the new format
 *
 * @param array $row An associative array representing a row of data
 * @return array The mapped row of data
 */
function mapData($row) {
    // Example logic to handle missing email and default UserName
    if(empty($row['Email'])) {
        $UserName = $row['First Name'].'.'.$row['Last Name'];
    } else {
        $UserName = $row['Email'];
    }

    // Example of how to handle a missing Account Name
    // Uncomment if needed
    /*
    if(empty($row['Account Name'])) {
        $AccountName = 'Unknown';
    } else {
        $AccountName = $row['Account Name'];
    }
    */

    // Return the mapped data
    return [
        'ClientName' => $row['Account Name'],
        'SiteName' => 'Main', // Default value as no direct equivalent in Zoho format
        'UserName' => $UserName,
        'FirstName' => $row['First Name'],
        'LastName' => $row['Last Name'],
        'NetworkLogin' => '', // No equivalent in Zoho format; left blank
        'PhoneNumber' => $row['Phone'],
        'MobileNumber' => $row['Mobile'],
        'EmailAddress' => $row['Email'],
        'Notes' => $row['Description'] // Assuming Description is used for Notes
    ];
}

// HTML form for uploading a CSV file
?>
<!DOCTYPE html>
<html>
<head>
    <title>Zoho Contacts Export => Halo Users Import CSV Converter</title>
</head>
<body>
<form action="zoho_tickets_export_halo_contacts_import.php" method="post" enctype="multipart/form-data">
    Select CSV file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload and Convert" name="submit">
    <br>
    <br>
    <code>https://customerslug.halopsa.com/config/users/settings</code>
    <p>Click the "Import Users" button at bottom of page.</p>
</form>
</body>
</html>
