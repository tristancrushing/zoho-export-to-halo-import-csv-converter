<?php
// This script converts CSV data from the Zoho Tickets format to the Halo Tickets format.
// Author: Tristan McGowan (tristan@ipspy.net)

// Check if the form was submitted and a file has been uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    // Process the uploaded file
    processFile($_FILES['fileToUpload']['tmp_name']);
}

/**
 * Processes the uploaded CSV file and outputs a converted CSV file.
 *
 * @param string $file Path to the uploaded CSV file.
 */
function processFile($file) {
    // Set headers to output a CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="converted_data.csv"');

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
 * Maps data from the original CSV format to the target format.
 *
 * @param array $row An associative array representing a row of data.
 * @return array The mapped row of data.
 */
function mapData($row) {
    // Mapping from imported format to the needed format
    return [
        'RequestID' => $row['Ticket Id'],
        'Impact' => 1, // Default value, adjust as needed
        'Urgency' => 1, // Default value, adjust as needed
        'ClientName' => $row['Account Name'],
        'SiteName' => $row['Department'], // Using Department as a proxy
        'Username' => $row['Contact Name'],
        'Summary' => $row['Subject'],
        'Details' => strip_tags($row['Description']), // Remove HTML tags for clean text
        'DateOccurred' => $row['Created Time'],
        'Category' => 'Account Administration', // Default value, adjust as needed
        'Category1' => 'Account Administration', // Default value, adjust as needed
        'Category2' => $row['Sub Category'], // Assuming this exists in the original data
        'Status' => $row['Status'],
        'AssignedTo' => $row['Ticket Owner'],
        'Team' => $row['Team'],
        'RequestType' => $row['Mode'], // Using Mode as a proxy
        'Priority' => $row['Priority'] // Map directly from original data
    ];
}

// HTML form for uploading a CSV file
?>
<!DOCTYPE html>
<html>
<head>
    <title>Zoho Tickets Export => Halo Tickets Import CSV Converter</title>
</head>
<body>
<form action="zoho_tickets_export_halo_tickets_import.php" method="post" enctype="multipart/form-data">
    Select CSV file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload and Convert" name="submit">
</form>
</body>
</html>
