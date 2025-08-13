<?php
session_start();
require_once '../backend/config.php';

if (empty($_SESSION["user_id"])) {
    header("Location: logout.php");
    exit();
}

// Get result ID from URL
$result_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($result_id <= 0) {
    header("Location: results.php");
    exit();
}

// Fetch the specific result
$sql = "SELECT dr.*, ta.full_name FROM diagnosis_results dr LEFT JOIN tbl_account_details ta ON dr.user_id = ta.acc_id WHERE dr.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $result_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: results.php");
    exit();
}

$diagnosis = $result->fetch_assoc();

$image_data = null;
$cell_counts = null;
if (!empty($diagnosis['image_filename'])) {
    $image_data = json_decode($diagnosis['image_filename'], true);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosis Report</title>

    <style>
        /* ---------- Page setup ---------- */
        @page {
            size: A4;
            margin: 15mm;
        }

        /* exact colors in print (where supported) */
        :root {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        /* Screen preview: show an A4 sheet */
        @media screen {
            .sheet {
                width: 210mm;
                min-height: 297mm;
                margin: 10mm auto;
                padding: 15mm;
                box-shadow: 0 0 0.5rem rgba(0, 0, 0, .2);
                background: white;
            }

            body {
                background: #f0f2f5;
            }
        }

        /* Print layout */
        @media print {

            html,
            body {
                height: auto;
                background: transparent;
            }

            .sheet {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            /* Hide elements that shouldn't print */
            .no-print {
                display: none !important;
            }

            /* Avoid breaking inside blocks (tables, cards, images, etc.) */
            .avoid-break {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            /* Typography polish */
            p,
            h1,
            h2,
            h3,
            h4,
            h5,
            h6 {
                orphans: 3;
                widows: 3;
            }

            img,
            svg {
                max-width: 100%;
            }
        }

        /* Base typography (works for both screen and print) */
        body {
            font: 12pt/1.4 "Inter", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }

        h1 {
            font-size: 20pt;
            margin: 0 0 8pt;
        }

        h2 {
            font-size: 14pt;
            margin: 16pt 0 6pt;
        }

        p,
        li {
            font-size: 11pt;
        }

        /* Header and footer styles */
        @media print {

            .print-header,
            .print-footer {
                position: fixed;
                left: 0;
                right: 0;
                padding: 6mm 10mm;
            }

            .print-header {
                top: 0;
                border-bottom: 1px solid #999;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 20px;
            }

            .print-footer {
                bottom: 0;
                border-top: 1px solid #999;
                text-align: center;
            }

            /* Reserve space so content doesn't overlap the fixed header/footer */
            .with-margins {
                padding-top: 20mm;
                padding-bottom: 20mm;
            }
        }

        .sheet h1{
            font-size: 20pt;
            margin: 20px auto;
            text-align: center;
        }

        .header-date, .patient-name{
            font-size: 14px;
        }

        .header-date{
            display: flex;
            flex-direction: column;
        }
    </style>
</head>

<body>

    <header class="print-header">
         <div class="patient-name"><strong>Patient Name:</strong> <?php echo htmlspecialchars($diagnosis['full_name']); ?></div>
        <div class="header-date">
            <strong>Date Printed:</strong> <span class="print-date"></span>
        </div>
    </header>
    <footer class="print-footer">
        <span>Diagnosis Report</span>
    </footer>

    <!-- Single page content -->
    <article class="sheet with-margins">
        <h1>Diagnosis Result</h1>
        <div style="margin-top: 20px;">
            <div style="display: flex; gap: 10px;">
                <strong>Analysis Date: </strong>
                <div><?php
                    $date = new DateTime($diagnosis['analysis_date']);
                    echo htmlspecialchars($date->format('F j, Y'));
                ?></div>
            </div>
        </div>

        <section class="avoid-break">
            <div style="display: flex; gap: 10px; font-size: 24px; font-weight: bold; margin: 6px 0 20px 0">
                <span>Diagnosis: </span>
                <div><?php echo htmlspecialchars($diagnosis['classification']); ?></div>
            </div>
            <div>
                <strong>Explanation:</strong>
                <div style="line-height: 26px; font-size: 14px;"><?php echo nl2br(htmlspecialchars($diagnosis['explanation'])); ?></div>
            </div>

            <div style="margin-top: 20px;">
                <strong>Health Risk:</strong>
                <div style="line-height: 26px; font-size: 14px;"><?php echo nl2br(htmlspecialchars($diagnosis['health_risk'])); ?></div>
            </div>
        </section>
    </article>

    <script>
        // Fill date/time in header
        document.querySelector('.print-date').textContent =
            new Date().toLocaleDateString();

        // Print and then close/redirect
        window.print();

        // Handle after print - close window or redirect
        window.addEventListener('afterprint', () => {
            // Try to close the window (works if opened via window.open)
            if (window.opener) {
                window.close();
            } else {
                // If can't close, redirect back to results page
                window.location.href = 'results.php';
            }
        });

        // Fallback: redirect after a short delay in case afterprint doesn't fire
        setTimeout(() => {
            if (window.opener) {
                window.close();
            } else {
                window.location.href = 'results.php';
            }
        }, 2000);
    </script>
</body>

</html>